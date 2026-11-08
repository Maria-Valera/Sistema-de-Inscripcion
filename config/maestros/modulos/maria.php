<?php

// Modulos y permisos de Maria (misma estructura que config/modulos.php). Solo Maria edita este archivo.

return [
    'calendario_academico' => [
        'nombre' => 'Calendario Académico',
        'icono' => 'fas fa-calendar-alt',
        'ruta' => 'admin.calendario_academico.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'calendario_academico.ver',
            'calendario_academico.gestionar',
        ],
    ],

    'configuracion_evaluacion' => [
        'nombre' => 'Configuración de Evaluación',
        'icono' => 'fas fa-sliders-h',
        'ruta' => 'admin.configuracion_evaluacion.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'configuracion_evaluacion.ver',
            'configuracion_evaluacion.gestionar',
        ],
    ],
];
