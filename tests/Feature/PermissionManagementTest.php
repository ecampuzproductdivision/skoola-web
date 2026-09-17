<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();
    }

    public function test_admin_can_view_paginated_permissions(): void
    {
        $this->actingAs($this->admin)->get(route('permissions.index'))->assertOk();
    }

    public function test_admin_can_create_permission(): void
    {
        $roleId = Role::where('name', 'COMPANY_PARTNER')->value('id');

        $response = $this->actingAs($this->admin)->post(route('permissions.store'), [
            'name' => 'Export Laporan',
            'code' => 'kejarkarir.laporan.export',
            'status' => 'active',
            'description' => 'Can export report',
            'roles' => [$roleId],
        ]);

        $response->assertRedirect(route('permissions.index'));

        $permission = Permission::where('name', 'kejarkarir.laporan.export')->firstOrFail();
        $this->assertTrue($permission->hasRole($roleId));
    }

    public function test_validation_fails_on_invalid_code_format(): void
    {
        $response = $this->actingAs($this->admin)->post(route('permissions.store'), [
            'name' => 'Bad Code',
            'code' => 'UPPER.CODE',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertDatabaseMissing('permissions', ['name' => 'UPPER.CODE']);
    }

    public function test_admin_can_update_permission(): void
    {
        $permission = Permission::where('name', 'kejarkarir.users.read')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('permissions.update', $permission), [
            'name' => 'View Users Updated',
            'code' => 'kejarkarir.users.read',
            'status' => 'active',
            'description' => $permission->description,
        ]);

        $response->assertRedirect(route('permissions.index'));

        $permission->refresh();
        $this->assertEquals('View Users Updated', $permission->display_name);
    }

    public function test_admin_can_delete_permission(): void
    {
        $permission = Permission::create([
            'name' => 'TEMP_PERMISSION',
            'display_name' => 'Temp Permission',
            'status' => 'inactive',
            'guard_name' => 'web',
        ]);

        $this->actingAs($this->admin)->delete(route('permissions.destroy', $permission))
            ->assertRedirect(route('permissions.index'));

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }
}
