<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    /**
     * Find a role by its primary key.
     */
    public function findById(int|string $id): ?Role;

    /**
     * Return paginated roles with optional search / status filters.
     *
     * @param  array{search?: string, status?: string}  $criteria
     */
    public function paginateRoles(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator;

    /**
     * All active permissions ordered by display name (for role forms).
     */
    public function getAllPermissions(): Collection;

    /**
     * Create a role and sync its permissions.
     *
     * @param  array{name: string, display_name: string, status: string, description: ?string, permissions?: array}  $data
     */
    public function createRole(array $data): Role;

    /**
     * Update a role and sync its permissions.
     *
     * @param  array{name: string, display_name: string, status: string, description: ?string, permissions?: array}  $data
     */
    public function updateRole(Role $role, array $data): Role;

    /**
     * Delete the given role.
     */
    public function deleteRole(Role $role): bool;
}
