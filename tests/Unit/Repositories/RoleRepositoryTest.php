<?php

namespace Tests\Unit\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_paginate_roles_returns_length_aware_paginator(): void
    {
        $paginator = app(RoleRepositoryInterface::class)->paginateRoles();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
    }

    public function test_create_role_syncs_permissions(): void
    {
        $repo = app(RoleRepositoryInterface::class);
        $permId = Permission::where('name', 'kejarkarir.users.read')->value('id');

        $role = $repo->createRole([
            'name' => 'operator',
            'display_name' => 'Operator',
            'status' => 'active',
            'description' => 'desc',
            'permissions' => [$permId],
        ]);

        $this->assertEquals('OPERATOR', $role->name);
        $this->assertTrue($role->hasPermissionTo('kejarkarir.users.read'));
    }

    public function test_update_role_syncs_permissions(): void
    {
        $repo = app(RoleRepositoryInterface::class);
        $role = Role::where('name', 'COMPANY_PARTNER')->firstOrFail();
        $permId = Permission::where('name', 'kejarkarir.roles.read')->value('id');

        $repo->updateRole($role, [
            'name' => 'COMPANY_PARTNER', 'display_name' => 'Partner Updated', 'status' => 'active', 'permissions' => [$permId],
        ]);

        $role->refresh();
        $this->assertEquals('Partner Updated', $role->display_name);
        $this->assertTrue($role->hasPermissionTo('kejarkarir.roles.read'));
    }
}
