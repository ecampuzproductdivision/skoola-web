<?php

namespace Tests\Unit\Repositories;

use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_paginate_menus_returns_length_aware_paginator(): void
    {
        $paginator = app(MenuRepositoryInterface::class)->paginateMenus();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
    }

    public function test_create_menu_with_parent(): void
    {
        $repo = app(MenuRepositoryInterface::class);
        $parent = Menu::whereNull('parent_id')->firstOrFail();

        $menu = $repo->createMenu([
            'name' => 'Child', 'url' => '/child', 'parent_id' => $parent->id, 'sort_order' => 1, 'status' => 'active',
        ]);

        $this->assertEquals($parent->id, $menu->parent_id);
    }

    public function test_update_menu_rejects_self_parent(): void
    {
        $repo = app(MenuRepositoryInterface::class);
        $menu = Menu::whereNull('parent_id')->firstOrFail();

        $this->expectException(DomainException::class);

        $repo->updateMenu($menu, [
            'name' => $menu->name, 'url' => $menu->url, 'parent_id' => $menu->id, 'sort_order' => 0, 'status' => 'active',
        ]);
    }

    public function test_delete_menu_detaches_children(): void
    {
        $repo = app(MenuRepositoryInterface::class);
        $parent = Menu::whereNull('parent_id')->firstOrFail();

        $child = $repo->createMenu([
            'name' => 'Child', 'url' => '/orphan-check', 'parent_id' => $parent->id, 'sort_order' => 1, 'status' => 'active',
        ]);

        $repo->deleteMenu($parent);
        $child->refresh();

        $this->assertNull($child->parent_id);
        $this->assertDatabaseMissing('menus', ['id' => $parent->id]);
    }

    public function test_is_self_or_descendant_detects_cycle(): void
    {
        $repo = app(MenuRepositoryInterface::class);
        $menu = Menu::whereNull('parent_id')->firstOrFail();

        $this->assertTrue($repo->isSelfOrDescendant($menu, $menu->id));
    }
}
