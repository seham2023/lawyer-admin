<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {


        $saudiStatesAndCities = [
            [
                'name' => ['en' => 'Riyadh Region', 'ar' => 'منطقة الرياض'],
                'cities' => [
                    ['name' => ['en' => 'Riyadh', 'ar' => 'الرياض']],
                    ['name' => ['en' => 'Al Kharj', 'ar' => 'الخرج']],
                    ['name' => ['en' => 'Al Majma\'ah', 'ar' => 'المجمعة']],
                    ['name' => ['en' => 'Ad Diriyah', 'ar' => 'الدرعية']],
                ],
            ],
            [
                'name' => ['en' => 'Makkah Region', 'ar' => 'منطقة مكة المكرمة'],
                'cities' => [
                    ['name' => ['en' => 'Jeddah', 'ar' => 'جدة']],
                    ['name' => ['en' => 'Makkah', 'ar' => 'مكة']],
                    ['name' => ['en' => 'Taif', 'ar' => 'الطائف']],
                    ['name' => ['en' => 'Rabigh', 'ar' => 'رابغ']],
                ],
            ],
            [
                'name' => ['en' => 'Eastern Province', 'ar' => 'المنطقة الشرقية'],
                'cities' => [
                    ['name' => ['en' => 'Dammam', 'ar' => 'الدمام']],
                    ['name' => ['en' => 'Khobar', 'ar' => 'الخبر']],
                    ['name' => ['en' => 'Dhahran', 'ar' => 'الظهران']],
                    ['name' => ['en' => 'Al Jubail', 'ar' => 'الجبيل']],
                ],
            ],
            [
                'name' => ['en' => 'Medina Region', 'ar' => 'منطقة المدينة المنورة'],
                'cities' => [
                    ['name' => ['en' => 'Medina', 'ar' => 'المدينة المنورة']],
                    ['name' => ['en' => 'Al Ula', 'ar' => 'العلا']],
                    ['name' => ['en' => 'Yanbu', 'ar' => 'ينبع']],
                ],
            ],
            [
                'name' => ['en' => 'Asir Region', 'ar' => 'منطقة عسير'],
                'cities' => [
                    ['name' => ['en' => 'Abha', 'ar' => 'أبها']],
                    ['name' => ['en' => 'Khamis Mushait', 'ar' => 'خميس مشيط']],
                ],
            ],
            [
                'name' => ['en' => 'Najran Region', 'ar' => 'منطقة نجران'],
                'cities' => [
                    ['name' => ['en' => 'Najran', 'ar' => 'نجران']],
                    ['name' => ['en' => 'Sharurah', 'ar' => 'شرورة']],
                ],
            ],
            [
                'name' => ['en' => 'Tabuk Region', 'ar' => 'منطقة تبوك'],
                'cities' => [
                    ['name' => ['en' => 'Tabuk', 'ar' => 'تبوك']],
                    ['name' => ['en' => 'Dhiba', 'ar' => 'ضباء']],
                ],
            ],
            [
                'name' => ['en' => 'Hail Region', 'ar' => 'منطقة حائل'],
                'cities' => [
                    ['name' => ['en' => 'Hail', 'ar' => 'حائل']],
                    ['name' => ['en' => 'Al Bukhariyah', 'ar' => 'البكيرية']],
                ],
            ],
            [
                'name' => ['en' => 'Northern Borders Region', 'ar' => 'منطقة الحدود الشمالية'],
                'cities' => [
                    ['name' => ['en' => 'Arar', 'ar' => 'عرعر']],
                    ['name' => ['en' => 'Turaif', 'ar' => 'طريف']],
                ],
            ],
            [
                'name' => ['en' => 'Al Jawf Region', 'ar' => 'منطقة الجوف'],
                'cities' => [
                    ['name' => ['en' => 'Sakakah', 'ar' => 'سكاكا']],
                    ['name' => ['en' => 'Dumat al-Jandal', 'ar' => 'دومة الجندل']],
                ],
            ],
            [
                'name' => ['en' => 'Al Bahah Region', 'ar' => 'منطقة الباحة'],
                'cities' => [
                    ['name' => ['en' => 'Al Bahah', 'ar' => 'الباحة']],
                    ['name' => ['en' => 'Baljurashi', 'ar' => 'بلجرشي']],
                ],
            ],
            [
                'name' => ['en' => 'Jizan Region', 'ar' => 'منطقة جازان'],
                'cities' => [
                    ['name' => ['en' => 'Jizan', 'ar' => 'جازان']],
                    ['name' => ['en' => 'Abu Arish', 'ar' => 'أبو عريش']],
                ],
            ],
        ];

        foreach ($saudiStatesAndCities as $state) {
            State::create([
                'country_id' => DB::table('countries')->where('name->en', 'Saudi Arabia')->value('id'),
                'name' => $state['name'],
            ]);

            foreach ($state['cities'] as $city) {
                City::create([
                    'state_id' => DB::table('states')->where('name->en',  $state['name'])->value('id'),
                    'name' => $city['name'],
                ]);
            }
        }
    }
}
