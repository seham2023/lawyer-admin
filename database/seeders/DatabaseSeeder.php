<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      RolesAndPermissionsSeeder::class,
      AdminsTableSeeder::class,
      NationalitySeeder::class,
      LevelSeeder::class,
      CurrenciesTableSeeder::class,
      CountrySeeder::class,
      CategorySeeder::class,
      StatusSeeder::class,
      NewPermissionsSeeder::class,
      StateSeeder::class,
      PaymentSeeder::class,
      EmailTemplateSeeder::class
    ]);
  }
}
