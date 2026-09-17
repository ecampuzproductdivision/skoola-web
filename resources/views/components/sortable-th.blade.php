@props([
    'label' => '',
    'column' => null,
    'client' => false,
    'sort' => request('sort'),
    'direction' => request('direction', 'asc'),
    'class' => '',
    'routeName' => null,
])

@php
    $isSortable = filled($column);
    $isActive = $isSortable && ! $client && $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';

    $queryParams = array_merge(
        request()->except(['sort', 'direction', 'page']),
        ['sort' => $column, 'direction' => $nextDirection]
    );
    $url = $routeName ? route($routeName, $queryParams) : url()->current() . '?' . http_build_query($queryParams);
    $icon = ! $isActive ? 'ti-arrows-sort opacity-50' : ($direction === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down');
@endphp

<th class="{{ $class }}">
    @if ($isSortable && $client)
        <button type="button" class="btn btn-link text-reset text-decoration-none d-inline-flex align-items-center gap-1 sortable-client" data-sort-column="{{ $column }}" aria-label="Sort by {{ $label }}">
            <span>{{ $label }}</span>
            <i class="ti ti-arrows-sort opacity-50"></i>
        </button>
    @elseif ($isSortable)
        <a href="{{ $url }}" class="text-reset text-decoration-none d-inline-flex align-items-center gap-1" aria-label="Sort by {{ $label }}">
            <span>{{ $label }}</span>
            <i class="ti {{ $icon }}"></i>
        </a>
    @else
        {{ $label }}
    @endif
</th>