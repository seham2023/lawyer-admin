<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caseLevels = [
            ['en' => 'Primary Court', 'ar' => 'المحكمة الابتدائية'],
            ['en' => 'Appeals Court', 'ar' => 'محكمة الاستئناف'],
            ['en' => 'Supreme Court', 'ar' => 'المحكمة العليا'],
        ];

        foreach ($caseLevels as $level) {
            Level::create([
                'name' => $level,
            ]);
        }
    }
}
