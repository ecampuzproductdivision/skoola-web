<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the permissions table with a per-module permission matrix
     * (view / create / update / delete for each module) and assign all of
     * them to the Super Admin role.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            ['name' => 'View Users', 'code' => 'kejarkarir.users.read', 'description' => 'Can view the list of users.', 'status' => 'active'],
            ['name' => 'Create User', 'code' => 'kejarkarir.users.create', 'description' => 'Can create a new user.', 'status' => 'active'],
            ['name' => 'Edit User', 'code' => 'kejarkarir.users.update', 'description' => 'Can edit an existing user.', 'status' => 'active'],
            ['name' => 'Delete User', 'code' => 'kejarkarir.users.delete', 'description' => 'Can delete a user.', 'status' => 'active'],

            // Roles
            ['name' => 'View Roles', 'code' => 'kejarkarir.roles.read', 'description' => 'Can view the list of roles.', 'status' => 'active'],
            ['name' => 'Create Role', 'code' => 'kejarkarir.roles.create', 'description' => 'Can create a new role.', 'status' => 'active'],
            ['name' => 'Edit Role', 'code' => 'kejarkarir.roles.update', 'description' => 'Can edit an existing role.', 'status' => 'active'],
            ['name' => 'Delete Role', 'code' => 'kejarkarir.roles.delete', 'description' => 'Can delete a role.', 'status' => 'active'],

            // Menus
            ['name' => 'View Menus', 'code' => 'kejarkarir.menus.read', 'description' => 'Can view the list of menus.', 'status' => 'active'],
            ['name' => 'Create Menu', 'code' => 'kejarkarir.menus.create', 'description' => 'Can create a new menu.', 'status' => 'active'],
            ['name' => 'Edit Menu', 'code' => 'kejarkarir.menus.update', 'description' => 'Can edit an existing menu.', 'status' => 'active'],
            ['name' => 'Delete Menu', 'code' => 'kejarkarir.menus.delete', 'description' => 'Can delete a menu.', 'status' => 'active'],

            // Permissions
            ['name' => 'View Permissions', 'code' => 'kejarkarir.permissions.read', 'description' => 'Can view the list of permissions.', 'status' => 'active'],
            ['name' => 'Create Permission', 'code' => 'kejarkarir.permissions.create', 'description' => 'Can create a new permission.', 'status' => 'active'],
            ['name' => 'Edit Permission', 'code' => 'kejarkarir.permissions.update', 'description' => 'Can edit an existing permission.', 'status' => 'active'],
            ['name' => 'Delete Permission', 'code' => 'kejarkarir.permissions.delete', 'description' => 'Can delete a permission.', 'status' => 'active'],
        ];

        foreach ($permissions as $data) {
            Permission::updateOrCreate(
                ['name' => $data['code']],
                [
                    'display_name' => $data['name'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'guard_name' => 'web',
                ]
            );
        }

        // Remove any permission that is no longer part of this matrix so the table stays in sync.
        $validCodes = array_column($permissions, 'code');
        Permission::whereNotIn('name', $validCodes)->delete();

        // Refresh cache before syncing
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign the full matrix to the Super Admin role.
        $superAdmin = Role::where('name', 'SUPER_ADMIN')->first();

        if ($superAdmin) {
            $superAdmin->syncPermissions(
                Permission::whereIn('name', $validCodes)->pluck('name')
            );
        }
    }
}

