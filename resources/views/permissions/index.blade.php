@extends('layouts.app')

@section('content')
<div class="custom-container">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="#!">Settings</a></li>
      <li class="breadcrumb-item active" aria-current="page">Permissions</li>
    </ol>
  </nav>

  <!-- Page Header Section -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-6">
    <div>
      <h1 class="h2 mb-1">Permission Management</h1>
      <p class="text-gray-600 mb-0">Define granular permissions and assign them to roles.</p>
    </div>
    <div class="d-flex gap-2">
      @can('kejarkarir.permissions.create')
      <a href="{{ route('permissions.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="ti ti-plus fs-5"></i>
        Add New Permission
      </a>
      @endcan
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

  <!-- Card Data -->
  <div class="card shadow-sm">
    <!-- Row 1: Filter (kiri) + Export/Print (kanan) -->
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 bg-transparent py-4">
      <form action="{{ route('permissions.index') }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <div class="input-group" style="max-width: 280px;">
          <span class="input-group-text bg-transparent border-end-0 text-secondary">
            <i class="ti ti-search"></i>
          </span>
          <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or code..." value="{{ request('search') }}">
        </div>
        <select name="status" class="form-select" style="max-width: 180px;">
          <option value="">All Statuses</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-white d-flex align-items-center gap-1">
          <i class="ti ti-filter"></i>
          Terapkan
        </button>
        <a href="{{ route('permissions.index') }}" class="btn btn-white btn-icon d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
          <i class="ti ti-refresh"></i>
        </a>
      </form>
      <div class="d-flex gap-2">
        <a href="#" class="btn btn-white d-flex align-items-center gap-1">
          <i class="ti ti-download"></i>
          Export
        </a>
        <a href="#" class="btn btn-white d-flex align-items-center gap-1">
          <i class="ti ti-printer"></i>
          Print
        </a>
      </div>
    </div>

    <!-- Row 2: Jumlah data -->
    <div class="d-flex align-items-center gap-2 px-4 py-3 text-gray-600">
      <i class="ti ti-database"></i>
      <span class="fw-semibold">Menampilkan {{ $permissions->count() }} dari {{ App\Models\Permission::count() }} data</span>
    </div>

  <!-- Row 3: Datatable -->
    <div class="table-responsive border-top">
      <table class="table table-hover table-centered text-nowrap mb-0">
        <thead class="table-light">
          <tr>
            <x-sortable-th label="PERMISSION NAME" column="display_name" class="py-4 ps-4" />
            <x-sortable-th label="PERMISSION CODE" column="name" class="py-4" />
            <x-sortable-th label="DESCRIPTION" column="description" class="py-4" />
            <x-sortable-th label="STATUS" column="status" class="py-4" />
            <x-sortable-th label="ROLES" column="roles_count" class="py-4 text-center" />
            <x-sortable-th label="ACTIONS" class="py-4 text-end pe-4" />
          </tr>
        </thead>
        <tbody>
          @forelse ($permissions as $permission)
            <tr>
              <td class="py-3 ps-4">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar avatar-md rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 38px; height: 38px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-key"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239.578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" /><path d="M15 9h.01" /></svg>
                  </div>
                  <span class="fw-semibold text-dark">{{ $permission->display_name }}</span>
                </div>
              </td>
              <td class="py-3">
                <code class="text-danger bg-danger-subtle px-2 py-1 rounded fw-semibold" style="font-size: 0.85rem; font-family: var(--bs-font-sans-serif);">{{ $permission->name }}</code>
              </td>
              <td class="py-3 text-wrap text-secondary" style="max-width: 320px;">
                {{ $permission->description ?: '-' }}
              </td>
              <td class="py-3">
                @if ($permission->status === 'active')
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">active</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">inactive</span>
                @endif
              </td>
              <td class="py-3 text-center">
                <span class="badge bg-light text-dark border rounded px-3 py-1">{{ $permission->roles_count }}</span>
              </td>
              <td class="py-3 text-end pe-4">
                <div class="dropdown dropstart">
                  <a class="btn btn-icon btn-ghost btn-sm d-inline-flex align-items-center justify-content-center text-secondary" href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots-vertical"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                  </a>
                  <ul class="dropdown-menu shadow border-0 py-2">
                    @can('kejarkarir.permissions.update')
                    <li>
                      <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('permissions.edit', $permission->id) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                        Edit Details
                      </a>
                    </li>
                    @endcan
                    @can('kejarkarir.permissions.delete')
                    <li>
                      <hr class="dropdown-divider text-light">
                    </li>
                    <li>
                      <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the permission \&quot;{{ $permission->name }}\&quot;?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                          Delete Permission
                        </button>
                      </form>
                    </li>
                    @endcan
                  </ul>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-secondary fs-5">No permissions found matching current filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="d-flex justify-content-end mt-3">
    {{ $permissions->links() }}
  </div>
</div>
@endsection
