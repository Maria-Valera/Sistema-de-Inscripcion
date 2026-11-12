@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}
    d-flex justify-content-between align-items-center py-3">

    {{-- Grupo Izquierdo: Hamburguesa + Breadcrumb --}}
    <div class="d-flex align-items-center" style="gap: 10px;">
        {{-- Navbar left links --}}
        <ul class="navbar-nav">
            {{-- Left sidebar toggler link --}}
            @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

            {{-- Configured left links --}}
            @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

            {{-- Custom left links --}}
            @yield('content_top_nav_left')
        </ul>

        <ul class="navbar-nav">
            <li class="nav-item d-flex align-items-center">
                <nav aria-label="breadcrumb" class="mb-0">
                    @if (Breadcrumbs::exists())
                        {!! Breadcrumbs::render() !!}
                    @endif
                </nav>
            </li>
        </ul>
    </div>

    <style>
        .main-header.navbar {
            border-bottom: 1px solid #E2E8F0;
            min-height: 66px;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .main-header.navbar .breadcrumb,
        .navbar .breadcrumb {
            background: transparent !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
            font-size: 16px !important;
            line-height: 1.4;
            align-items: center;
        }

        .main-header.navbar .breadcrumb .breadcrumb-item a,
        .navbar .breadcrumb .breadcrumb-item a {
            color: #64748B !important;
            text-decoration: none !important;
            border-bottom: none !important;
            font-weight: 400;
            transition: color 0.15s ease;
        }

        .main-header.navbar .breadcrumb .breadcrumb-item a:hover,
        .main-header.navbar .breadcrumb .breadcrumb-item a:focus,
        .navbar .breadcrumb .breadcrumb-item a:hover,
        .navbar .breadcrumb .breadcrumb-item a:focus {
            color: #0F172A !important;
            text-decoration: none !important;
        }

        .main-header.navbar .breadcrumb .breadcrumb-item.active,
        .navbar .breadcrumb .breadcrumb-item.active {
            color: #0F172A !important;
            font-weight: 600 !important;
        }

        .main-header.navbar .breadcrumb .breadcrumb-item+.breadcrumb-item::before,
        .navbar .breadcrumb .breadcrumb-item+.breadcrumb-item::before {
            content: "/" !important;
            color: #CBD5E1 !important;
            padding: 0 8px !important;
        }
    </style>

    {{-- Grupo Derecho: Selector de Año + Perfil de Usuario --}}
    <ul class="navbar-nav ms-auto ml-auto d-flex align-items-center" style="gap: 16px;">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Indicador de Año Escolar Seleccionado --}}
        @include('partials.indicador-anio')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
