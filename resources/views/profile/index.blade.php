@extends('layouts.app')

@section('content')
<div class="custom-container">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Profile</li>
    </ol>
  </nav>

  <!-- Page Header Section -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-6">
    <div>
      <h1 class="h2 mb-1">My Profile</h1>
      <p class="text-gray-600 mb-0">Manage your personal information, account details, and password.</p>
    </div>
  </div>

  <!-- Flash Messages -->
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: #e8f5e9; color: #2e7d32;">
      <div class="d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-circle-check-filled"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 3.34a10 10 0 1 1 -14.995 8.984l-.005 -.324l.005 -.324a10 10 0 0 1 14.995 -8.336zm-1.293 5.953a1 1 0 0 0 -1.414 0l-3.293 3.293l-1.293 -1.293a1 1 0 0 0 -1.414 1.414l2 2a1 1 0 0 0 1.414 0l4 -4a1 1 0 0 0 0 -1.414z" fill="currentColor" /></svg>
        <span>{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row g-6">
    {{-- ─── Left Column: Profile Info + Permissions ─── --}}
    <div class="col-xl-4 col-12">
      {{-- Profile Summary Card --}}
      <div class="card shadow-sm mb-6">
        <div class="card-body p-5 text-center">
          <div class="avatar avatar-xl rounded-circle d-inline-flex align-items-center justify-content-center text-primary bg-primary-subtle mb-4" style="width: 96px; height: 96px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
          </div>
          <h4 class="mb-1">{{ $user->name }}</h4>
          <p class="text-secondary mb-3">{{ $user->email }}</p>
          @if ($user->role)
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">{{ $user->role->display_name }}</span>
          @else
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">No Role</span>
          @endif
        </div>
      </div>

      {{-- Permission Menu Card --}}
      <div class="card shadow-sm">
        <div class="card-header bg-transparent py-4">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <i class="ti ti-shield-lock fs-5 text-primary"></i>
            Permission Menu
          </h5>
        </div>
        <div class="card-body p-5 pt-3">
          @if ($permissions->isEmpty())
            <div class="alert alert-light border text-secondary mb-0">No permissions assigned to your account.</div>
          @else
            @php
              $grouped = $permissions->groupBy(fn ($p) => explode('.', $p->name, 2)[0]);
            @endphp
            <div class="d-flex flex-column gap-4">
              @foreach ($grouped as $module => $modulePerms)
                <div>
                  <h6 class="fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.78rem; letter-spacing: 0.5px;">{{ $module }}</h6>
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($modulePerms as $permission)
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                        <i class="ti ti-check me-1" style="font-size: 0.75rem;"></i>
                        {{ $permission->display_name }}
                      </span>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- ─── Right Column: Edit Profile + Change Password ─── --}}
    <div class="col-xl-8 col-12">
      {{-- Edit Profile Card --}}
      <div class="card shadow-sm mb-6">
        <div class="card-body p-5">
          <h5 class="card-title mb-4 pb-2 border-bottom text-secondary" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="ti ti-user-edit me-2"></i>Edit Profile
          </h5>

          @if ($errors->updateProfile->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="background-color: #ffebee; color: #c62828;">
              <div class="fw-bold mb-2">Please fix the following validation errors:</div>
              <ul class="mb-0">
                @foreach ($errors->updateProfile->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4 mb-4">
              <!-- Name -->
              <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name', 'updateProfile') is-invalid @enderror" placeholder="e.g. John Doe" value="{{ old('name', $user->name) }}" required>
                @error('name', 'updateProfile')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>


              <!-- Email (readonly — cannot be edited) -->
              <div class="col-md-6">
                <label for="email" class="form-label fw-semibold text-dark">Email</label>
                <input type="email" id="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                <div class="form-text text-secondary mt-1" style="font-size: 0.82rem;">Email cannot be changed.</div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
              <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                <i class="ti ti-device-floppy"></i>
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Change Password Card --}}
      <div class="card shadow-sm">
        <div class="card-body p-5">
          <h5 class="card-title mb-4 pb-2 border-bottom text-secondary" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="ti ti-key me-2"></i>Change Password
          </h5>

          @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: #ffebee; color: #c62828;">
              <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-alert-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                <span>{{ session('error') }}</span>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if ($errors->changePassword->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="background-color: #ffebee; color: #c62828;">
              <div class="fw-bold mb-2">Please fix the following validation errors:</div>
              <ul class="mb-0">
                @foreach ($errors->changePassword->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4 mb-4">
              <!-- Current Password -->
              <div class="col-md-6">
                <label for="current_password" class="form-label fw-semibold text-dark">Current Password <span class="text-danger">*</span></label>
                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password', 'changePassword') is-invalid @enderror" placeholder="Enter your current password" required>
                @error('current_password', 'changePassword')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- New Password -->
              <div class="col-md-6">
                <label for="password" class="form-label fw-semibold text-dark">New Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control @error('password', 'changePassword') is-invalid @enderror" placeholder="Enter new password" required>
                <div class="form-text text-secondary mt-1" style="font-size: 0.82rem;">Must contain uppercase, lowercase, number, and symbol.</div>
                @error('password', 'changePassword')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Confirm New Password -->
              <div class="col-md-6">
                <label for="password_confirmation" class="form-label fw-semibold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password', 'changePassword') is-invalid @enderror" placeholder="Repeat new password" required>
                @error('password_confirmation', 'changePassword')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>


            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
              <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                <i class="ti ti-key"></i>
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection