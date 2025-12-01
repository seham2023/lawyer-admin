<?php

namespace App\Observers;

use App\Models\Qestass\User;
use App\Models\Category;
use App\Models\Status;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Check if the user type is 'lawyer'
        if ($user->type === 'lawyer') {
            $this->seedLawyerCategories($user);
            $this->seedLawyerStatuses($user);
        }
    }

    /**
     * Seed categories for the lawyer user
     */
    private function seedLawyerCategories(User $user): void
    {
        // Client categories
        $clientCategories = [
            ['en' => 'Complainant', 'ar' => 'المشتكي'],
            ['en' => 'Defendant', 'ar' => 'المشتكى به'],
            ['en' => 'Plaintiff', 'ar' => 'المدعي'],
            ['en' => 'Respondent', 'ar' => 'المدعى عليه'],
            ['en' => 'Appellant', 'ar' => 'المستأنف'],
            ['en' => 'Appellee', 'ar' => 'المستأنف عليه'],
            ['en' => 'Creditor', 'ar' => 'الدائن'],
            ['en' => 'Debtor', 'ar' => 'المدين'],
        ];

        foreach ($clientCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'client',
                'user_id' => $user->id,
            ]);
        }

        // Case categories
        $caseCategories = [
            ['en' => 'Administrative', 'ar' => 'إدارية'],
            ['en' => 'Real Estate', 'ar' => 'عقارية'],
            ['en' => 'Commercial', 'ar' => 'تجارية'],
            ['en' => 'Criminal', 'ar' => 'جنائية'],
            ['en' => 'Civil', 'ar' => 'مدنية'],
            ['en' => 'Personal Status', 'ar' => 'أحوال شخصية'],
        ];

        foreach ($caseCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'case',
                'user_id' => $user->id,
            ]);
        }

        // Expense categories
        $expenseCategories = [
            ['en' => 'Transport', 'ar' => 'المواصلات'],
            ['en' => 'Water and Electricity', 'ar' => 'الماء والكهرباء'],
            ['en' => 'Rent', 'ar' => 'الإيجار'],
            ['en' => 'Office Supplies', 'ar' => 'لوازم المكتب'],
        ];

        foreach ($expenseCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'expense',
                'user_id' => $user->id,
            ]);
        }

        // Client types
        $clientTypes = [
            ['en' => 'Individual', 'ar' => 'شخص'],
            ['en' => 'Company', 'ar' => 'شركة'],
            ['en' => 'Foundation', 'ar' => 'مؤسسة'],
            ['en' => 'Organization', 'ar' => 'منظمة'],
            ['en' => 'Other', 'ar' => 'آخر'],
        ];

        foreach ($clientTypes as $type) {
            Category::create([
                'name' => $type,
                'type' => 'client_type',
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Seed statuses for the lawyer user
     */
    private function seedLawyerStatuses(User $user): void
    {
        // Case statuses
        $caseStatuses = [
            ['en' => 'Approved', 'ar' => 'مقبول'],
            ['en' => 'Open', 'ar' => 'مفتوح'],
            ['en' => 'Pending', 'ar' => 'قيد الانتظار'],
            ['en' => 'Under Review', 'ar' => 'قيد المراجعة'],
            ['en' => 'In Lawsuits', 'ar' => 'في القضايا'],
            ['en' => 'Negotiations', 'ar' => 'في المفاوضات'],
            ['en' => 'Awaiting Payment', 'ar' => 'في انتظار السداد'],
            ['en' => 'Archived', 'ar' => 'مؤرشف'],
            ['en' => 'Reopened', 'ar' => 'أعيد فتحه'],
            ['en' => 'Closed', 'ar' => 'مغلق'],
        ];

        foreach ($caseStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'case',
                'user_id' => $user->id,
            ]);
        }

        // Expense statuses
        $expenseStatuses = [
            ['en' => 'Pending', 'ar' => 'قيد الانتظار'],
            ['en' => 'Empty', 'ar' => 'فارغ'],
            ['en' => 'Delayed', 'ar' => 'مؤجل'],
            ['en' => 'Partial Payment', 'ar' => 'دفع جزئي'],
            ['en' => 'Confirmed', 'ar' => 'مؤكد'],
            ['en' => 'Under Review', 'ar' => 'قيد المراجعة'],
            ['en' => 'Approved', 'ar' => 'مقبول'],
            ['en' => 'Paid', 'ar' => 'مدفوع'],
            ['en' => 'Rejected', 'ar' => 'مرفوض'],
            ['en' => 'In Process', 'ar' => 'قيد المعالجة'],
            ['en' => 'Settled', 'ar' => 'تمت التسوية'],
        ];

        foreach ($expenseStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'expense',
                'user_id' => $user->id,
            ]);
        }

        // Check statuses
        $checkStatuses = [
            ['en' => 'Pending', 'ar' => 'قيد الانتظار'],
            ['en' => 'Empty', 'ar' => 'فارغ'],
            ['en' => 'Delayed', 'ar' => 'مؤجل'],
            ['en' => 'Partial Payment', 'ar' => 'دفع جزئي'],
            ['en' => 'Confirmed', 'ar' => 'مؤكد'],
            ['en' => 'Under Review', 'ar' => 'قيد المراجعة'],
            ['en' => 'Approved', 'ar' => 'مقبول'],
            ['en' => 'Paid', 'ar' => 'مدفوع'],
            ['en' => 'Rejected', 'ar' => 'مرفوض'],
            ['en' => 'In Process', 'ar' => 'قيد المعالجة'],
            ['en' => 'Settled', 'ar' => 'تمت التسوية'],
        ];

        foreach ($checkStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'check',
                'user_id' => $user->id,
            ]);
        }
    }
}
