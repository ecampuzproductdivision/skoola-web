<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::whereHas('roles', fn ($q) => $q->where('name', 'SUPER_ADMIN'))->firstOrFail();
    }

    public function test_admin_can_view_paginated_menus(): void
    {
        $this->actingAs($this->admin)->get(route('menus.index'))->assertOk();
    }

    public function test_admin_can_create_menu(): void
    {
        $parent = Menu::whereNull('parent_id')->firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('menus.store'), [
            'name' => 'New Child',
            'url' => '/new-child',
            'icon' => 'ti-user',
            'parent_id' => $parent->id,
            'sort_order' => 0,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['url' => '/new-child', 'parent_id' => $parent->id]);
    }

    public function test_validation_fails_on_empty_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('menus.store'), [
            'url' => '/invalid',
            'sort_order' => 0,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_menu_cannot_be_its_own_parent(): void
    {
        $menu = Menu::whereNull('parent_id')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('menus.update', $menu), [
            'name' => $menu->name,
            'url' => $menu->url,
            'parent_id' => $menu->id,
            'sort_order' => $menu->sort_order,
            'status' => $menu->status,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('parent_id');
        $menu->refresh();
        $this->assertNull($menu->parent_id);
    }

    public function test_admin_can_update_and_delete_menu(): void
    {
        $menu = Menu::whereNull('parent_id')->firstOrFail();

        $update = $this->actingAs($this->admin)->put(route('menus.update', $menu), [
            'name' => 'Renamed',
            'url' => $menu->url,
            'parent_id' => null,
            'sort_order' => 5,
            'status' => 'active',
        ]);
        $update->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['id' => $menu->id, 'name' => 'Renamed']);

        $this->actingAs($this->admin)->delete(route('menus.destroy', $menu))->assertRedirect(route('menus.index'));
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }
}
