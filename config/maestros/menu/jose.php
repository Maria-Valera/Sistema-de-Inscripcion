<?php

// Opciones del menú de José. Solo José edita este archivo.
// 'calendario' = Calendario y Evaluación, 'notas' = Notas, 'institucion' = Institución.

return [
    'calendario'  => [],
    'notas'       => [
        [
            'text' => 'Planes de Estudio (Pensums)',
            'url'  => 'admin/pensums',
            'icon' => 'fas fa-graduation-cap',
            'can'  => 'pensums.ver',
        ],
        [
            'text' => 'Malla Curricular',
            'url'  => 'admin/malla-curricular',
            'icon' => 'fas fa-layer-group',
            'can'  => 'malla_curricular.ver',
        ],
        [
            'text' => 'Reglas de Conversión',
            'url'  => 'admin/conversion-reglas',
            'icon' => 'fas fa-exchange-alt',
            'can'  => 'conversion_reglas.ver',
        ],
        [
            'text' => 'Menciones y Ciclos',
            'url'  => 'admin/menciones',
            'icon' => 'fas fa-certificate',
            'can'  => 'menciones.ver',
        ],
    ],
    'institucion' => [],
];
