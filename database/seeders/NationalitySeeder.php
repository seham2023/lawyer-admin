<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            ['en' => 'Egyptian', 'ar' => 'مصري'],
            ['en' => 'Saudi', 'ar' => 'سعودي'],
            ['en' => 'Emirati', 'ar' => 'إماراتي'],
            ['en' => 'Jordanian', 'ar' => 'أردني'],
            ['en' => 'Lebanese', 'ar' => 'لبناني'],
            ['en' => 'Kuwaiti', 'ar' => 'كويتي'],
            ['en' => 'Qatari', 'ar' => 'قطري'],
            ['en' => 'Omani', 'ar' => 'عماني'],
            ['en' => 'Bahraini', 'ar' => 'بحريني'],
            ['en' => 'Palestinian', 'ar' => 'فلسطيني'],
            ['en' => 'Syrian', 'ar' => 'سوري'],
            ['en' => 'Iraqi', 'ar' => 'عراقي'],
            ['en' => 'Yemeni', 'ar' => 'يمني'],
            ['en' => 'Moroccan', 'ar' => 'مغربي'],
            ['en' => 'Algerian', 'ar' => 'جزائري'],
            ['en' => 'Tunisian', 'ar' => 'تونسي'],
            ['en' => 'Libyan', 'ar' => 'ليبي'],
            ['en' => 'Sudanese', 'ar' => 'سوداني'],
            ['en' => 'American', 'ar' => 'أمريكي'],
            ['en' => 'British', 'ar' => 'بريطاني'],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::create([
                'name' => $nationality,
            ]);
         
        }        
    }
}
