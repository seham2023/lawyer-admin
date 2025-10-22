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

            ]);
        }

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

            ]);
        }

        $checkStatuses=   [
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
