<?php

// Qué roles reciben los módulos de José (misma estructura que config/roles_modulos.php).

return [
    'Admin' => [
        'pensums'           => '*',
        'malla_curricular'  => '*',
        'conversion_reglas' => '*',
        'menciones'         => '*',
    ],
    'Subdirectora' => [
        'pensums'           => '*',
        'malla_curricular'  => '*',
        'conversion_reglas' => '*',
        'menciones'         => '*',
    ],
];
