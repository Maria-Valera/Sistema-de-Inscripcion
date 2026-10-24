<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Módulos por Rol
    |--------------------------------------------------------------------------
    |
    | Este archivo define qué módulos y permisos tiene cada rol por defecto.
    | Formato: rol -> módulo -> permisos
    | - '*' significa todos los permisos del módulo
    | - Array con permisos específicos significa solo esos permisos
    |
    */

    'Representante' => [
        'portal_prosecucion' => '*',
        'portal_carnets' => ['ver_estudiante', 'generar_estudiante'],
        'portal_horarios' => ['ver_estudiante', 'generar_estudiante'],
        'portal_constancias' => '*',
    ],

    'Docente' => [
        'portal_carnets' => ['ver_docente', 'generar_docente'],
        'portal_horarios' => ['ver_docente', 'generar_docente'],
        'portal_carga_academica' => '*',
    ],

    'Subdirectora' => [
        'asistencia' => '*',
        'justificaciones' => '*',
        'reportes_asistencia' => '*',
        'permisos_reposos' => '*',
        'inasistencia_docente' => '*',
        'calendario' => '*',
        'grados' => '*',
        'secciones' => '*',
        'bloques_horarios' => '*',
        'grado_area_formacion' => '*',
        'estudios_realizados' => '*',
        'area_estudio_realizado' => '*',
        'ubicacion_geografica' => '*',
        'catalogos' => '*',
        'instituciones' => '*',
        'grupos_estables' => '*',
        'aulas' => '*',
        'areas_formacion' => '*',
        'personal' => ['ver'],
        'horarios' => ['ver'],
        'asignacion_docente' => ['ver'],
    ],

    'Admin' => [
        // Módulos de gestión del Director
        'horarios' => '*',
        'aulas' => '*',
        'personal' => '*',
        'areas_formacion' => '*',
        'actas' => '*',
        'usuarios_accesos' => '*',
        'auditoria' => '*',
        
        // Prosecución con activar
        'prosecucion' => '*',
        
        // Módulos de configuración
        'calendario' => '*',
        'grados' => '*',
        'secciones' => '*',
        'bloques_horarios' => '*',
        'grado_area_formacion' => '*',
        'estudios_realizados' => '*',
        'area_estudio_realizado' => '*',
        'ubicacion_geografica' => '*',
        'catalogos' => '*',
        'instituciones' => '*',
        'grupos_estables' => '*',
        
        // Módulos académicos y control
        'inscripciones' => '*',
        'alumnos' => '*',
        'representantes' => '*',
        'asignacion_docente' => '*',
        'historico' => '*',
        'percentil' => '*',
        'permisos_reposos' => '*',
        'inasistencia_docente' => '*',
        'asistencia' => '*',
        'justificaciones' => '*',
        'reportes_asistencia' => '*',
    ],


    'Editor' => [],

    'User' => [],
];
