<?php

// Módulos y permisos de Stephania (misma estructura que config/modulos.php). Solo Stephania edita este archivo.

return [
    'anio_escolar' => [
        'nombre' => 'Años Escolares',
        'icono' => 'fas fa-calendar-alt',
        'ruta' => 'admin.anio-escolar.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'anio_escolar.ver',
            'anio_escolar.cambiar',
            'anio_escolar.gestionar',
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
    'escala_literal' => [
        'nombre' => 'Escala Literal',
        'icono' => 'fas fa-sort-alpha-down',
        'ruta' => 'admin.escala-literal.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'escala_literal.ver',
            'escala_literal.gestionar',
        ],
    ],
    'autoridad_firmante' => [
        'nombre' => 'Autoridades Firmantes',
        'icono' => 'fas fa-signature',
        'ruta' => 'admin.autoridad-firmante.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'autoridad_firmante.ver',
            'autoridad_firmante.gestionar',
        ],
    ],
];
