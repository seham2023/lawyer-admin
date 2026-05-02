<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $resources = [
            'User' => 'User Management',
            'SubLawyer' => 'Sub Lawyer Management',
            'Client' => 'Client Management',
            'CaseRecord' => 'Case Management',
            'Visit' => 'Visit Management',
            'Expense' => 'Expense Management',
            'Payment' => 'Payment Management',
            'Category' => 'Settings',
            'Role' => 'Role Management',
            'Permission' => 'Role Management',
        ];

        $actions = [
            'view-any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force-delete',
        ];

        foreach ($resources as $resource => $group) {
            foreach ($actions as $action) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => "{$action} {$resource}",
                    'guard_name' => 'web',
                ], [
                    'group' => $group,
                ]);
            }
        }
    }
}
