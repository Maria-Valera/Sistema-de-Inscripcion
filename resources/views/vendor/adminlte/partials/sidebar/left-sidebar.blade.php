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
                @php
                    // Crear una instancia de AdminLte para que compile el menú según los permisos del usuario actual en esta request
                    $freshAdminLte = new \JeroenNoten\LaravelAdminLte\AdminLte(config('adminlte.filters', []));
                    $sidebarMenu = $freshAdminLte->menu('sidebar');

                    if (auth()->check()) {
                        $dashboardUrl = auth()->user()->rutaDashboard();
                        foreach ($sidebarMenu as &$menuItem) {
                            if (isset($menuItem['text']) && $menuItem['text'] === 'Panel Principal') {
                                $menuItem['href'] = $dashboardUrl;
                                $menuItem['url'] = $dashboardUrl;
                            }
                        }
                        unset($menuItem);
                    }

                    // Función para limpiar headers huérfanos y submenús vacíos recursivamente
                    if (!function_exists('sanitizarMenuAdminLte')) {
                        function sanitizarMenuAdminLte(array $menu): array {
                            // 1. Filtrar recursivamente submenús
                            foreach ($menu as $key => &$item) {
                                if (isset($item['submenu']) && is_array($item['submenu'])) {
                                    $item['submenu'] = sanitizarMenuAdminLte($item['submenu']);
                                    if (empty($item['submenu'])) {
                                        unset($menu[$key]);
                                    }
                                }
                            }
                            unset($item);

                            $menu = array_values($menu);

                            // 2. Eliminar headers que se hayan quedado sin ítems interactivos debajo
                            $cleaned = [];
                            $count = count($menu);
                            for ($i = 0; $i < $count; $i++) {
                                $item = $menu[$i];
                                if (isset($item['header'])) {
                                    $hasChild = false;
                                    for ($j = $i + 1; $j < $count; $j++) {
                                        if (isset($menu[$j]['header'])) {
                                            break;
                                        }
                                        $hasChild = true;
                                        break;
                                    }
                                    if ($hasChild) {
                                        $cleaned[] = $item;
                                    }
                                } else {
                                    $cleaned[] = $item;
                                }
                            }

                            return $cleaned;
                        }
                    }

                    $sidebarMenu = sanitizarMenuAdminLte($sidebarMenu);
                @endphp
                @each('adminlte::partials.sidebar.menu-item', $sidebarMenu, 'item')
            </ul>
        </nav>
    </div>

</aside>

