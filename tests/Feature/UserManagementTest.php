<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();
    }

    /**
     * Super Admin dapat melihat halaman list users (paginated).
     */
    public function test_super_admin_can_view_paginated_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('users');
    }

    /**
     * SUPER_ADMIN dapat membuat user baru dengan role.
     */
    public function test_super_admin_can_create_user(): void
    {
        $roleId = Role::where('name', 'SUPER_ADMIN')->value('id');

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Passw0rd!',
            'password_confirmation' => 'Passw0rd!',
            'role_id' => $roleId,
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $user = User::where('email', 'newuser@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('SUPER_ADMIN'));
    }

    /**
     * Validasi gagal -> password tidak memenuhi kriteria.
     */
    public function test_validation_fails_on_weak_password(): void
    {
        $roleId = Role::where('name', 'SUPER_ADMIN')->value('id');

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Bad User',
            'email' => 'bad@example.com',
            'password' => 'short',
            'role_id' => $roleId,
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'bad@example.com']);
    }

    /**
     * SUPER_ADMIN dapat memperbarui data user.
     */
    public function test_super_admin_can_update_user(): void
    {
        $target = User::factory()->create(['email' => 'target@example.com']);
        $target->assignRole('COMPANY_PARTNER');
        $roleId = Role::where('name', 'COMPANY_PARTNER')->value('id');

        $response = $this->actingAs($this->admin)->put(route('users.update', $target), [
            'name' => 'Updated Name',
            'email' => 'target@example.com',
            'role_id' => $roleId,
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Tidak bisa menghapus akun sendiri.
     */
    public function test_user_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * SUPER_ADMIN tidak bisa dihapus.
     */
    public function test_super_admin_user_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->create()->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $superAdmin));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    /**
     * SUPER_ADMIN dapat menghapus user biasa.
     */
    public function test_super_admin_can_delete_regular_user(): void
    {
        $partner = User::whereHas('roles', fn ($q) => $q->where('name', 'COMPANY_PARTNER'))->firstOrFail();

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $partner));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $partner->id]);
    }

    /**
     * Pengguna tanpa permission kejarkarir.users.read tidak diizinkan.
     */
    public function test_company_partner_cannot_access_user_management(): void
    {
        $partner = User::whereHas('roles', fn ($q) => $q->where('name', 'COMPANY_PARTNER'))->firstOrFail();

        $this->actingAs($partner)->get(route('users.index'))->assertForbidden();
    }
}
