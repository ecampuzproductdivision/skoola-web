<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_paginate_permissions_returns_length_aware_paginator(): void
    {
        $paginator = app(PermissionRepositoryInterface::class)->paginatePermissions();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
    }

    public function test_create_permission_syncs_roles(): void
    {
        $repo = app(PermissionRepositoryInterface::class);
        $roleId = Role::where('name', 'SUPER_ADMIN')->value('id');

        $permission = $repo->createPermission([
            'name' => 'Export Laporan', 'code' => 'kejarkarir.laporan.export', 'status' => 'active', 'roles' => [$roleId],
        ]);

        $this->assertEquals('kejarkarir.laporan.export', $permission->name);
        $this->assertTrue($permission->hasRole($roleId));
    }
}
