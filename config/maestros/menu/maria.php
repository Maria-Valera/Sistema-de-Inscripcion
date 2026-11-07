<?php

// Opciones del menú de María. Solo María edita este archivo.
// 'calendario' = Calendario y Evaluación, 'notas' = Notas, 'institucion' = Institución.

return [
    'calendario' => [
        [
            'text' => 'Calendario Académico',
            'url' => 'admin/calendario_academico',
            'icon' => 'fas fa-fw fa-calendar-alt',
            'active' => ['admin/calendario_academico*'],
            'can' => 'calendario_academico.ver',
        ],
        [
            'text' => 'Configuración de Evaluación',
            'url' => 'admin/configuracion_evaluacion',
            'icon' => 'fas fa-fw fa-sliders-h',
            'active' => ['admin/configuracion_evaluacion*'],
            'can' => 'configuracion_evaluacion.ver',
        ],
    ],
    'notas' => [],
    'institucion' => [],
];
