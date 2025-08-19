<?php

namespace Database\Seeders;

use Exception;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class NewPermissionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   * @throws Exception
   */
  public function run(): void
  {

  //   DB::statement('SET FOREIGN_KEY_CHECKS=0;');
  //   DB::table('permissions')->truncate();
  //   $roles = [
  //     'create',
  //     'read',
  //     'update',
  //     'delete',
  //   ];


  //   $admins = config('new-permissions.admin');
  //   $permission_ids = [];

  //   foreach ($roles as $role) {
  //     foreach ($admins as $model) {
  //       $permission =     Permission::firstorcreate(['guard_name' => 'admin', 'name' => $role . '-' . $model, 'group' => $model]);
  //       $permission_ids[] = $permission->id;
  //     }
  //   }

  //   $Admin = Admin::UpdateOrCreate(['email' => 'admin@makka.com'],[
  //     'name' => 'Admin',
  //     'username' => 'new_admin',
  //     'email' => 'admin@makka.com',
  //     'phone' => 1235478910,
  //     'active' => 1,
  //     'password' => 'password'
  //   ]);
  //   $role = Role::firstorcreate(['name' => 'employee', 'guard_name' => 'admin']);
  //   $Admin->assignRole($role->name);

  //   $role->syncPermissions($permission_ids);
  // }

  }
}
