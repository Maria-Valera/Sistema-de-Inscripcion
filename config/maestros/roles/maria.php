<?php

// Qué roles reciben los módulos de María (misma estructura que config/roles_modulos.php).
// '*' = todos los permisos del modulo.

return [
    'Admin' => [
        'calendario_academico' => '*',
        'configuracion_evaluacion' => '*',
        'lapsos' => '*',
        'cortes' => '*',
    ],
    'Subdirectora' => [
        'calendario_academico' => '*',
        'configuracion_evaluacion' => '*',
        'lapsos' => '*',
        'cortes' => '*',
    ],
];
