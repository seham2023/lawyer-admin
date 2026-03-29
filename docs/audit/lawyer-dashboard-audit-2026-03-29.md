# Lawyer Management Dashboard Audit Report - 2026-03-29

## 1. Executive Summary
The Lawyer Management Dashboard is functional and follows a modern Filament-based architecture. However, several critical logic gaps, security risks, and incomplete features were identified that prevent it from being production-ready.

**Key findings:**
- **Critical Logic Gap:** The Visit (Consultation) flow has its payment fields commented out, breaking the financial tracking for new consultations.
- **Data Integrity Risk:** Redundant data storage between `expenses` and `payments` tables creates sync risks.
- **Security Vulnerability:** Absence of Laravel Policies (`app/Policies`) relies solely on UI-level query scoping, which is insufficient for a professional multi-user system.
- **Translation Status:** Approximately 40% of the UI (especially Widgets and Relation Managers) remains in English or has mixed terminology.

---

## 2. Audit Findings Table

| Module | Resource / Page / Widget | Area Type | Current State | Issue Type | Description | Severity | Impact | Recommended Fix | Priority | Status | Notes |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Financial** | VisitResource | Form | Payment fields (amount/tax) are re-enabled and synced. | **Fixed** | Consultations now have pricing and payments attached correctly. | **Critical** | Financial tracking restored. | Uncommented payment fields and synced with service prices. | 1 | **COMPLETE** | `CreateVisit` and `EditVisit` now handle payment sync. |
| **Security** | Global | Permission | `app/Policies` created for core models. | **Security Risk** | Access control was relying purely on query scoping. | **Critical** | Potential data leakage between lawyers. | Implement explicit Policies for all resources checking ownership. | 1 | **IN PROGRESS**| Core Policies (Case, Visit, User) implemented. |
| **Security** | Global | Permission | `app/Policies` now exists for all core resources. | **Fixed** | Access control is now enforced at the model level (ownership checks). | **Critical** | Data privacy secured. | Policies implemented for Case, Visit, User, Payment, Expense, Service. | 1 | **COMPLETE** | Standard PHP ownership logic implemented. |
| **Financial** | ExpenseResource | Logic/Data | Redundant `currency_id` and `pay_method_id` in both `expenses` and `payments` tables. | **Data Integrity** | Duplicated info leads to "source of truth" confusion. | **High** | Possible data inconsistency. | Remove redundant columns from `expenses` table or strictly sync them. | 2 | Needs Fix | Source of truth should be `payments`. |
| **Dashboard** | CalendarWidget | Logic/Widget | Case selector in "Add Session" is now scoped. | **Bug** | Lawyers could see and select cases belonging to other lawyers. | **High** | Privacy breach. | Scope the `CaseRecord` query in `getSessionFormSchema` to `auth()->id()`. | 1 | **COMPLETE** | Identified and fixed in `CalendarWidget.php`. |
| **UI/UX** | ClientResource | Form | Phone field now has country key selector. | **Fixed** | Mobile numbers are standardized with a +966 default. | **Medium** | Data inconsistency. | Added `country_key` Select and phone placeholder. | 2 | **COMPLETE** | Default to +966 added to form. |
| **Clients** | ClientResource | Action | Default delete was destructive. | **Fixed** | Deleting a client removed it globally even if other lawyers were linked to it. | **Fixed** | Data loss. | Replaced with `DetachAction` (Now Complete). | - | **COMPLETE** | Handled by refactoring LawyerClientAccess. |
| **Financial** | PaymentResource | Form | `Expense` added to `payable_type`. | **Fixed** | Expense payments can now be managed centrally. | **High** | UX inconsistency. | Added `Expense` to dropdown and table badges. | 2 | **COMPLETE** | Integrated Expense morph relationship. |
| **Dashboard** | Home | Widget | Premium KPIs implemented (Collected/Outstanding). | **Fixed** | Lawyers can see total earnings and pending dues at a glance. | **Medium** | Poor financial visibility. | Added `Total Collected` and `Outstanding` stat cards. | 3 | **COMPLETE** | Implemented using real-time aggregations. |
| **Translation** | Global | Translation | Arabic sweep completed for all new features. | **Fixed** | Dynamic labels and widgets are now fully localized. | **Medium** | Unprofessional UX. | Updated `ar.json` with Roles, KPIs, and resource labels. | 2 | **COMPLETE** | Full coverage for MVP achieved. |
| **Strategy** | Global | Currency | Standardized to SAR globally. | **Fixed** | No more hardcoded 'USD' fallbacks. | **Medium** | Customer confusion. | Used `Money::getCurrencyCode()` across all resources. | 2 | **COMPLETE** | Sweep of all `money()` columns finished. |
| **Logic** | CaseResource | Action | Opponents are now searchable and reusable. | **Fixed** | Selection from existing opponents prevents database bloat. | **Low** | Database bloat. | Replaced custom creation logic with relationship-based Select. | 4 | **COMPLETE** | Form auto-handles createOptionForm. |
| **Admin** | AdminResource | Logic | Scoped staff roles (Secretary, researcher, assistant). | **Fixed** | Lawyers can now create staff with specific designations. | **Medium** | Workflow limitation. | Added `specialist_type` (role) and standardized phone input. | 3 | **COMPLETE** | Role badges added to index table. |

---

## 3. Resource-by-Resource Summary

### 🏢 Dashboard Home
- **Status:** Functional but basic.
- **Issues:** Scoping bug in the calendar widget; missing financial KPIs; translations incomplete.
- **Improvement:** Add "Recent Payments" and "Overdue Cases" widgets.

### 👥 Client Management
- **Status:** Stable after "Detach" fix.
- **Needs:** Phone number country code defaulting to Saudi (+966).

### ⚖️ Case Management
- **Status:** Good logic; follows wizard steps.
- **Needs:** Stronger filters (by status, payment state); Standardized SAR currency display.

### 📅 Consultation (Visits)
- **Status:** **BROKEN FLOW.**
- **Needs:** Re-enable payment fields; Show service prices in the selection menu.

### 💰 Financial Management (Payments & Expenses)
- **Status:** Polymorphic logic is working, but UI is disconnected.
- **Needs:** Sync `status_id` automatically when a payment is fully paid; fix the amount display in expense editing.

---

## 4. Final Action Plan

### Phase 1: Critical Fixes (High Priority)
1. **Security:** Create `app/Policies` for all models to enforce ownership.
2. **Logic:** Uncomment and fix the payment integration in `VisitResource`.
3. **Bug:** Scope the `CaseRecord` query in the `CalendarWidget`.
4. **Data:** Fix Redundant storage in `Expense` vs `Payments`.

### Phase 2: Professional Standardization
1. **Currency:** Force SAR as default across all numeric/money columns.
2. **Phone:** Add `country_key` (+966) to all phone input sections.
3. **Translations:** Complete the `lang/ar.json` sweep for all Resources/Widgets.

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
1. Fix the `VisitResource` payment field block.
2. Implement Model Policies (`ClientPolicy`, `CasePolicy`, etc.).
3. Add "Outstanding Balance" KPI to Dashboard.
4. Normalize Phone Input (+966 default).
5. Complete translation of `CalendarWidget` and relation managers.
