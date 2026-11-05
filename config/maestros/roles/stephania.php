<?php

// Qué roles reciben los módulos de Stephania (misma estructura que config/roles_modulos.php).

return [
    'Admin' => [
        'anio_escolar' => '*',
        'institucion' => '*',
    ],
    'Subdirectora' => [
        'anio_escolar' => '*',
    ],
];
