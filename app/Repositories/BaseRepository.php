<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * BaseRepository adalah abstract class untuk semua akses data ke database
 * (Eloquent). Controller TIDAK boleh memanggil Eloquent directly — semua
 * akses data dan business logic Wajib lewat Repository.
 *
 * Sub-class wajib meng-override method model() untuk menyebut Model Eloquent
 * yang dikelola.
 */
abstract class BaseRepository
{
    /**
     * The Eloquent model instance.
     */
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    /**
     * Return the fully qualified class name of the Eloquent model.
     */
    abstract protected function model(): string;

    /**
     * Resolve the per-page value, clamped to the configured bounds.
     */
    protected function resolvePerPage(int|string|null $perPage): int
    {
        $default = (int) config('kejarkarir.pagination.default_per_page', 15);
        $max = (int) config('kejarkarir.pagination.max_per_page', 100);

        if ($perPage === null || $perPage === '' || $perPage === 0) {
            return $default;
        }

        $perPage = (int) max(1, $perPage);

        return min($perPage, $max);
    }

    /**
     * Find a single record by its primary key.
     */
    public function findById(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Return a plain (non-paginated) collection matching the criteria.
     */
    public function findAll(array $criteria = []): Collection
    {
        return $this->model->where($criteria)->get();
    }

    /**
     * Return a paginated, LengthAwarePaginator result.
     */
    public function paginate(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where($criteria)
            ->paginate($this->resolvePerPage($perPage));
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by its primary key.
     */
    public function update(int|string $id, array $data): Model
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record;
    }

    /**
     * Delete the given model instance.
     */
    public function delete(Model $record): bool
    {
        return (bool) $record->delete();
    }
}
