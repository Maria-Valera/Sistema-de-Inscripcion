<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif 

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @role('Representante')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal-representante.index') ? 'active' : '' }}" href="{{ route('portal-representante.index') }}">
                            <i class="nav-icon fas fa-fw fa-tachometer-alt"></i>
                            <p>Panel Principal</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal-representante.prosecucion.*') ? 'active' : '' }}" href="{{ route('portal-representante.prosecucion.index') }}">
                            <i class="nav-icon fas fa-fw fa-sync-alt"></i>
                            <p>Inscripción por Prosecución</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portal-representante.carnet.*') ? 'active' : '' }}" href="{{ route('portal-representante.carnet.index') }}">
                            <i class="nav-icon fas fa-fw fa-id-card"></i>
                            <p>Mi Carnet</p>
                        </a>
                    </li>
                @else
                    @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
                @endrole
            </ul>
        </nav>
    </div>

</aside>
