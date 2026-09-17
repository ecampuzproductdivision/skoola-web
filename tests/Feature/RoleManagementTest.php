<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();
    }

    public function test_admin_can_view_paginated_roles(): void
    {
        $this->actingAs($this->admin)->get(route('roles.index'))->assertOk();
    }

    public function test_admin_can_create_role(): void
    {
        $permId = Permission::where('name', 'kejarkarir.users.read')->value('id');

        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'display_name' => 'Operator',
            'name' => 'OPERATOR',
            'status' => 'active',
            'description' => 'Limited access operator',
            'permissions' => [$permId],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'OPERATOR')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('kejarkarir.users.read'));
    }

    public function test_validation_fails_on_invalid_role_code(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'display_name' => 'Bad Code',
            'name' => 'lower space',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('roles', ['display_name' => 'Bad Code']);
    }

    public function test_admin_can_update_role(): void
    {
        $role = Role::where('name', 'COMPANY_PARTNER')->firstOrFail();
        $permId = Permission::where('name', 'kejarkarir.users.read')->value('id');

        $response = $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'display_name' => 'Partner Updated',
            'name' => 'COMPANY_PARTNER',
            'status' => 'active',
            'permissions' => [$permId],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role->refresh();
        $this->assertEquals('Partner Updated', $role->display_name);
        $this->assertTrue($role->hasPermissionTo('kejarkarir.users.read'));
    }

    public function test_admin_can_delete_role(): void
    {
        $role = Role::create([
            'name' => 'TEMPORARY',
            'display_name' => 'Temporary',
            'status' => 'inactive',
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $role));

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
