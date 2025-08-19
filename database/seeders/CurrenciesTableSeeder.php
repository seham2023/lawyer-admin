<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'en' => 'US Dollar', 'ar' => 'الدولار الأمريكي'],
            ['code' => 'EUR', 'en' => 'Euro', 'ar' => 'اليورو'],
            ['code' => 'SAR', 'en' => 'Saudi Riyal', 'ar' => 'الريال السعودي'],
            ['code' => 'EGP', 'en' => 'Egyptian Pound', 'ar' => 'الجنيه المصري'],
            ['code' => 'AED', 'en' => 'UAE Dirham', 'ar' => 'الدرهم الإماراتي'],
            ['code' => 'GBP', 'en' => 'British Pound', 'ar' => 'الجنيه الإسترليني'],
            ['code' => 'JPY', 'en' => 'Japanese Yen', 'ar' => 'الين الياباني'],
            ['code' => 'INR', 'en' => 'Indian Rupee', 'ar' => 'الروبية الهندية'],
            ['code' => 'CNY', 'en' => 'Chinese Yuan', 'ar' => 'اليوان الصيني'],
            ['code' => 'TRY', 'en' => 'Turkish Lira', 'ar' => 'الليرة التركية'],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::create([
                'code' => $currency['code'],
                'name' => $currency,
            ]);
        }
    }
}
