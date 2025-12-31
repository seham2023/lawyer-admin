<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds major courts in Saudi Arabia (KSA)
     */
    public function run(): void
    {
        // Disable foreign key constraints temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Court::truncate();

        // Get court categories
        $generalCourtCategory = Category::where('type', 'court')->where('name->en', 'General Court')->first();
        $familyCourtCategory = Category::where('type', 'court')->where('name->en', 'Family Court')->first();
        $criminalCourtCategory = Category::where('type', 'court')->where('name->en', 'Criminal Court')->first();
        $commercialCourtCategory = Category::where('type', 'court')->where('name->en', 'Commercial Court')->first();
        $laborCourtCategory = Category::where('type', 'court')->where('name->en', 'Labor Court')->first();
        $appealCourtCategory = Category::where('type', 'court')->where('name->en', 'Court of Appeal')->first();
        $supremeCourtCategory = Category::where('type', 'court')->where('name->en', 'Supreme Court')->first();
        $executionCourtCategory = Category::where('type', 'court')->where('name->en', 'Execution Court')->first();

        $courts = [
            // Riyadh Courts
            [
                'name' => 'المحكمة العامة بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-GEN-001',
                'description' => 'General Court of Riyadh - Main judicial authority for general cases',
                'category_id' => $generalCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الأحوال الشخصية بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-FAM-001',
                'description' => 'Family Court of Riyadh - Handles family and personal status matters',
                'category_id' => $familyCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة الجزائية بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-CRM-001',
                'description' => 'Criminal Court of Riyadh - Handles criminal cases',
                'category_id' => $criminalCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة التجارية بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-COM-001',
                'description' => 'Commercial Court of Riyadh - Handles commercial disputes',
                'category_id' => $commercialCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة العمالية بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-LAB-001',
                'description' => 'Labor Court of Riyadh - Handles employment disputes',
                'category_id' => $laborCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الاستئناف بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-APP-001',
                'description' => 'Court of Appeal of Riyadh - Reviews lower court decisions',
                'category_id' => $appealCourtCategory?->id,
            ],
            [
                'name' => 'محكمة التنفيذ بالرياض',
                'location' => 'Riyadh',
                'court_number' => 'RYD-EXE-001',
                'description' => 'Execution Court of Riyadh - Enforces court judgments',
                'category_id' => $executionCourtCategory?->id,
            ],

            // Jeddah Courts
            [
                'name' => 'المحكمة العامة بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-GEN-001',
                'description' => 'General Court of Jeddah - Main judicial authority for general cases',
                'category_id' => $generalCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الأحوال الشخصية بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-FAM-001',
                'description' => 'Family Court of Jeddah - Handles family and personal status matters',
                'category_id' => $familyCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة الجزائية بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-CRM-001',
                'description' => 'Criminal Court of Jeddah - Handles criminal cases',
                'category_id' => $criminalCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة التجارية بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-COM-001',
                'description' => 'Commercial Court of Jeddah - Handles commercial disputes',
                'category_id' => $commercialCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة العمالية بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-LAB-001',
                'description' => 'Labor Court of Jeddah - Handles employment disputes',
                'category_id' => $laborCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الاستئناف بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-APP-001',
                'description' => 'Court of Appeal of Jeddah - Reviews lower court decisions',
                'category_id' => $appealCourtCategory?->id,
            ],
            [
                'name' => 'محكمة التنفيذ بجدة',
                'location' => 'Jeddah',
                'court_number' => 'JED-EXE-001',
                'description' => 'Execution Court of Jeddah - Enforces court judgments',
                'category_id' => $executionCourtCategory?->id,
            ],

            // Dammam Courts
            [
                'name' => 'المحكمة العامة بالدمام',
                'location' => 'Dammam',
                'court_number' => 'DAM-GEN-001',
                'description' => 'General Court of Dammam - Main judicial authority for general cases',
                'category_id' => $generalCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الأحوال الشخصية بالدمام',
                'location' => 'Dammam',
                'court_number' => 'DAM-FAM-001',
                'description' => 'Family Court of Dammam - Handles family and personal status matters',
                'category_id' => $familyCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة التجارية بالدمام',
                'location' => 'Dammam',
                'court_number' => 'DAM-COM-001',
                'description' => 'Commercial Court of Dammam - Handles commercial disputes',
                'category_id' => $commercialCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الاستئناف بالدمام',
                'location' => 'Dammam',
                'court_number' => 'DAM-APP-001',
                'description' => 'Court of Appeal of Dammam - Reviews lower court decisions',
                'category_id' => $appealCourtCategory?->id,
            ],

            // Mecca Courts
            [
                'name' => 'المحكمة العامة بمكة المكرمة',
                'location' => 'Mecca',
                'court_number' => 'MEC-GEN-001',
                'description' => 'General Court of Mecca - Main judicial authority for general cases',
                'category_id' => $generalCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الأحوال الشخصية بمكة المكرمة',
                'location' => 'Mecca',
                'court_number' => 'MEC-FAM-001',
                'description' => 'Family Court of Mecca - Handles family and personal status matters',
                'category_id' => $familyCourtCategory?->id,
            ],
            [
                'name' => 'المحكمة الجزائية بمكة المكرمة',
                'location' => 'Mecca',
                'court_number' => 'MEC-CRM-001',
                'description' => 'Criminal Court of Mecca - Handles criminal cases',
                'category_id' => $criminalCourtCategory?->id,
            ],

            // Medina Courts
            [
                'name' => 'المحكمة العامة بالمدينة المنورة',
                'location' => 'Medina',
                'court_number' => 'MED-GEN-001',
                'description' => 'General Court of Medina - Main judicial authority for general cases',
                'category_id' => $generalCourtCategory?->id,
            ],
            [
                'name' => 'محكمة الأحوال الشخصية بالمدينة المنورة',
                'location' => 'Medina',
                'court_number' => 'MED-FAM-001',
                'description' => 'Family Court of Medina - Handles family and personal status matters',
                'category_id' => $familyCourtCategory?->id,
            ],

            // Supreme Court (National Level)
            [
                'name' => 'المحكمة العليا',
                'location' => 'Riyadh',
                'court_number' => 'KSA-SUP-001',
                'description' => 'Supreme Court of Saudi Arabia - Highest judicial authority',
                'category_id' => $supremeCourtCategory?->id,
            ],
        ];

        foreach ($courts as $court) {
            Court::create($court);
        }

        // Re-enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
