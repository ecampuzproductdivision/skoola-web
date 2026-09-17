<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Models\Role;
use App\Providers\CacheKeyServiceProvider;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected function model(): string
    {
        return Role::class;
    }

    /**
     * Find a role by its primary key.
     */
    public function findById(int|string $id): ?Role
    {
        /** @var Role|null */
        return parent::findById($id);
    }

    /**
     * Return paginated roles (with user counts) matching optional filters.
     */
    public function paginateRoles(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator
    {
        $query = Role::query()->withCount('users');

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

        $sortable = ['display_name', 'name', 'description', 'status', 'users_count', 'created_at'];

        if (in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        return $query->paginate($this->resolvePerPage($perPage));
    }

    /**
     * All active permissions — cached master/reference data.
     */
    public function getAllPermissions(): Collection
    {
        return Cache::remember(
            CacheKeyServiceProvider::LIST_PERMISSIONS,
            CacheKeyServiceProvider::CACHE_TTL,
            fn () => Permission::orderBy('display_name')->get()
        );
    }

    /**
     * Create a role and sync its permissions (resolving by ID to models).
     */
    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => strtoupper($data['name']),
            'display_name' => $data['display_name'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'guard_name' => 'web',
        ]);

        $this->syncRolePermissions($role, $data['permissions'] ?? []);

        Cache::forget(CacheKeyServiceProvider::LIST_ROLES);

        return $role;
    }

    /**
     * Update a role and sync its permissions.
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update([
            'name' => strtoupper($data['name']),
            'display_name' => $data['display_name'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncRolePermissions($role, $data['permissions'] ?? []);

        Cache::forget(CacheKeyServiceProvider::LIST_ROLES);

        return $role;
    }

    /**
     * Delete the given role.
     */
    public function deleteRole(Role $role): bool
    {
        Cache::forget(CacheKeyServiceProvider::LIST_ROLES);

        return $this->delete($role);
    }

    /**
     * Spatie's syncPermissions treats plain strings as permission *names*;
     * resolve the provided permission IDs to model instances.
     */
    protected function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);
    }
}
