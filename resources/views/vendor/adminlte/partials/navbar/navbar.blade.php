@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}
    d-flex justify-content-between align-items-center py-3 flex-nowrap">

    {{-- Grupo Izquierdo: Hamburguesa + Breadcrumb --}}
    <div class="d-flex align-items-center flex-shrink-1 overflow-hidden" style="gap: 10px;">
        {{-- Navbar left links --}}
        <ul class="navbar-nav">
            {{-- Left sidebar toggler link --}}
            @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

            {{-- Configured left links --}}
            @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

            {{-- Custom left links --}}
            @yield('content_top_nav_left')
        </ul>

        <div class="d-none d-md-block">
            <ul class="navbar-nav overflow-hidden">
                <li class="nav-item d-flex align-items-center overflow-hidden">
                    <nav aria-label="breadcrumb" class="mb-0 text-truncate">
                        @if (Breadcrumbs::exists())
                            {!! Breadcrumbs::render() !!}
                        @endif
                    </nav>
                </li>
            </ul>
        </div>
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

        @media (max-width: 768px) {
            .main-header.navbar {
                padding: 0.5rem 0.75rem !important;
                min-height: 56px;
            }
            .main-header.navbar .breadcrumb,
            .navbar .breadcrumb {
                font-size: 13px !important;
            }
            .main-header.navbar .breadcrumb .breadcrumb-item {
                max-width: 130px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .navbar-nav.ms-auto,
            .navbar-nav.ml-auto {
                gap: 8px !important;
            }
        }

        @media (max-width: 576px) {
            .main-header.navbar {
                padding: 0.4rem 0.5rem !important;
            }
            .main-header.navbar .breadcrumb .breadcrumb-item:not(.active) {
                display: none !important;
            }
            .main-header.navbar .breadcrumb .breadcrumb-item.active::before {
                display: none !important;
            }
            .main-header.navbar .breadcrumb .breadcrumb-item.active {
                max-width: 100px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    </style>

    {{-- Grupo Derecho: Selector de Año + Perfil de Usuario --}}
    <ul class="navbar-nav ms-auto ml-auto d-flex align-items-center flex-shrink-0" style="gap: 16px;">
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

<script>
    (function () {
        function syncTopbarHeight() {
            const nav = document.querySelector('.main-header.navbar');
            if (!nav) return;
            document.documentElement.style.setProperty('--topbar-height', nav.offsetHeight + 'px');
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', syncTopbarHeight);
        } else {
            syncTopbarHeight();
        }
        window.addEventListener('resize', syncTopbarHeight);
    })();
</script>
