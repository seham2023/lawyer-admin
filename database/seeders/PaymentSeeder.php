<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            ['en' => 'Cash', 'ar' => 'نقدي'],
            ['en' => 'Check', 'ar' => 'شيك'],
            ['en' => 'Credit Card', 'ar' => 'بطاقة ائتمان'],
            ['en' => 'Debit Card', 'ar' => 'بطاقة الخصم'],
            ['en' => 'Bank Transfer', 'ar' => 'التحويل المصرفي'],
            ['en' => 'Other', 'ar' => 'أخرى'],
        ];

        foreach ($paymentMethods as $method) {
            \App\Models\PayMethod::create([
                'name' => $method,
            ]);
        }
    }
}
