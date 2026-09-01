<?php



return [


    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title' => '',
    'title_prefix' => ' ',
    'title_postfix' => ' | Sistema de Inscripción',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    */

    'logo' => '<b>Sistema de Inscripción</b>',
    'logo_img' => 'vendor/adminlte/dist/img/liceo_logo.pn',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => '',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/liceo_logo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/liceo_logo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 140,
            'height' => 140,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cog',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => false,
    'right_sidebar_push' => false,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Custom CSS
    |--------------------------------------------------------------------------
    */

    'custom_css' => [
        'css/admin-custom.css',
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' => [

        // Buscador
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar...',
        ],


        // ================== PANEL PRINCIPAL ==================
        [
            'text' => 'Panel Director',
            'url' => 'home',
            'icon' => 'fas fa-fw fa-tachometer-alt',

            'active' => ['home', 'home/*'],
        ],

        // ================== CONFIGURACIÓN BASE ==================
        [
            'text' => 'Calendario Escolar',
            'url'  => 'admin/anio_escolar',
            'icon' => 'fas fa-calendar-check',
        ],

        // ---------------------------
            [
            'text' => 'Calendario Académico',
            'url'  => 'admin/calendario_academico',
            'icon' => 'fas fa-calendar-alt',
            'active' => ['admin/calendario_academico*'],
        ],
        // ---------------------------

        [
            'text' => 'Histórico',
            'url'  => 'admin/historico',
            'icon' => 'fas fa-history',
        ],

        ['header' => 'INSCRIPCIONES'],

        // ================== INSCRIPCIONES ==================
        [
            'text' => 'INSCRIPCIONES',
            'icon' => 'fas fa-fw fa-clipboard-list',
            'submenu' => [
                [
                    'text' => 'Nuevo Ingreso',
                    'url' => 'admin/transacciones/inscripcion',
                    'icon' => 'fas fa-fw fa-user-plus',
                    'active' => ['admin/transacciones/inscripcion', 'admin/transacciones/inscripcion/*'],
                ],
                [
                    'text' => 'Prosecución',
                    'url' => 'admin/transacciones/inscripcion_prosecucion',
                    'icon' => 'fas fa-fw fa-user-graduate',
                    'active' => ['admin/transacciones/inscripcion_prosecucion', 'admin/transacciones/inscripcion_prosecucion/*'],
                ],
            ],
        ],

        ['header' => 'ASISTENCIA'],

        [
            'text' => 'Permisos y Reposos',
            'url' => 'admin/permisos_reposos',
            'icon' => 'fas fa-fw fa-calendar-check',
            'active' => ['admin/permisos_reposos', 'admin/permisos_reposos/*'],
        ],

        // ================== EVALUACIÓN ==================
        [
            'text' => 'Historial del Percentil',
            'url' => 'admin/transacciones/percentil',
            'icon' => 'fas fa-fw fa-chart-line',
            'active' => ['admin/transacciones/percentil', 'admin/transacciones/percentil/*'],
        ],

        ['header' => 'INFORMACION ACADÉMICA'],

        // ================== PERSONAS ==================
        [
            'text' => 'Alumnos',
            'url' => 'admin/alumnos',
            'icon' => 'fas fa-fw fa-user-graduate',
            'active' => ['admin/alumnos', 'admin/alumnos/*'],
        ],

        [
            'text' => 'Representantes',
            'url' => 'representante',
            'icon' => 'fas fa-fw fa-user-tie',
            'active' => ['representante', 'representante/*'],
        ],

        [
            'text' => 'Aulas',
            'url' => 'aulas',
            'icon' => 'fas fa-fw fa-door-open',
            'active' => ['aulas', 'aulas/*'],
        ],

        // ============================================================
        //  GESTIÓN DOCENTES
        // ============================================================
        ['header' => 'GESTIÓN DE DOCENTES'],

        [
            'text' => 'DOCENTES',
            'icon' => 'fas fa-chalkboard-teacher',
            'submenu' => [
                [
                    'text' => 'Listado de Docentes',
                    'url'  => 'admin/docente',
                    'icon' => 'fas fa-user',
                ],
                [
                    'text' => 'Asignar Area Formación',
                    'url'  => 'admin/transacciones/docente_area_grado',
                    'icon' => 'fas fa-book-reader',
                ],
                [
                    'text' => 'Estudios Realizados',
                    'url'  => 'admin/estudios_realizados',
                    'icon' => 'fas fa-user-graduate',
                ],
                [
                    'text' => 'Expresiones Literarias',
                    'url'  => 'admin/expresion_literaria',
                    'icon' => 'fas fa-feather-alt',
                ],
            ],
        ],




        // ============================================================
        //  GESTIÓN ACADÉMICA
        // ============================================================
        ['header' => 'CONFIGURACIÓN GENERAL'],

        [
            'text' => 'CONFIGURACIONES',
            'icon' => 'fas fa-fw fa-cogs',
            'submenu' => [
                ['header' => 'GESTIÓN ACADÉMICA'],

                [
                    'text' => 'Niveles Academicos',
                    'url' => 'admin/grado',
                    'icon' => 'fas fa-fw fa-layer-group',
                    'active' => ['admin/grado', 'admin/grado/*'],
                ],

                [
                    'text' => 'Bloques Horarios',
                    'url' => 'admin/bloque_horario',
                    'icon' => 'fas fa-fw fa-clock',
                    'active' => ['admin/bloque_horario', 'admin/bloque_horario/*'],
                ],

                                [
                    'text' => 'Reposos y permisos',
                    'url' => 'admin/permisos_reposos',
                    'icon' => 'fas fa-fw fa-clock',
                    'active' => ['admin/permisos_reposos', 'admin/permisos_reposos/*'],
                ],

                // Asignaciones
                [

                    'text' => 'ASIGNACIONES',
                    'icon' => 'fas fa-fw fa-link',
                    'active' => ['admin/transacciones/grado_area_formacion*', 'admin/transacciones/area_estudio_realizado*', 'admin/transacciones/docente*'],
                    'submenu' => [
                        [
                            'text' => 'Áreas de Formación',
                            'url'  => 'admin/area_formacion',
                            'icon' => 'fas fa-book-open',
                        ],
                        [
                            'text' => 'Niveles Academicos → Área de Formacion',
                            'url'  => 'admin/transacciones/grado_area_formacion',
                            'icon' => 'fas fa-link',
                        ],
                        [
                            'text' => 'Área de Formacion → Estudios Realizados',
                            'url'  => 'admin/transacciones/area_estudio_realizado',
                            'icon' => 'fas fa-arrow-right',
                        ],
                    ],
                ],

                ['header' => 'DATOS PERSONALES'],

                [

                    'text' => 'DATOS',
                    'icon' => 'fas fa-fw fa-user',
                    'active' => ['admin/etnia_indigena*', 'admin/discapacidad*', 'admin/ocupacion*'],
                    'submenu' => [
                        [
                            'text' => 'Etnias Indígenas',
                            'url'  => 'admin/etnia_indigena',
                            'icon' => 'fas fa-users',
                        ],
                        [
                            'text' => 'Discapacidades',
                            'url'  => 'admin/discapacidad',
                            'icon' => 'fas fa-wheelchair',
                        ],
                        [
                            'text' => 'Ocupaciones',
                            'url'  => 'admin/ocupacion',
                            'icon' => 'fas fa-briefcase',
                        ],
                    ],
                ],

                ['header' => 'UBICACIÓN GEOGRÁFICA'],

                [
                    'text' => 'UBICACIONES',
                    'icon' => 'fas fa-fw fa-map-marker-alt',
                    'active' => ['admin/estado*', 'admin/municipio*', 'admin/localidad*'],
                    'submenu' => [
                        [
                            'text' => 'Paises',
                            'url'  => 'admin/pais',
                            'icon' => 'fas fa-flag',
                        ],
                        [
                            'text' => 'Estados',
                            'url'  => 'admin/estado',
                            'icon' => 'fas fa-map',
                        ],
                        [
                            'text' => 'Municipios',
                            'url'  => 'admin/municipio',
                            'icon' => 'fas fa-city',
                        ],
                        [
                            'text' => 'Localidades',
                            'url'  => 'admin/localidad',
                            'icon' => 'fas fa-home',
                        ],
                    ],
                ],

                ['header' => 'PARAMETROS GENERALES'],

                [
                    'text' => 'PARAMETROS GENERALES',
                    'icon' => 'fas fa-fw fa-cogs',
                    'active' => ['admin/banco*', 'admin/prefijo_telefono*', 'admin/institucion_procedencia*'],
                    'submenu' => [
                        [
                            'text' => 'Bancos',
                            'url'  => 'admin/banco',
                            'icon' => 'fas fa-university',
                        ],
                        [
                            'text' => 'Prefijos de Teléfono',
                            'url'  => 'admin/prefijo_telefono',
                            'icon' => 'fas fa-phone',
                        ],
                        [
                            'text' => 'Instituciones de Procedencia',
                            'url'  => 'admin/institucion_procedencia',
                            'icon' => 'fas fa-school',
                        ],
                    ],
                ],
            ],
        ],
         // ============================================================
        //  MENU DE LA SUBDIRECTORA
        // ============================================================
        [
            'text' => 'Panel Subdirectora',
            'url' => 'dashboard/subdirectora',
            'icon' => 'fas fa-user-tie',
            'role' => 'Subdirectora',
        ],
        // ============================================================
        //  HORARIOS
        // ============================================================
        [
            'text' => 'Horarios',
            'url' => 'admin/horario',
            'icon' => 'fas fa-clock',
            'role' => 'Subdirectora',
        ],

        // ============================================================
        //  INASISTENCIAS
        // ============================================================
        [
            'text' => 'Gestión de Inasistencias',
            'url' => 'admin/inasistencia',
            'icon' => 'fas fa-clipboard-list',
            'role' => 'Subdirectora',
        ],

// ============================================================
//  MENU DEL DOCENTE
// ============================================================
[
    'text' => 'Panel Docente',
    'url' => 'dashboard/docente',
    'icon' => 'fas fa-chalkboard-teacher',
    'role' => 'Docente',
],



/*         ['header' => 'ADMINISTRACIÓN'],

        [
            'text' => 'Roles y Permisos',
            'url'  => 'admin/roles',
            'icon' => 'fas fa-user-shield',
        ], */
    ],


    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'bootstrap' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://code.jquery.com/jquery-3.6.0.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,
];
