<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Find a user by its primary key.
     */
    public function findById(int|string $id): ?User;

    /**
     * Return paginated users with optional search / role filters.
     *
     * @param  array{search?: string, role_id?: int}  $criteria
     */
    public function paginateUsers(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator;

    /**
     * All roles ordered by display name — used for filters & form selection.
     */
    public function getAllRoles(): Collection;

    /**
     * Create a user, hash password (handled by cast) and assign a role.
     *
     * @param  array{name: string, email: string, password: string, role_id: int}  $data
     */
    public function createUser(array $data): User;

    /**
     * Update a user's profile and sync its role (and password when present).
     *
     * @param  array{name: string, email: string, role_id: int, password?: string|null}  $data
     */
    public function updateUser(User $user, array $data): User;

    /**
     * Delete the given user.
     *
     * @throws \DomainException when the user cannot be deleted (self or SUPER_ADMIN).
     */
    public function deleteUser(User $user, ?int $actorId = null): bool;

    /**
     * Update the authenticated user's profile (name only — email is immutable).
     *
     * @param  array{name: string}  $data
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Change the authenticated user's password after verifying the current password.
     *
     * @param  array{current_password: string, password: string}  $data
     *
     * @throws \DomainException when the current password does not match.
     */
    public function changePassword(User $user, array $data): User;

    /**
     * Return all permissions assigned to the user via their roles.
     */
    public function getUserPermissions(User $user): Collection;
}