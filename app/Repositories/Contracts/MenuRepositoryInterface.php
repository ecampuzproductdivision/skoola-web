<?php

namespace App\Repositories\Contracts;

use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MenuRepositoryInterface
{
    /**
     * Find a menu by its primary key.
     */
    public function findById(int|string $id): ?Menu;

    /**
     * Return paginated menus with optional search / status filters, ordered
     * parents-first.
     *
     * @param  array{search?: string, status?: string}  $criteria
     */
    public function paginateMenus(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator;

    /**
     * Sidebar menus (active top-level menus with their active children).
     */
    public function getSidebarMenus(): Collection;

    /**
     * Top-level menus for the "create" parent selector; excludes nothing.
     */
    public function getParentOptions(): Collection;

    /**
     * Top-level menus for the "edit" parent selector, excluding the given
     * menu and all of its descendants to prevent cyclic references.
     */
    public function getParentOptionsForEdit(Menu $menu): Collection;

    /**
     * Create a new menu.
     */
    public function createMenu(array $data): Menu;

    /**
     * Update a menu after validating the new parent does not create a cycle.
     */
    public function updateMenu(Menu $menu, array $data): Menu;

    /**
     * Delete a menu, detaching children so they become top-level.
     */
    public function deleteMenu(Menu $menu): bool;

    /**
     * Determine if a candidate id is the menu itself or one of its descendants.
     */
    public function isSelfOrDescendant(Menu $menu, int $candidateParentId): bool;
}
