<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissionRepository
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kejarkarir.permissions.read', only: ['index']),
            new Middleware('permission:kejarkarir.permissions.create', only: ['create', 'store']),
            new Middleware('permission:kejarkarir.permissions.update', only: ['edit', 'update']),
            new Middleware('permission:kejarkarir.permissions.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request)
    {
        $permissions = $this->permissionRepository->paginatePermissions([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], $request->integer('per_page'));

        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->permissionRepository->getRolesForPermission();

        return view('permissions.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        $this->permissionRepository->createPermission($request->validated());

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $roles = $this->permissionRepository->getRolesForPermission();
        $permission->load('roles');

        return view('permissions.edit', compact('permission', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $this->permissionRepository->updatePermission($permission, $request->validated());

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $this->permissionRepository->deletePermission($permission);

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
