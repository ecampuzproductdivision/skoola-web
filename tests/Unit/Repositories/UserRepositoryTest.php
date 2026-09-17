<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_paginate_users_returns_length_aware_paginator(): void
    {
        $paginator = app(UserRepositoryInterface::class)->paginateUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
    }

    public function test_create_user_assigns_role(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $roleId = Role::where('name', 'COMPANY_PARTNER')->value('id');

        $user = $repo->createUser([
            'name' => 'Repo User',
            'email' => 'repo@example.com',
            'password' => 'Passw0rd!',
            'role_id' => $roleId,
        ]);

        $this->assertTrue($user->hasRole('COMPANY_PARTNER'));
        $this->assertNotSame('Passw0rd!', $user->password);
    }

    public function test_update_user_syncs_role(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $user = User::factory()->create();
        $user->assignRole('COMPANY_PARTNER');

        $repo->updateUser($user, [
            'name' => 'Renamed',
            'email' => $user->email,
            'role_id' => Role::where('name', 'SUPER_ADMIN')->value('id'),
        ]);

        $user->refresh();
        $this->assertTrue($user->hasRole('SUPER_ADMIN'));
        $this->assertFalse($user->hasRole('COMPANY_PARTNER'));
    }

    public function test_delete_user_rejects_delete_own_account(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();

        $this->expectException(DomainException::class);
        $repo->deleteUser($admin, $admin->id);
    }

    public function test_delete_user_rejects_super_admin(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $superAdmin = User::factory()->create()->assignRole('SUPER_ADMIN');

        $this->expectException(DomainException::class);
        $repo->deleteUser($superAdmin, null);
    }

    public function test_delete_regular_user_succeeds(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $partner = User::whereHas('roles', fn ($q) => $q->where('name', 'COMPANY_PARTNER'))->firstOrFail();

        $this->assertTrue($repo->deleteUser($partner, null));
        $this->assertDatabaseMissing('users', ['id' => $partner->id]);
    }

    public function test_update_profile_updates_name_only(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $user = User::factory()->create(['name' => 'Original Name', 'email' => 'profile@example.com']);

        $updated = $repo->updateProfile($user, ['name' => 'Updated Name']);

        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame('profile@example.com', $updated->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'profile@example.com',
        ]);
    }

    public function test_change_password_with_valid_current_password(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $user = User::factory()->create(['password' => 'CurrentPassw0rd!']);

        $updated = $repo->changePassword($user, [
            'current_password' => 'CurrentPassw0rd!',
            'password' => 'NewPassw0rd!',
        ]);

        $this->assertNotSame('NewPassw0rd!', $updated->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassw0rd!', $updated->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $user = User::factory()->create(['password' => 'CurrentPassw0rd!']);

        $this->expectException(DomainException::class);

        $repo->changePassword($user, [
            'current_password' => 'WrongPassw0rd!',
            'password' => 'NewPassw0rd!',
        ]);
    }

    public function test_get_user_permissions_returns_permissions(): void
    {
        $repo = app(UserRepositoryInterface::class);
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();

        $permissions = $repo->getUserPermissions($admin);

        $this->assertNotEmpty($permissions);
        $this->assertTrue($permissions->contains(fn ($p) => $p->name === 'kejarkarir.users.read'));
    }
}
