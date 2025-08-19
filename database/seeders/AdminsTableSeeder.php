<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminsTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Insert the primary admin with a specific role
    $admin = Admin::create([
      'name' => 'Admin Name',
      'email' => 'admin@admin.com',
      'username' => 'admin',
      'phone' => '010000000',
      'password' => 123456789,
      'remember_token' => Str::random(10),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    // $admin->assignRole('Administrator');

    Admin::factory()->count(15)->create();
  }
}
