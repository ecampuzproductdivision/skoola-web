<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Providers\CacheKeyServiceProvider;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    /**
     * Find a user by its primary key.
     */
    public function findById(int|string $id): ?User
    {
        /** @var ?User */
        return parent::findById($id);
    }

    /**
     * Return paginated users (with roles) matching optional filters.
     */
    public function paginateUsers(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->with('roles');

        if (! empty($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($criteria['role_id'])) {
            $query->whereHas('roles', fn ($q) => $q->where('id', $criteria['role_id']));
        }

        // Apply sortable column (whitelist) with a safe direction; default to latest.
        $sort = $criteria['sort'] ?? null;
        $direction = strtolower((string) ($criteria['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortable = ['name', 'email', 'role', 'created_at'];

        if (in_array($sort, $sortable, true)) {
            if ($sort === 'role') {
                // Sort by the display name of the user's first assigned role.
                $query->orderBy(
                    Role::select('display_name')
                        ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.model_type', User::class)
                        ->orderBy('model_has_roles.role_id')
                        ->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->latest();
        }

        return $query->paginate($this->resolvePerPage($perPage));
    }

    /**
     * All roles ordered by display name — cached master/reference data.
     */
    public function getAllRoles(): Collection
    {
        return Cache::remember(
            CacheKeyServiceProvider::LIST_ROLES,
            CacheKeyServiceProvider::CACHE_TTL,
            fn () => Role::orderBy('display_name')->get()
        );
    }

    /**
     * Create a user and assign its role.
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $role = Role::find($data['role_id']);
        if ($role) {
            $user->assignRole($role->name);
        }

        return $user;
    }

    /**
     * Update a user's profile, role and (optional) password.
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $role = Role::find($data['role_id']);
        if ($role) {
            $user->syncRoles([$role->name]);
        }

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return $user;
    }

    /**
     * Delete the given user.
     *
     * @throws \DomainException when the user cannot be deleted (self or SUPER_ADMIN).
     */
    public function deleteUser(User $user, ?int $actorId = null): bool
    {
        if ($actorId !== null && $user->id === $actorId) {
            throw new \DomainException('You cannot delete your own account.');
        }

        if ($user->hasRole('SUPER_ADMIN')) {
            throw new \DomainException('Super Admin users cannot be deleted.');
        }

        return $this->delete($user);
    }

    /**
     * Update the authenticated user's profile (name only — email is immutable).
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
        ]);

        return $user;
    }

    /**
     * Change the authenticated user's password after verifying the current password.
     *
     * @throws \DomainException when the current password does not match.
     */
    public function changePassword(User $user, array $data): User
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw new \DomainException('The current password is incorrect.');
        }

        $user->update([
            'password' => $data['password'],
        ]);

        return $user;
    }

    /**
     * Return all permissions assigned to the user via their roles.
     */
    public function getUserPermissions(User $user): Collection
    {
        return $user->getAllPermissions();
    }
}
