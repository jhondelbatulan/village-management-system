@props(['item', 'menuService', 'isChild' => false])

@php
    $hasChildren = !empty($item['children']);
    $isActive = $menuService->isActive($item);
    $route = $item['route'] ?? '#';
    $routeParams = $item['route_params'] ?? [];
    $url = ($route !== '#') ? route($route, $routeParams) : '#';
    $itemId = 'menu-' . \Illuminate\Support\Str::slug($item['label']);
@endphp

<li class="nav-item {{ $hasChildren ? 'has-submenu' : '' }}">
    @if($hasChildren)
        <a class="nav-link submenu-toggle {{ $isActive ? 'active' : '' }}"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#{{ $itemId }}"
           aria-expanded="{{ $isActive ? 'true' : 'false' }}"
           aria-controls="{{ $itemId }}">
            <i class="bi {{ $item['icon'] ?? 'bi-circle' }} me-2"></i>
            <span>{{ $item['label'] }}</span>
            <i class="bi bi-chevron-down submenu-arrow ms-auto"></i>
        </a>
        <div class="collapse {{ $isActive ? 'show' : '' }}" id="{{ $itemId }}">
            <ul class="nav flex-column submenu">
                @foreach($item['children'] as $child)
                    <x-menu-item :item="$child" :menuService="$menuService" :isChild="true" />
                @endforeach
            </ul>
        </div>
    @else
        <a class="nav-link {{ $isActive ? 'active' : '' }} {{ $isChild ? 'submenu-link' : '' }}"
           href="{{ $url }}">
            <i class="bi {{ $item['icon'] ?? 'bi-circle' }} me-2"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endif
</li>
