<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Always access data & business logic through the repository.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Display the authenticated user's profile page.
     */
    public function show(): View
    {
        $user = auth()->user();
        $permissions = $this->userRepository->getUserPermissions($user);

        return view('profile.index', compact('user', 'permissions'));
    }

    /**
     * Update the authenticated user's profile (name only — email is immutable).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->userRepository->updateProfile($request->user(), $request->validated());

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        try {
            $this->userRepository->changePassword($request->user(), $request->validated());
        } catch (DomainException $e) {
            return redirect()->route('profile.show')->with('error', $e->getMessage());
        }

        return redirect()->route('profile.show')->with('success', 'Password changed successfully.');
    }
}