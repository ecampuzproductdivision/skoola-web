<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    /**
     * Find a permission by its primary key.
     */
    public function findById(int|string $id): ?Permission;

    /**
     * Return paginated permissions with optional search / status filters.
     *
     * @param  array{search?: string, status?: string}  $criteria
     */
    public function paginatePermissions(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator;

    /**
     * All roles ordered by display name (used in permission forms).
     */
    public function getRolesForPermission(): Collection;

    /**
     * Create a permission and sync its roles.
     *
     * @param  array{name: string, code: string, status: string, description: ?string, roles?: array}  $data
     */
    public function createPermission(array $data): Permission;

    /**
     * Update a permission and sync its roles.
     *
     * @param  array{name: string, code: string, status: string, description: ?string, roles?: array}  $data
     */
    public function updatePermission(Permission $permission, array $data): Permission;

    /**
     * Delete the given permission.
     */
    public function deletePermission(Permission $permission): bool;
}
