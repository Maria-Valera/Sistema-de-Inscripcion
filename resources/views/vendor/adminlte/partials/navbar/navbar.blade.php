@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>
    <ul class="navbar-nav ml-left">
        <li class="nav-item align-self-center">
            <nav aria-label="breadcrumb" class="mb-0">
                @if (Breadcrumbs::exists())
                    {!! Breadcrumbs::render() !!}
                @endif 
            </nav>
        </li>
    </ul>
    <style>
        .navbar .breadcrumb {
            background: transparent;
            margin-bottom: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.4;
            align-items: center;
        }

        .navbar .breadcrumb .breadcrumb-item a {
            color: #64748B;
            text-decoration: none;
            font-weight: 400;
            transition: color 0.15s ease;
        }

        .navbar .breadcrumb .breadcrumb-item a:hover {
            color: #0F172A;
            text-decoration: none;
        }

        .navbar .breadcrumb .breadcrumb-item.active {
            color: #0F172A;
            font-weight: 500;
        }

        .navbar .breadcrumb .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
            color: #CBD5E1;
            padding: 0 8px;
        }
    </style>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
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
