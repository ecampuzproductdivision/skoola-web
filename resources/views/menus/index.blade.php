@extends('layouts.app')

@section('content')
<div class="custom-container">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="#!">Settings</a></li>
      <li class="breadcrumb-item active" aria-current="page">Menus</li>
    </ol>
  </nav>

  <!-- Page Header Section -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-6">
    <div>
      <h1 class="h2 mb-1">Menu Management</h1>
      <p class="text-gray-600 mb-0">Organize application navigation menus and their hierarchy.</p>
    </div>
    <div class="d-flex gap-2">
      @can('kejarkarir.menus.create')
      <a href="{{ route('menus.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="ti ti-plus fs-5"></i>
        Add New Menu
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

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: #ffebee; color: #c62828;">
      <div class="d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-alert-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
        <span>{{ session('error') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Card Data -->
  <div class="card shadow-sm">
    <!-- Row 1: Filter (kiri) + Export/Print (kanan) -->
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 bg-transparent py-4">
      <form action="{{ route('menus.index') }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <div class="input-group" style="max-width: 280px;">
          <span class="input-group-text bg-transparent border-end-0 text-secondary">
            <i class="ti ti-search"></i>
          </span>
          <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name or url..." value="{{ request('search') }}">
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
        <a href="{{ route('menus.index') }}" class="btn btn-white btn-icon d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
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
      <span class="fw-semibold">Menampilkan {{ $menus->count() }} dari {{ App\Models\Menu::count() }} data</span>
    </div>

  <!-- Row 3: Datatable -->
    <div class="table-responsive border-top">
      <table class="table table-hover table-centered text-nowrap mb-0">
        <thead class="table-light">
          <tr>
            <x-sortable-th label="NAME" column="name" class="py-4 ps-4" />
            <x-sortable-th label="URL" column="url" class="py-4" />
            <x-sortable-th label="PARENT" column="parent_id" class="py-4" />
            <x-sortable-th label="ORDER" column="sort_order" class="py-4 text-center" />
            <x-sortable-th label="STATUS" column="status" class="py-4" />
            <x-sortable-th label="CHILDREN" column="children_count" class="py-4 text-center" />
            <x-sortable-th label="ACTIONS" class="py-4 text-end pe-4" />
          </tr>
        </thead>
        <tbody>
          @forelse ($menus as $menu)
            <tr>
              <td class="py-3 ps-4">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar avatar-md rounded-circle d-flex align-items-center justify-content-center text-primary bg-primary-subtle" style="width: 38px; height: 38px;">
                    @if ($menu->icon)
                      <i class="ti {{ $menu->icon }}"></i>
                    @else
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-menu-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 6l16 0" /><path d="M4 12l16 0" /><path d="M4 18l16 0" /></svg>
                    @endif
                  </div>
                  <div class="d-flex flex-column">
                    @if ($menu->parent_id)
                      <span class="text-secondary" style="font-size: 0.8rem;">└&nbsp;Sub</span>
                    @endif
                    <span class="fw-semibold text-dark">{{ $menu->name }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3">
                <code class="text-danger bg-danger-subtle px-2 py-1 rounded fw-semibold" style="font-size: 0.85rem; font-family: var(--bs-font-sans-serif);">{{ $menu->url }}</code>
              </td>
              <td class="py-3 text-secondary">{{ $menu->parent?->name ?: '-' }}</td>
              <td class="py-3 text-center text-secondary">{{ $menu->sort_order }}</td>
              <td class="py-3">
                @if ($menu->status === 'active')
                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">active</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">inactive</span>
                @endif
              </td>
              <td class="py-3 text-center">
                <span class="badge bg-light text-dark border rounded px-3 py-1">{{ $menu->children_count }}</span>
              </td>
              <td class="py-3 text-end pe-4">
                <div class="dropdown dropstart">
                  <a class="btn btn-icon btn-ghost btn-sm d-inline-flex align-items-center justify-content-center text-secondary" href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots-vertical"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                  </a>
                  <ul class="dropdown-menu shadow border-0 py-2">
                    @can('kejarkarir.menus.update')
                    <li>
                      <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('menus.edit', $menu->id) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                        Edit Details
                      </a>
                    </li>
                    @endcan
                    @can('kejarkarir.menus.delete')
                    <li>
                      <hr class="dropdown-divider text-light">
                    </li>
                    <li>
                      <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the menu \&quot;{{ $menu->name }}\&quot;?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                          Delete Menu
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
              <td colspan="7" class="text-center py-5 text-secondary fs-5">No menus found matching current filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="d-flex justify-content-end mt-3">
    {{ $menus->links() }}
  </div>
</div>
@endsection
