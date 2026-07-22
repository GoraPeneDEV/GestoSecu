@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$checkMenuActive = function($menuItem, $currentRoute) use (&$checkMenuActive) {
    if (!isset($menuItem->submenu)) {
        $slugs = gettype($menuItem->slug) === 'array' ? $menuItem->slug : [$menuItem->slug];
        return in_array($currentRoute, $slugs);
    }
    foreach ($menuItem->submenu as $child) {
        if ($checkMenuActive($child, $currentRoute)) return true;
    }
    return false;
};
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-text demo menu-text fw-bold">{{ config('app.name', 'GestoSecu') }}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small">
            <span class="menu-header-text">{{ $menu->menuHeader }}</span>
        </li>
      @else

      {{-- active menu method --}}
      @php
      $activeClass = null;
      $currentRouteName = Route::currentRouteName();

      if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
      }
      elseif (isset($menu->submenu)) {
        if (gettype($menu->slug) === 'array') {
          foreach($menu->slug as $slug){
            if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
              $activeClass = 'active open';
            }
          }
        }
        else{
          if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
            $activeClass = 'active open';
          }
        }
        if ($activeClass === null && $checkMenuActive($menu, $currentRouteName)) {
          $activeClass = 'active open';
        }
      }
      @endphp

      {{-- main menu --}}
      @php
        $hasPermission = true;
        if (isset($menu->permission)) {
          $hasPermission = auth()->check() && auth()->user()->can($menu->permission);
        }
      @endphp

      @if($hasPermission)
      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
          @isset($menu->icon)
            <i class="{{ $menu->icon }}"></i>
          @endisset
          <div>{{ $menu->name ?? '' }}</div>
          @isset($menu->badge)
            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @isset($menu->submenu)
          @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
        @endisset
      </li>
      @endif
      @endif
    @endforeach
  </ul>

</aside>
