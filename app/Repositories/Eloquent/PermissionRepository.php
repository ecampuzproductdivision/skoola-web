<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Models\Role;
use App\Providers\CacheKeyServiceProvider;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    protected function model(): string
    {
        return Permission::class;
    }

    /**
     * Find a permission by its primary key.
     */
    public function findById(int|string $id): ?Permission
    {
        /** @var Permission|null */
        return parent::findById($id);
    }

    /**
     * Return paginated permissions (with role counts) matching filters.
     */
    public function paginatePermissions(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator
    {
        $query = Permission::query()->withCount('roles');

        if (! empty($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (! empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        // Apply sortable column (whitelist) with a safe direction; default to latest.
        $sort = $criteria['sort'] ?? null;
        $direction = strtolower((string) ($criteria['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortable = ['display_name', 'name', 'description', 'status', 'roles_count', 'created_at'];

        if (in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        return $query->paginate($this->resolvePerPage($perPage));
    }

    /**
     * All roles ordered by display name (for permission forms), cached.
     */
    public function getRolesForPermission(): Collection
    {
        return Role::orderBy('display_name')->get();
    }

    /**
     * Create a permission and sync its roles.
     */
    public function createPermission(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['code'],
            'display_name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'guard_name' => 'web',
        ]);

        $this->syncPermissionRoles($permission, $data['roles'] ?? []);

        Cache::forget(CacheKeyServiceProvider::LIST_PERMISSIONS);

        return $permission;
    }

    /**
     * Update a permission and sync its roles.
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update([
            'name' => $data['code'],
            'display_name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncPermissionRoles($permission, $data['roles'] ?? []);

        Cache::forget(CacheKeyServiceProvider::LIST_PERMISSIONS);

        return $permission;
    }

    /**
     * Delete the given permission.
     */
    public function deletePermission(Permission $permission): bool
    {
        Cache::forget(CacheKeyServiceProvider::LIST_PERMISSIONS);

        return $this->delete($permission);
    }

    /**
     * Sync the roles assigned to the permission.
     */
    protected function syncPermissionRoles(Permission $permission, array $roleIds): void
    {
        $permission->syncRoles($roleIds);
    }
}
