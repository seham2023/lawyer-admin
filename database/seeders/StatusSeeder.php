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
            ['en' => 'Approved', 'ar' => 'موافقة'],
            ['en' => 'Open', 'ar' => 'مفتوحة'],
            ['en' => 'Pending', 'ar' => 'في الانتظار'],
            ['en' => 'Under Review', 'ar' => 'في الانتظار المراجعة'],
            ['en' => 'In Lawsuits', 'ar' => 'في الدعاوى القضائية'],
            ['en' => 'Negotiations', 'ar' => 'مفاوضات التسوية'],
            ['en' => 'Awaiting Payment', 'ar' => 'في انتظار الدفع'],
            ['en' => 'Archived', 'ar' => 'المؤرشفة'],
            ['en' => 'Reopened', 'ar' => 'أعيد فتحه'],
            ['en' => 'Closed', 'ar' => 'أغلقت'],
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
            ['en' => 'Delayed', 'ar' => 'متأخرة'],
            ['en' => 'Partial Payment', 'ar' => 'الدفع الجزئي'],
            ['en' => 'Confirmed', 'ar' => 'أثبت'],
            ['en' => 'Under Review', 'ar' => 'للمراجعة'],
            ['en' => 'Approved', 'ar' => 'تم الموافقة'],
            ['en' => 'Paid', 'ar' => 'المدفوع'],
            ['en' => 'Rejected', 'ar' => 'تم الرفض'],
            ['en' => 'In Process', 'ar' => 'جاري المعالجة'],
            ['en' => 'Settled', 'ar' => 'المسددة'],
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
            ['en' => 'Delayed', 'ar' => 'متأخرة'],
            ['en' => 'Partial Payment', 'ar' => 'الدفع الجزئي'],
            ['en' => 'Confirmed', 'ar' => 'أثبت'],
            ['en' => 'Under Review', 'ar' => 'للمراجعة'],
            ['en' => 'Approved', 'ar' => 'تم الموافقة'],
            ['en' => 'Paid', 'ar' => 'المدفوع'],
            ['en' => 'Rejected', 'ar' => 'تم الرفض'],
            ['en' => 'In Process', 'ar' => 'جاري المعالجة'],
            ['en' => 'Settled', 'ar' => 'المسددة'],
        ];

        foreach ($checkStatuses as $status) {
            Status::create([
                'name' => $status,
                'type' => 'check',

            ]);
        }
    }

}
