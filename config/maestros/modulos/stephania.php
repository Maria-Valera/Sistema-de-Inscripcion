<?php

// Módulos y permisos de Stephania (misma estructura que config/modulos.php). Solo Stephania edita este archivo.

return [
    'anio_escolar' => [
        'nombre' => 'Año Escolar',
        'icono' => 'fas fa-calendar-alt',
        'ruta' => null,
        'grupo' => 'configuracion',
        'permisos' => [
            'anio_escolar.ver',
            'anio_escolar.cambiar',
        ],
    ],
    'institucion' => [
        'nombre' => 'Datos del Plantel',
        'icono' => 'fas fa-school',
        'ruta' => 'admin.institucion.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'institucion.ver',
            'institucion.gestionar',
        ],
    ],
];
