<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * Always access data & business logic through the repository.
     */
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kejarkarir.roles.read', only: ['index']),
            new Middleware('permission:kejarkarir.roles.create', only: ['create', 'store']),
            new Middleware('permission:kejarkarir.roles.update', only: ['edit', 'update']),
            new Middleware('permission:kejarkarir.roles.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = $this->roleRepository->paginateRoles([
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], $request->integer('per_page'));

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->roleRepository->getAllPermissions();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->roleRepository->createRole($request->validated());

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = $this->roleRepository->getAllPermissions();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->roleRepository->updateRole($role, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->roleRepository->deleteRole($role);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
