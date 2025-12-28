<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key constraints temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Category::truncate();
        
        $client_categories = [
            ['en' => 'Complainant', 'ar' => 'المشتكي'],
            ['en' => 'Defendant', 'ar' => 'المشتكى به'],
            ['en' => 'Plaintiff', 'ar' => 'المدعي'],
            ['en' => 'Respondent', 'ar' => 'المدعى عليه'],
            ['en' => 'Appellant', 'ar' => 'المستأنف'],
            ['en' => 'Appellee', 'ar' => 'المستأنف عليه'],
            ['en' => 'Creditor', 'ar' => 'الدائن'],
            ['en' => 'Debtor', 'ar' => 'المدين'],
        ];

        foreach ($client_categories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'client',
            ]);
        }


        $case_categories = [
            ['en' => 'Administrative', 'ar' => 'إدارية'],
            ['en' => 'Real Estate', 'ar' => 'عقارية'],
            ['en' => 'Commercial', 'ar' => 'تجارية'],
            ['en' => 'Criminal', 'ar' => 'جنائية'],
            ['en' => 'Civil', 'ar' => 'مدنية'],
            ['en' => 'Personal Status', 'ar' => 'أحوال شخصية'],
        ];

        foreach ($case_categories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'case',
            ]);
        }

        $expense_categories = [
            // ['en' => 'Catering', 'ar' => 'الوجبات الكارثية'],
            ['en' => 'Transport', 'ar' => 'المواصلات'],
            ['en' => 'Water and Electricity', 'ar' => 'الماء والكهرباء'],
            ['en' => 'Rent', 'ar' => 'الإيجار'],
            ['en' => 'Office Supplies', 'ar' => 'لوازم المكتب'],
        ];

        foreach ($expense_categories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'expense',
            ]);
        }

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
            ]);
        }
        $courtCategories = [
            ['en' => 'General Court', 'ar' => 'المحكمة العامة'],
            ['en' => 'Family Court', 'ar' => 'محكمة الأحوال الشخصية'],
            ['en' => 'Criminal Court', 'ar' => 'المحكمة الجزائية'],
            ['en' => 'Commercial Court', 'ar' => 'المحكمة التجارية'],
            ['en' => 'Labor Court', 'ar' => 'المحكمة العمالية'],
            ['en' => 'Court of Appeal', 'ar' => 'محكمة الاستئناف'],
            ['en' => 'Supreme Court', 'ar' => 'المحكمة العليا'],
            ['en' => 'Execution Court', 'ar' => 'محكمة التنفيذ']
        ];

        foreach ($courtCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'court',
            ]);
        }
        
        // Re-enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
