<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolesAndPermissionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   * @throws Exception
   */
  public function run(): void
  {
    // Start transaction
    DB::beginTransaction();

    // try {
    //   // Define roles and permissions from configuration
    //   $rolesStructure = config('roles_permissions.roles');

    //   // Create roles and assign permissions
    //   foreach ($rolesStructure as $roleName => $permissions) {
    //     $role = Role::firstOrCreate(['name' => $roleName,'guard_name' => 'admin']);

    //     foreach ($permissions as $permissionGroup => $permissionNames) {
    //       foreach ($permissionNames as $permissionName) {
    //         $permission = Permission::firstOrCreate([
    //           'name' => $permissionName,
    //           'group' => $permissionGroup,
    //           'guard_name' => 'admin',
    //         ]);
    //         $role->givePermissionTo($permission);
    //       }
    //     }

    //   }

    //   // Commit transaction
    //   DB::commit();

    // } catch (Exception $e) {
    //   // Rollback transaction in case of error
    //   DB::rollBack();
    //   Log::error('Failed to seed roles and permissions: ' . $e->getMessage());
    //   throw $e;
    // }
  }
}
