<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Status::truncate();
         $paymentStatuses = [
            ['en' => 'Pending', 'ar' => 'قيد الانتظار'], //1
            ['en' => 'Paid', 'ar' => 'مدفوع'], //2
            ['en' => 'Failed', 'ar' => 'فشل'], //3
            ['en' => 'Refunded', 'ar' => 'مرتجع'], //4

        ];
        foreach ($paymentStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'payment',
            ]);
        }
        $caseStatuses = [
            ['en' => 'Consultation', 'ar' => 'استشارة'],

            // Active Stages
            ['en' => 'Active', 'ar' => 'نشط'],
            ['en' => 'Discovery', 'ar' => 'جمع الأدلة'], // Important for evidence gathering
            ['en' => 'Negotiation', 'ar' => 'تفاوض'],
            ['en' => 'In Litigation', 'ar' => 'في التقاضي'], // Court phase

            // Paused
            ['en' => 'Suspended', 'ar' => 'معلق'], // Client stopped paying or disappeared

            // Resolution Phase
            ['en' => 'Judgment', 'ar' => 'صدر الحكم'],
            ['en' => 'Appeal', 'ar' => 'استئناف'], // Widely used in law
            ['en' => 'Settled', 'ar' => 'تمت التسوية'], // Resolved without court judgment

            // Final
            ['en' => 'Closed', 'ar' => 'مغلق'],
            ['en' => 'Archived', 'ar' => 'مؤرشف'],
        ];

        foreach ($caseStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'case',

            ]);
        }

        $expenseStatuses = [
            // Internal Approval (Before billing client)
            ['en' => 'Draft', 'ar' => 'مسودة'],
            ['en' => 'Pending Approval', 'ar' => 'بانتظار الموافقة'],
            ['en' => 'Rejected', 'ar' => 'مرفوض'],

            // Billing Status (Crucial for Revenue)
            ['en' => 'Unbilled', 'ar' => 'غير مفوتر'], // Expense approved but not sent to client yet
            ['en' => 'Invoiced', 'ar' => 'مفوتر'],     // Included in an invoice

        ];
        foreach ($expenseStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'expense',

            ]);
        }

        $checkStatuses =   [
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

            ]);
        }
    }
}
