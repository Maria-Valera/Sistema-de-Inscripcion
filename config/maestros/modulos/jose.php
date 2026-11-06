<?php

// Módulos y permisos de José (misma estructura que config/modulos.php). Solo José edita este archivo.

return [
    'pensums' => [
        'nombre' => 'Planes de Estudio (Pensums)',
        'icono' => 'fas fa-graduation-cap',
        'ruta' => 'admin.pensums.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'pensums.ver',
            'pensums.gestionar',
        ],
    ],
    'malla_curricular' => [
        'nombre' => 'Malla Curricular',
        'icono' => 'fas fa-layer-group',
        'ruta' => 'admin.malla-curricular.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'malla_curricular.ver',
            'malla_curricular.gestionar',
        ],
    ],
    'conversion_reglas' => [
        'nombre' => 'Reglas de Conversión',
        'icono' => 'fas fa-exchange-alt',
        'ruta' => 'admin.conversion-reglas.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'conversion_reglas.ver',
            'conversion_reglas.gestionar',
        ],
    ],
    'menciones' => [
        'nombre' => 'Menciones y Ciclos',
        'icono' => 'fas fa-certificate',
        'ruta' => 'admin.menciones.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'menciones.ver',
            'menciones.gestionar',
        ],
    ],
];
