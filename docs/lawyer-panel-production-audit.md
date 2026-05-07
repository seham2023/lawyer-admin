# Lawyer Panel Production Readiness Audit

Date: 2026-05-03  
Scope: Lawyer panel (`/admin`) with supporting models, policies, current local MySQL schema, Arabic/Saudi readiness, payment logic, and multi-tenant leakage risks.  
Mode: Read-only audit. No fixes were applied as part of this report.

## Executive Summary

The Lawyer panel is not production-ready yet. The largest risks are authorization, multi-tenant isolation, and payment/schema consistency. The most urgent issue is that `App\Models\Qestass\User::canAccessPanel()` currently returns `true` for the Lawyer panel for every authenticated user, and `Gate::before()` grants every user with `parent_id = null` full permission access. In the current local `qestass_app.users` database, many ordinary users/providers have `parent_id = null`, so this can expose the Lawyer panel and role/permission management to users who should never access it.

The second major risk is data isolation. The system mixes records owned by `user_id`, records linked via `lawyer_users`, and cross-database client records from `qestass_app.users`. These concepts are not consistently enforced. Some local records already show mismatched client links.

The third major risk is schema drift. Several Lawyer-panel flows still reference columns or relationship shapes that do not exist in the current local schema, especially `payments.payment_date`, old `case_record_id` payment relationships, and date-vs-datetime mismatches.

## Environment Checked

- Laravel: 11.50.0
- PHP: 8.4.20
- App environment: `local`
- Debug: enabled
- Locale: `ar`
- Timezone: `UTC`
- Main DB connection: `mysql`
- Main DB database: `qestas_lawyer`
- App users DB connection: `qestass_app`
- App users DB database: `qestass`
- Local migrations: all listed migrations are marked as ran
- Current `qestas_lawyer` DB: 49 tables
- Current `qestass_app` DB: 95 tables

## Test Results

- `php artisan test`: failed
  - `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response`
  - Expected `/` to return `200`, current response is `404`.
- `composer audit --locked`: no vulnerability advisories found.
  - One abandoned package: `filament/spatie-laravel-translatable-plugin`, suggested replacement `lara-zeus/spatie-translatable`.
- `npm audit --omit=dev`: 0 vulnerabilities.

## Critical Findings

### C-01: Any Authenticated User Can Access the Lawyer Panel

Evidence:

- `app/Models/Qestass/User.php:500`

```php
if ($panel->getId() === 'admin') {
    return true;
    return $this->parent_id === null && $this->type !== 'admin';
}
```

Impact:

Any authenticated user from `qestass_app.users` can enter the Lawyer panel, including clients, providers, and possibly admins. The second return is unreachable.

Local DB evidence:

- `qestass_app.users` has `595` users with `type = user`.
- `583` of those users have `parent_id = null`.
- `607` providers also have `parent_id = null`.

Recommendation:

Replace the unconditional `return true` with strict rules:

- Lawyer panel: only approved lawyer/provider accounts, or explicit lawyer role.
- Sub-lawyer panel: only linked sub-lawyers.
- Super admin panel: only super admins.
- Consider checking `active`, `block`, `status`, and `approve` fields.

### C-02: Root `parent_id = null` Users Bypass All Policies

Evidence:

- `app/Providers/AppServiceProvider.php:36`

```php
Gate::before(function ($user, $ability) {
    if ($user->parent_id === null) {
        return true;
    }
    return null;
});
```

Impact:

Any user with no parent gets every permission. This is dangerous because many normal users/providers have `parent_id = null`. Combined with C-01, this can expose roles, permissions, clients, cases, payments, and delete actions.

Recommendation:

Remove this global bypass. Replace it with role-based checks, for example only a verified owner lawyer role can bypass within their tenant, and never across tenants. Super admin should be a separate role/panel rule.

### C-03: Role and Permission Management Is Exposed in the Lawyer Panel

Evidence:

- `/admin/roles`
- `/admin/permissions`
- `app/Providers/Filament/AdminPanelProvider.php` registers `FilamentSpatieRolesPermissionsPlugin`.

Impact:

Because C-01 and C-02 are open, role/permission management is exposed through the Lawyer panel. This can become privilege escalation.

Recommendation:

Move role/permission management to SuperAdmin only, or hide it from Lawyer panel except for tightly scoped sub-lawyer permission assignment.

### C-04: Generated Policy Placeholders Still Exist

Evidence:

Many policies still contain strings such as:

```php
$user->checkPermissionTo('{{ deleteAnyPermission }}');
```

Examples:

- `app/Policies/CourtPolicy.php`
- `app/Policies/ClientPolicy.php`
- `app/Policies/DocumentPolicy.php`
- `app/Policies/PaymentDetailPolicy.php`
- Many others

Impact:

These permissions will not match real permission names. Depending on the action, this can cause incorrect denies, confusing behavior, or reliance on the dangerous `Gate::before` bypass.

Recommendation:

Regenerate or manually fix all policies. Add automated tests for view/create/update/delete/deleteAny per resource.

## High Findings

### H-01: Multi-Tenant Data Model Is Inconsistent

The app uses three different ownership concepts:

- `user_id` on local tables such as `case_records`, `visits`, `expenses`, `payments`.
- `client_id` pointing to `qestass_app.users`.
- `lawyer_users` linking lawyers to clients/sub-lawyers.

Local DB evidence:

`case_records` with missing `lawyer_users` client links:

| Case | Lawyer | Client | Link Exists |
|---|---:|---:|---|
| 1 | 11 | 1357 | No |
| 2 | 11 | 11 | No |
| 3 | 11 | 1357 | No |
| 5 | 670 | 1371 | Yes |

Impact:

Some screens show records by `user_id`, while client lists use `lawyer_users`. A lawyer can have cases for clients not visible in their client list, and relation managers can behave unpredictably.

Recommendation:

Define one tenant contract:

- Lawyer owns workspace by `lawyer_id`.
- Every client relation must exist in `lawyer_users`.
- Cases, visits, expenses, payments must validate that their `client_id` is linked to the same lawyer.
- Add database-level indexes and application-level validation.

### H-02: Sub-Lawyer Tenancy Is Only Partially Implemented

Evidence:

- `CaseResource::getEloquentQuery()` supports sub-lawyer assignment.
- Other resources such as visits, expenses, payments, courts, services use only `auth()->id()`.

Impact:

Sub-lawyers may see assigned cases but fail to see related visits, payments, courts, services, expenses, or calendar data because those records are owned by the parent lawyer.

Recommendation:

Create a single helper such as `TenantContext::ownerId()`:

- Main lawyer: owner ID is current user ID.
- Sub-lawyer: owner ID is parent lawyer ID.
- Apply consistently to all Lawyer panel resources.

### H-03: Payment "Paid" Status Does Not Mean Remaining Is Zero

Evidence:

- `Payment::remaining_payment` is calculated from `payment_details`, not `payments.status_id`.
- `CaseResource` updates only `payment.status_id` from the table.
- Local DB payment `id = 6` has `status_id = 2` (Paid), amount `200.00`, paid details `0.00`, remaining `200.00`.

Impact:

Users can mark a payment as Paid while the financial summary still shows remaining amount. Reports and filters become unreliable.

Recommendation:

Centralize in `Payment` model:

- When status changes to Paid, create/update a payment detail for the remaining balance.
- When payment details reach full amount, set status to Paid.
- When payment details are partial, set status to Pending/Partial if you add Partial status.

### H-04: Code References `payments.payment_date`, but Local Schema Does Not Have It

Evidence:

- Current `payments` table has no `payment_date`.
- `app/Models/Payment.php:22` includes `payment_date` in fillable.
- `app/Models/Payment.php:31` casts `payment_date`.
- `app/Filament/Lawyer/Resources/ClientResource/Pages/ViewClient.php:143` creates payment with `payment_date`.
- `ExpenseResource/RelationManagers/PaymentsRelationManager` has `DatePicker::make('payment_date')`.

Impact:

Any active flow that saves `payment_date` to `payments` can fail with SQL error `Unknown column payment_date`.

Recommendation:

Choose one:

- Add `payment_date` migration to `payments`.
- Or remove all usage and rely on `created_at` / `payment_details.paid_at`.

For production, explicit `payment_date` is recommended.

### H-05: Case Client Sorting Joins the Wrong Database

Evidence:

- `app/Filament/Lawyer/Resources/CaseResource.php:359`

```php
return $query->join('users', 'case_records.client_id', '=', 'users.id')
```

But clients live in `qestass_app.users`, while local `qestas_lawyer.users` is empty.

Impact:

Sorting cases by client can return wrong or empty results.

Recommendation:

Use the configured app DB name when joining, or avoid cross-database joins and use a safer subquery.

### H-06: Visit Date Uses DateTimePicker but DB Column Is Date Only

Evidence:

- Current `visits.visit_date` is `date`.
- `VisitResource` uses `DateTimePicker`.
- Client `add_visit` action also uses `DateTimePicker`.

Impact:

Time portion is silently lost. This is especially bad for appointments.

Recommendation:

Change `visits.visit_date` to `datetime`, or switch UI to `DatePicker`. For lawyer visits in Saudi offices, `datetime` is the right choice.

### H-07: Expense Payments Relation Manager Can Break

Evidence:

- `ExpenseResource` includes `RelationManagers\PaymentsRelationManager`.
- That relation manager uses `payment_date`, which does not exist on `payments`.

Impact:

Viewing or creating expense payments through the relation manager can fail or display broken columns.

Recommendation:

Fix the `payments` schema or remove the relation manager fields that do not exist.

## Medium Findings

### M-01: Payment Totals Are Inconsistent Between Cases, Visits, and Expenses

Cases currently store raw `amount` without adding tax in `CreateCase`, while expenses store gross amount including tax. Visits sum service price and use tax `0`.

Impact:

Financial reports compare different meanings of `payments.amount`.

Recommendation:

Define `payments.amount` as either:

- gross total including tax, with `tax` informational, or
- base subtotal, with computed gross elsewhere.

Use the same rule across cases, visits, expenses, and payment resource.

### M-02: Case Create Payment Does Not Set `client_id`

Evidence:

- `CreateCase.php:93` creates a payment but does not pass `client_id`.
- Local DB case payments `1`, `2`, `3`, and `6` have `client_id = null`.

Impact:

Client financial summaries relying on `payments.client_id` are incomplete.

Recommendation:

Always set `client_id` on payment creation from the payable record.

### M-03: Client Creation Can Link Existing Users by Phone/Identity Without Consent Flow

Evidence:

- `CreateClient.php:59` finds any `type = user` by phone or identity and links it to the lawyer.

Impact:

A lawyer can attach an existing platform user as a client if they know phone/identity. This may be acceptable internally, but it should be controlled and auditable.

Recommendation:

Require confirmation/OTP/invitation, or restrict linking to verified office-created clients.

### M-04: Global Categories and Statuses Are Shared Across Tenants

Evidence:

Categories and statuses are queried globally in forms, often without `user_id` or tenant scoping.

Impact:

This may be intentional for standard values, but if lawyers can customize statuses/categories later, lists will mix global and tenant data.

Recommendation:

Separate system defaults from lawyer custom values:

```text
whereNull(user_id) OR where(user_id, tenant_owner_id)
```

### M-05: Courts Seeded with `user_id = null` Are Hidden From Lawyer Court Lists

Local DB evidence:

- `courts` has `22` rows with `user_id = null`.
- `CourtResource::getEloquentQuery()` filters `where('user_id', auth()->id())`.

Impact:

Saudi courts seeded as defaults may be invisible to lawyers.

Recommendation:

Use global courts plus lawyer custom courts, or assign copied defaults per lawyer.

### M-06: File Uploads Need a Production Storage Decision

Evidence:

- Uploads are used for case documents, expenses, and receipts.
- `public/storage` symlink was not present in the local `public` directory.
- Uploads are legal documents and receipts.

Impact:

Files may be inaccessible, or worse, publicly accessible without authorization if moved to public disk.

Recommendation:

For legal documents, use private storage with signed/download routes that check tenant ownership. Do not rely on public URLs for case files.

### M-07: Translatable Status Color Matching May Fail in Arabic Locale

Evidence:

`PaymentResource` matches status name against English strings such as `Paid`, `Pending`, `Cancelled`, while app locale is Arabic and statuses are translatable JSON.

Impact:

Badges may show default color in Arabic.

Recommendation:

Use stable status codes/slugs instead of translated names, or compare status IDs/constants.

## Localization Findings

Arabic is the main language and the app locale is currently `ar`. The `lang/ar.json` file has good coverage, but the Lawyer panel still has missing keys.

Audit result:

- Lawyer panel translation keys found: `394`
- Missing Arabic keys: `54`

Examples of missing keys:

- `Add payment installments to track partial payments for this visit.`
- `Client already exists for your account.`
- `Document added successfully`
- `Existing client linked to your account.`
- `Payment Summary`
- `Payment Type`
- `Remaining balance`
- `This identity number belongs to a different client record.`
- `This phone belongs to a different client record.`
- `This will remove the client from your workspace without deleting the client record.`
- `court_information`
- `manage_court_details`
- `payment_date`

Recommendations:

- Add all missing Arabic keys before production.
- Avoid full English sentences as translation keys for long UI text. Use stable keys such as `client.already_linked`.
- Review RTL layout for long Arabic labels in tables/actions.
- Use Saudi Arabic legal terms consistently. For example, prefer terms aligned with Najiz/courts where applicable.

## Saudi Lawyer Workflow Gaps

The app has core entities: clients, cases, sessions, documents, payments, visits, expenses, reminders. For a Saudi-focused legal office, these areas need stronger modeling before production:

- Saudi national ID / Iqama validation for clients and opponents.
- Case number format and court/platform reference fields.
- Najiz-style litigation stage tracking.
- Hijri date support or at least Hijri display for court sessions.
- Court/session outcome fields: judgment text, next action, required documents.
- Power of attorney / wakalah fields and expiry reminders.
- Lawyer license expiry reminders.
- Client contract/engagement letter with approval status.
- Receipt/invoice numbering compliant with Saudi practice.
- VAT handling if invoices are issued.
- Audit log for financial changes and document access.
- Document privacy levels and download logs.
- Conflict check before creating a case for a client/opponent.

## Schema Drift and Data Integrity Notes

Current local schema issues:

- `payments.payment_date` used in code but missing in DB.
- `visits.visit_date` is `date` but UI captures datetime.
- `case_records` has no FK for `user_id` and `client_id`, likely due cross-database users, but app must enforce integrity.
- Local `users` table in `qestas_lawyer` is empty while some code joins it accidentally.
- `payment_details` exists and is the real source of paid/remaining amounts.
- `service_visit` has an `id`, so the custom pivot model should keep `public $incrementing = true`.

## Recommended Fix Order

1. Lock down panel access.
   - Fix `canAccessPanel()`.
   - Remove or narrow `Gate::before()`.
   - Move roles/permissions out of Lawyer panel.

2. Define tenant ownership rules.
   - Create a tenant helper for main lawyer/sub-lawyer owner ID.
   - Apply it to every Lawyer resource and relation manager.
   - Validate client belongs to lawyer before creating cases, visits, payments, expenses.

3. Fix payment model consistency.
   - Add `payment_date` or remove usage.
   - Make Paid status create/update payment detail.
   - Normalize tax and amount meaning across cases, visits, expenses.
   - Set `client_id` on all payments.

4. Fix current schema/UI mismatches.
   - Change visits to datetime.
   - Fix case client sort cross-database join.
   - Fix expense payments relation manager.

5. Finish Arabic localization.
   - Add the 54 missing keys.
   - Replace sentence keys with stable keys.
   - Review Arabic legal terminology for Saudi usage.

6. Harden production environment.
   - Disable debug.
   - Cache config/routes/views.
   - Configure queue worker and scheduler.
   - Configure private file storage.
   - Remove console logs from production JS.
   - Rotate any real API secrets currently used as config fallbacks.

## Suggested Test Plan

Minimum tests before production:

- Auth:
  - Client cannot access `/admin`.
  - Provider without lawyer role cannot access `/admin`.
  - Main lawyer can access only own records.
  - Sub-lawyer can access only assigned cases and allowed related records.

- Tenancy:
  - Lawyer A cannot view/edit/delete Lawyer B cases, visits, payments, expenses, services, courts, documents.
  - Record URLs with another tenant's ID return 403/404.
  - Global search does not return another tenant's records.

- Payments:
  - Marking Paid makes remaining zero.
  - Adding partial payment updates total paid/remaining.
  - Overpayment is rejected.
  - Case, visit, and expense payments behave the same.

- Schema:
  - Create/edit case.
  - Create/edit visit with services.
  - Create/edit expense with payment.
  - Upload and download case document with authorization.

- Localization:
  - Arabic UI has no raw English keys.
  - Arabic RTL table/action layouts are usable on mobile and desktop.

## Current Production Readiness Rating

Not ready for production.

Suggested readiness after fixing:

- Critical auth/tenancy/payment schema issues: can move to closed beta.
- Localization, Saudi workflow, file privacy, and tests: required before public launch.
