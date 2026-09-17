<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Always access data & business logic through the repository.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:kejarkarir.users.read', only: ['index']),
            new Middleware('permission:kejarkarir.users.create', only: ['create', 'store']),
            new Middleware('permission:kejarkarir.users.update', only: ['edit', 'update']),
            new Middleware('permission:kejarkarir.users.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->paginateUsers([
            'search' => $request->input('search'),
            'role_id' => $request->input('role_id'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ], $request->integer('per_page'));

        $roles = $this->userRepository->getAllRoles();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->userRepository->getAllRoles();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->userRepository->createUser($request->validated());

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = $this->userRepository->getAllRoles();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userRepository->updateUser($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $this->userRepository->deleteUser($user, (int) auth()->id());
        } catch (DomainException $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
