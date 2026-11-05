<?php

// Opciones del menú de Stephania. Solo Stephania edita este archivo.
// 'calendario' = Calendario y Evaluación, 'notas' = Notas, 'institucion' = Institución.

return [
    'calendario'  => [],
    'notas'       => [],
    'institucion' => [
        [
            'text' => 'Datos del Plantel',
            'url'  => 'admin/institucion',
            'icon' => 'fas fa-school',
            'can'  => 'institucion.ver',
        ],
    ],
];
