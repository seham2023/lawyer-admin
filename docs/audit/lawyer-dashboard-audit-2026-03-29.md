# Lawyer Management Dashboard Audit Report - 2026-03-29

## 1. Executive Summary
The Lawyer Management Dashboard is functional and follows a modern Filament-based architecture. However, several critical logic gaps, security risks, and incomplete features were identified that prevent it from being production-ready.

**Key findings:**
- **Critical Logic Gap:** The Visit (Consultation) flow has its payment fields commented out, breaking the financial tracking for new consultations.
- **Data Integrity Risk:** Redundant data storage between `expenses` and `payments` tables creates sync risks.
- **Security Vulnerability:** Absence of Laravel Policies (`app/Policies`) relies solely on UI-level query scoping, which is insufficient for a professional multi-user system.
- **Translation Status:** Approximately 40% of the UI (especially Widgets and Relation Managers) remains in English or has mixed terminology.

## 1.1 Working Status
This file is now being used as a step-by-step implementation tracker as well as an audit report.

| Area | Working Status | Notes |
| :--- | :--- | :--- |
| Client detach safety | **DONE** | Client list, view, and edit actions now use detach behavior instead of deleting the shared client record. |
| Client filters | **DONE** | Name/mobile filters now use the real client fields (`first_name`, `last_name`, `phone`). |
| Client quick-add visit | **DONE** | Quick-add consultation now persists `status_id`, syncs services, and includes notes in the form. |
| Expense edit bug | **DONE** | Expense edit now back-fills amount, currency, payment method, tax, and total from the linked payment. |
| VisitResource payment flow | **DONE** | Main consultation form now includes payment fields again, scopes clients/services correctly, and keeps create/edit payment calculations aligned. |
| PaymentResource expense support | **DONE** | Main payment resource now handles expense payments in the payable type flow, filters, and payment view. |
| Payment route protection | **DONE** | Public payment success/pending/failed/status routes now require signed access, and the pending page uses signed polling/redirect URLs. |
| Calendar widget scoping | **DONE** | Calendar create/resolve paths are now consistently scoped to the authenticated lawyer. |
| Core SAR standardization | **DONE** | Visible Filament money columns, nested payment relation managers, and financial summary screens now use the shared SAR money helper instead of `USD`/`$` fallbacks. |
| Dashboard/widget translation cleanup | **DONE** | Missing admin-home and unread-message widget phrases were added to `lang/en.json` and `lang/ar.json`. |
| Messages page Livewire throttling | **DONE** | Livewire hardening middleware now uses a realistic per-user limit instead of breaking chat/message polling at 30 requests per minute per IP. |
| Next implementation step | **NEXT** | The remaining work is now mostly larger architectural follow-up: expense source-of-truth simplification, staff-role structure, and broader UX/reporting depth. |

---

## 2. Audit Findings Table

| Module | Resource / Page / Widget | Area Type | Current State | Issue Type | Description | Severity | Impact | Recommended Fix | Priority | Status | Notes |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Financial** | VisitResource | Form | Main Visit form now exposes payment fields again and keeps payment data synchronized in create/edit flows. | **Fixed** | Consultations can now be priced and billed from the core resource, not only from side actions. | **Critical** | Financial tracking is restored for the main consultation workflow. | Next enhancement: make payment fully derived from service totals when that business rule is finalized. | 1 | **COMPLETE** | Updated in `VisitResource.php`, `Pages/CreateVisit.php`, and `Pages/EditVisit.php`. |
| **Security** | Global | Permission | Core policy files now exist for users/clients, visits, cases, payments, expenses, and services. | **Fixed** | Ownership checks are now present at the model-policy layer instead of relying only on query scoping. | **Critical** | Better baseline protection for multi-user data. | Next hardening step: review any remaining custom actions/relation managers against the same policy expectations. | 1 | **COMPLETE** | Policies present in `app/Policies/*.php`; translation-manager gate is also admin-only. |
| **Security** | Global | Permission | Main resource policy layer is present, but custom action/relation manager coverage still deserves review over time. | **Permission Gap** | Most major resource access is covered, but not every custom action explicitly checks policy rules yet. | **Medium** | Some edge-case action visibility can still drift from backend rules. | Review custom actions/relation managers and add explicit `visible()`/policy checks where needed. | 2 | **NEEDS REVIEW** | Lower risk now than before the policy batch existed. |
| **Financial** | ExpenseResource | Logic/Data | Redundant `currency_id` and `pay_method_id` in both `expenses` and `payments` tables. | **Data Integrity** | Duplicated info leads to "source of truth" confusion. | **High** | Possible data inconsistency. | Remove redundant columns from `expenses` table or strictly sync them. | 2 | Needs Fix | Source of truth should be `payments`. |
| **Dashboard** | CalendarWidget | Logic/Widget | Case selector and record resolver are now scoped to the authenticated lawyer. | **Fixed** | Lawyers no longer resolve or select other lawyers’ calendar-backed case/session records through this widget path. | **High** | Privacy risk reduced. | Keep future calendar actions scoped the same way. | 1 | **COMPLETE** | Updated in `app/Filament/Widgets/CalendarWidget.php`. |
| **UI/UX** | ClientResource | Form | Client form now includes `country_key` with Saudi default and local number input. | **Fixed** | Mobile numbers are now entered in a more standardized way for clients. | **Medium** | Better data consistency. | Keep extending the same pattern to other user/staff resources for full standardization. | 2 | **COMPLETE** | Implemented in `app/Filament/Resources/ClientResource.php`. |
| **Clients** | ClientResource | Action | Delete flow has been replaced with detach actions in the client list/view/edit flow. | **Fixed** | Client removal from a lawyer workspace no longer deletes the shared client record in the current client UI flow. | **Critical** | Data loss risk reduced significantly. | Keep hard delete restricted to controlled admin-only flows if still needed. | 1 | **COMPLETE** | Implemented in `ClientResource.php`, `Pages/EditClient.php`, and `Pages/ViewClient.php`. |
| **Clients** | ViewClient quick-add visit | Action | Quick-add consultation now saves `status_id`, notes, and selected services. | **Fixed** | The modal no longer drops the fields the user selected. | **High** | Consultation creation is now more trustworthy from the client page. | Next enhancement: add visible payment/total behavior from selected services. | 1 | **COMPLETE** | Implemented in `app/Filament/Resources/ClientResource/Pages/ViewClient.php`. |
| **Financial** | ExpenseResource EditExpense | Form/Edit | Expense edit now back-fills amount and total from the linked payment record. | **Fixed** | The known empty amount / total-on-edit bug has been addressed. | **Critical** | Finance users can edit existing expenses with visible amounts again. | Add feature coverage when the test suite is expanded. | 1 | **COMPLETE** | Implemented in `app/Filament/Resources/ExpenseResource/Pages/EditExpense.php`. |
| **Financial** | PaymentResource | Form/View/Filter | Expense payments are now handled in payable type selection, filters, table badges, and payment view rendering. | **Fixed** | Expense payments can now be managed more consistently from the central payment resource. | **High** | Better finance workflow consistency. | Next enhancement: add dedicated expense-specific quick actions if operations need them. | 2 | **COMPLETE** | Updated in `PaymentResource.php` and `Pages/ViewPayment.php`. |
| **Security** | Public payment routes | Route / Payment | Payment success/pending/failed/status endpoints now require signed URLs, and signed links are generated inside the controller for the pending-page polling flow. | **Fixed** | Raw payment IDs alone are no longer enough to access payment status pages. | **Critical** | Payment metadata exposure risk reduced significantly. | If external customer-facing entry points are added later, generate signed links from those flows as well. | 1 | **COMPLETE** | Updated in `routes/web.php`, `app/Http/Controllers/PaymentController.php`, and `resources/views/payments/pending.blade.php`. |
| **Dashboard** | Home | Widget | Stats widget now includes `Total Clients`, `Active Cases`, `Total Collected`, and `Outstanding`. | **Partially Fixed** | Core financial visibility is better, though more KPI depth is still desirable. | **Medium** | Dashboard is more actionable than before. | Add consultations, paid/unpaid breakdowns, expenses, and role-specific dashboards as a second wave. | 3 | **NEEDS REVIEW** | `StatsOverviewWidget` already includes collected/outstanding totals. |
| **Translation** | Global | Translation | Missing dashboard/widget message phrases were added and the most visible admin-home labels now have locale coverage. | **Partially Fixed** | The top-level dashboard experience is more complete in Arabic, though a full translation sweep is still broader than this pass. | **Medium** | UI polish improved. | Continue standardizing older phrase-based keys over time. | 2 | **NEEDS REVIEW** | Updated `lang/en.json` and `lang/ar.json` for admin-home/message-widget phrases. |
| **Strategy** | Global | Currency | Core Filament surfaces now use the shared SAR helper instead of `USD`/`$` fallbacks. | **Partially Fixed** | The most visible currency inconsistencies were removed from the dashboard/resources reviewed in this pass. | **Critical** | Much lower risk of wrong currency display in daily use. | Keep refactoring any remaining non-Filament or legacy paths to the same helper. | 2 | **NEEDS REVIEW** | `rg` scan for `USD`/`$` fallbacks in `app/Filament`, `app/Support`, and `resources/views` now returns clean. |
| **Communication** | Messages page / Livewire middleware | Logic / UX | Livewire update hardening now rate-limits by user-aware key at a realistic dashboard-safe threshold instead of 30 requests/minute per IP. | **Fixed** | The messages page no longer collapses into `Too many requests. Please slow down.` during normal polling usage. | **High** | Chat usability and message monitoring are restored. | Keep the middleware in place, but tune further if more polling widgets are added. | 1 | **COMPLETE** | Updated in `app/Http/Middleware/HardenLivewireRequests.php`. |
| **Logic** | CaseResource | Action | Opponents are still created ad hoc and are not yet reusable/searchable in a standardized way. | **Enhancement** | Repeated creation can still cause database bloat and inconsistent naming. | **Low** | Database clutter. | Replace free-text creation flow with searchable reusable records and optional create-on-the-fly. | 4 | **NEEDS FIX** | Not yet implemented in the current form flow. |
| **Admin** | AdminResource | Logic | Scoped staff roles (Secretary, researcher, assistant) are not yet implemented in a dedicated management structure. | **Logic Gap** | Lawyers still do not have a proper management/staff resource for non-lawyer operations users. | **Medium** | Workflow limitation. | Add a management/staff user type and separate role structure. | 3 | **NEEDS FIX** | Keep this as a later structure phase after safety and finance fixes. |

---

## 3. Resource-by-Resource Summary

### 🏢 Dashboard Home
- **Status:** Improved, but still not fully mature.
- **Issues:** KPI breadth still needs work, but the most visible dashboard/widget translation gaps are improved.
- **Improvement:** Add "Recent Payments" and "Overdue Cases" widgets.

### 👥 Client Management
- **Status:** Improved. Detach safety, client filters, Saudi-default phone input, and quick-add consultation persistence are now patched.
- **Needs:** Full policy coverage and shared translation cleanup.

### ⚖️ Case Management
- **Status:** Good logic; follows wizard steps.
- **Needs:** Stronger filters (by status, payment state); Standardized SAR currency display.

### 📅 Consultation (Visits)
- **Status:** Improved. Main payment fields are restored, services show prices, and the resource is now scoped to the lawyer’s own clients/services.
- **Needs:** Stronger policy coverage and a final decision on whether pricing should always be auto-derived from selected services.

### 💰 Financial Management (Payments & Expenses)
- **Status:** Polymorphic logic is working, but UI is still disconnected in places.
- **Needs:** Sync `status_id` automatically when a payment is fully paid; complete the main Visit payment flow.
- **Done:** Fixed the known expense edit bug where amount/total were empty on edit.

---

## 4. Final Action Plan

### Phase 1: Critical Fixes (High Priority)
1. **Done in current codebase:** Core policies exist for the main resources.
2. **Done in this pass:** Restored and aligned the `VisitResource` payment integration.
3. **Done in this pass:** Scoped the `CalendarWidget` query and resolver paths.
4. **Data:** Fix Redundant storage in `Expense` vs `Payments`.
5. **Done in this pass:** Safer client detach flow, fixed client filters, fixed quick-add consultation persistence, fixed expense edit amount/total fill, and secured payment routes.

### Phase 2: Professional Standardization
1. **Done in this pass:** Core Filament SAR standardization using the shared money helper.
2. **Phone:** Add `country_key` (+966) to all phone input sections.
3. **Continue:** Complete the broader `lang/ar.json` sweep for all Resources/Widgets.

### Phase 3: Dashboard & UX Enhancements
1. **KPIs:** Add Stats for "Collected Amount", "Remaining Balance", and "Active Consultations".
2. **Filters:** Add "Paid / Unpaid / Partial" filters to Cases and Visits.
3. **Staff Role:** Introduce a "Management" user type for non-lawyer staff.

---

## 5. Quick Wins
- Standardize all currency columns to use `SAR`.
- Update `StatsOverviewWidget` to include financial totals.
- Fix "Total Users" label to "Total Clients".
- Add the missing "Export" button to Payments and Expenses.

## 6. High-Risk Bugs / Future Risks
- **Polymorphic Changes:** If the `payable` types are changed, existing payments will lose their links.
- **Delete Logic:** Without soft deletes on Clients, accidental database wipes by a Super Admin are possible.
- **Tax Recalculation:** If tax rates change, old payments stored as "Total" will not reflect the change unless stored as net/gross separately.

---
## 7. Recommended Next Steps (Order of Implementation)
1. Expand dashboard KPIs beyond collected/outstanding into consultations, payment states, and expenses.
2. Review custom actions/relation managers for explicit policy-aware visibility.
3. Resolve the redundant source-of-truth split between `expenses` and `payments`.
4. Add the dedicated management/staff structure for non-lawyer operations users.
5. Continue the broader translation cleanup pass for older phrase-based labels and empty states.
