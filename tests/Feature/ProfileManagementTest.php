<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->user = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();
    }

    /**
     * User dapat melihat halaman profil sendiri.
     */
    public function test_user_can_view_own_profile(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertViewHas('user');
        $response->assertViewHas('permissions');
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

    /**
     * User dapat mengupdate nama sendiri.
     */
    public function test_user_can_update_own_name(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => 'Updated Profile Name',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Profile Name',
        ]);
    }

    /**
     * Email tidak bisa diubah melalui profile — hanya nama yang diupdate.
     */
    public function test_user_cannot_change_email_via_profile(): void
    {
        $originalEmail = $this->user->email;

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => 'Another Name',
            'email' => 'hacked@example.com',
        ]);

        $response->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Another Name',
            'email' => $originalEmail,
        ]);
    }

    /**
     * User dapat mengubah password dengan current password yang benar.
     */
    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'NewPassw0rd!',
            'password_confirmation' => 'NewPassw0rd!',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassw0rd!', $this->user->password));
    }

    /**
     * User tidak bisa mengubah password dengan current password yang salah.
     */
    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassw0rd!',
            'password_confirmation' => 'NewPassw0rd!',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');

        $this->user->refresh();
        $this->assertTrue(Hash::check('password', $this->user->password));
    }

    /**
     * Validasi gagal jika password baru tidak memenuhi kriteria.
     */
    public function test_validation_fails_on_weak_new_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password', null, 'changePassword');

    }
/**
     * Error validasi Change Password harus masuk error bag 'changePassword',
     * TIDAK boleh bocor ke error bag 'updateProfile' (bug alert salah section).
     */
    public function test_password_validation_errors_stay_in_change_password_bag(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password', null, 'changePassword');

        /** @var \Illuminate\Support\ViewErrorBag $errors */
        $errors = session('errors');
        $this->assertFalse($errors->getBag('updateProfile')->hasAny());
        $this->assertTrue($errors->getBag('changePassword')->has('password'));

        // Pastikan halaman profile tetap render normal setelah redirect (dengan error di bag changePassword)
        $this->actingAs($this->user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Please fix the following validation errors:')
            ->assertSee('Change Password');
    }

    /**
     * Error validasi Edit Profile harus masuk ke 'updateProfile',
     * TIDAK boleh bocor ke error bag 'changePassword'.
     */
    public function test_profile_update_errors_stay_in_update_profile_bag(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name', null, 'updateProfile');

        /** @var \Illuminate\Support\ViewErrorBag $errors */
        $errors = session('errors');
        $this->assertTrue($errors->getBag('updateProfile')->has('name'));
        $this->assertFalse($errors->getBag('changePassword')->hasAny());
    }

    /**
     * Guest tidak bisa mengakses halaman profil.
     */
    public function test_guest_cannot_access_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }
}