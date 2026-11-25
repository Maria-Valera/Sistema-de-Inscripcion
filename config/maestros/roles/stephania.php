<?php

// Qué roles reciben los módulos de Stephania (misma estructura que config/roles_modulos.php).

return [
    'Admin' => [
        'anio_escolar' => '*',
        'institucion' => '*',
        'escala_literal' => '*',
        'autoridad_firmante' => '*',
        'carga_academica' => '*',
    ],
    'Subdirectora' => [
        'anio_escolar' => '*',
        'escala_literal' => '*',
        'carga_academica' => '*',
    ],
    'Docente' => [
        'carga_academica' => ['ver'],
    ],
];
