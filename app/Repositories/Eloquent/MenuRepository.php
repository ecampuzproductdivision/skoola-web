<?php

namespace App\Repositories\Eloquent;

use App\Models\Menu;
use App\Providers\CacheKeyServiceProvider;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    protected function model(): string
    {
        return Menu::class;
    }

    /**
     * Find a menu by its primary key.
     */
    public function findById(int|string $id): ?Menu
    {
        /** @var ?Menu */
        return parent::findById($id);
    }

    /**
     * Return paginated menus (with parent + children count) matching filters.
     */
    public function paginateMenus(array $criteria = [], int|string|null $perPage = 15): LengthAwarePaginator
    {
        $query = Menu::query()->with('parent')->withCount('children');

        if (! empty($criteria['search'])) {
            $search = $criteria['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if (! empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        // Apply sortable column (whitelist) with a safe direction; default to parents-first.
        $sort = $criteria['sort'] ?? null;
        $direction = strtolower((string) ($criteria['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortable = ['name', 'url', 'parent_id', 'sort_order', 'status', 'children_count', 'created_at'];

        if (in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->parentsFirst()->latest('id');
        }

        return $query->paginate($this->resolvePerPage($perPage));
    }

    /**
     * Sidebar menus (active parents + active children), cached.
     */
    public function getSidebarMenus(): Collection
    {
        return Cache::remember(
            CacheKeyServiceProvider::SIDEBAR_MENUS,
            CacheKeyServiceProvider::CACHE_TTL,
            function (): Collection {
                return Menu::with(['children' => function ($q) {
                    $q->where('status', 'active')->orderBy('sort_order');
                }])
                    ->whereNull('parent_id')
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->get();
            }
        );
    }

    /**
     * Top-level menus for the create parent selector, cached.
     */
    public function getParentOptions(): Collection
    {
        return Cache::remember(
            CacheKeyServiceProvider::LIST_PARENT_MENUS,
            CacheKeyServiceProvider::CACHE_TTL,
            fn (): Collection => Menu::whereNull('parent_id')->orderBy('sort_order')->get()
        );
    }

    /**
     * Top-level menus for the edit selector, excluding the given menu and its
     * descendants so a menu cannot become its own ancestor.
     */
    public function getParentOptionsForEdit(Menu $menu): Collection
    {
        $excludeIds = collect([$menu->id]);

        foreach ($menu->children as $child) {
            $excludeIds->push($child->id);
            foreach ($child->children as $grandchild) {
                $excludeIds->push($grandchild->id);
            }
        }

        return Menu::whereNotIn('id', $excludeIds->all())
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a new menu.
     */
    public function createMenu(array $data): Menu
    {
        $menu = Menu::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'],
        ]);

        $this->forgetMenuCache();

        return $menu;
    }

    /**
     * Update a menu after guarding against cyclic parent assignment.
     */
    public function updateMenu(Menu $menu, array $data): Menu
    {
        $newParent = $data['parent_id'] ?? null;

        if ($newParent && $this->isSelfOrDescendant($menu, (int) $newParent)) {
            throw new \DomainException('A menu cannot be its own parent or a parent of one of its descendants.');
        }

        $menu->update([
            'name' => $data['name'],
            'url' => $data['url'],
            'icon' => $data['icon'] ?? null,
            'parent_id' => $newParent,
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'],
        ]);

        $this->forgetMenuCache();

        return $menu;
    }

    /**
     * Delete a menu, detaching children so they become top-level.
     */
    public function deleteMenu(Menu $menu): bool
    {
        Menu::where('parent_id', $menu->id)->update(['parent_id' => null]);

        $result = $this->delete($menu);

        $this->forgetMenuCache();

        return $result;
    }

    /**
     * Determine if the candidate id is the menu itself or one of its descendants.
     */
    public function isSelfOrDescendant(Menu $menu, int $candidateParentId): bool
    {
        if ($menu->id === $candidateParentId) {
            return true;
        }

        foreach ($menu->children as $child) {
            if ($this->isSelfOrDescendant($child, $candidateParentId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset all menu-related caches.
     */
    protected function forgetMenuCache(): void
    {
        Cache::forget(CacheKeyServiceProvider::SIDEBAR_MENUS);
        Cache::forget(CacheKeyServiceProvider::LIST_MENUS);
        Cache::forget(CacheKeyServiceProvider::LIST_PARENT_MENUS);
    }
}
