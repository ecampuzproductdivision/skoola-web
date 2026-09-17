<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\StoreMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MenuController extends Controller implements HasMiddleware
{
    /**
     * Always access data & business logic through the repository.
     */
    public function __construct(
        private readonly MenuRepositoryInterface $menuRepository
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kejarkarir.menus.read', only: ['index']),
            new Middleware('permission:kejarkarir.menus.create', only: ['create', 'store']),
            new Middleware('permission:kejarkarir.menus.update', only: ['edit', 'update']),
            new Middleware('permission:kejarkarir.menus.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request)
    {
        $menus = $this->menuRepository->paginateMenus([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], $request->integer('per_page'));

        return view('menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parents = $this->menuRepository->getParentOptions();

        return view('menus.create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request)
    {
        $this->menuRepository->createMenu($request->validated());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $parents = $this->menuRepository->getParentOptionsForEdit($menu);

        return view('menus.edit', compact('menu', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        try {
            $this->menuRepository->updateMenu($menu, $request->validated());
        } catch (DomainException $e) {
            return back()
                ->withErrors(['parent_id' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $this->menuRepository->deleteMenu($menu);

        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }
}
