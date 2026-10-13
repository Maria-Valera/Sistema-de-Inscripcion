<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Módulos del Sistema
    |--------------------------------------------------------------------------
    |
    | Este archivo define los módulos del sistema y sus permisos asociados.
    | Cada módulo es un grupo de permisos con el formato "modulo.accion".
    |
    | Formato de permisos:
    | - modulo.ver: Ver el listado del módulo
    | - modulo.crear: Crear nuevos registros
    | - modulo.editar: Editar registros existentes
    | - modulo.eliminar: Eliminar registros
    | - modulo.verificar: Verificar existencia de registros (verificación AJAX)
    | - modulo.restaurar: Restaurar registros eliminados (soft deletes)
    | - modulo.reporte: Generar reportes PDF
    |
    */

    // ===== MÓDULO: CALENDARIO ESCOLAR =====
    'anio_escolar' => [
        'nombre' => 'Calendario Escolar',
        'descripcion' => 'Gestión del año escolar y sus fechas',
        'permisos' => [
            'anio_escolar.ver',
            'anio_escolar.crear',
            'anio_escolar.editar',
            'anio_escolar.eliminar',
            'anio_escolar.extender',
        ],
    ],

    // ===== MÓDULO: UBICACIÓN GEOGRÁFICA =====
    'pais' => [
        'nombre' => 'Países',
        'descripcion' => 'Gestión de países',
        'permisos' => [
            'pais.ver',
            'pais.crear',
            'pais.editar',
            'pais.eliminar',
            'pais.verificar',
        ],
    ],

    'estado' => [
        'nombre' => 'Estados',
        'descripcion' => 'Gestión de estados',
        'permisos' => [
            'estado.ver',
            'estado.crear',
            'estado.editar',
            'estado.eliminar',
            'estado.verificar',
        ],
    ],

    'municipio' => [
        'nombre' => 'Municipios',
        'descripcion' => 'Gestión de municipios',
        'permisos' => [
            'municipio.ver',
            'municipio.crear',
            'municipio.editar',
            'municipio.eliminar',
            'municipio.verificar',
        ],
    ],

    'localidad' => [
        'nombre' => 'Localidades',
        'descripcion' => 'Gestión de localidades',
        'permisos' => [
            'localidad.ver',
            'localidad.crear',
            'localidad.editar',
            'localidad.eliminar',
            'localidad.verificar',
        ],
    ],

    'institucion_procedencia' => [
        'nombre' => 'Instituciones de Procedencia',
        'descripcion' => 'Gestión de instituciones de procedencia',
        'permisos' => [
            'institucion_procedencia.ver',
            'institucion_procedencia.crear',
            'institucion_procedencia.editar',
            'institucion_procedencia.eliminar',
            'institucion_procedencia.verificar',
        ],
    ],

    // ===== MÓDULO: CATÁLOGOS =====
    'banco' => [
        'nombre' => 'Bancos',
        'descripcion' => 'Gestión de bancos',
        'permisos' => [
            'banco.ver',
            'banco.crear',
            'banco.editar',
            'banco.eliminar',
            'banco.verificar',
        ],
    ],

    'etnia_indigena' => [
        'nombre' => 'Etnias Indígenas',
        'descripcion' => 'Gestión de etnias indígenas',
        'permisos' => [
            'etnia_indigena.ver',
            'etnia_indigena.crear',
            'etnia_indigena.editar',
            'etnia_indigena.eliminar',
            'etnia_indigena.verificar',
        ],
    ],

    'ocupacion' => [
        'nombre' => 'Ocupaciones',
        'descripcion' => 'Gestión de ocupaciones',
        'permisos' => [
            'ocupacion.ver',
            'ocupacion.crear',
            'ocupacion.editar',
            'ocupacion.eliminar',
            'ocupacion.verificar',
        ],
    ],

    'expresion_literaria' => [
        'nombre' => 'Expresiones Literarias',
        'descripcion' => 'Gestión de expresiones literarias',
        'permisos' => [
            'expresion_literaria.ver',
            'expresion_literaria.crear',
            'expresion_literaria.editar',
            'expresion_literaria.eliminar',
            'expresion_literaria.verificar',
        ],
    ],

    'discapacidad' => [
        'nombre' => 'Discapacidades',
        'descripcion' => 'Gestión de discapacidades',
        'permisos' => [
            'discapacidad.ver',
            'discapacidad.crear',
            'discapacidad.editar',
            'discapacidad.eliminar',
            'discapacidad.verificar',
        ],
    ],

    'prefijo_telefono' => [
        'nombre' => 'Prefijos Telefónicos',
        'descripcion' => 'Gestión de prefijos telefónicos',
        'permisos' => [
            'prefijo_telefono.ver',
            'prefijo_telefono.crear',
            'prefijo_telefono.editar',
            'prefijo_telefono.eliminar',
            'prefijo_telefono.verificar',
        ],
    ],

    // ===== MÓDULO: ACADÉMICO =====
    'grado' => [
        'nombre' => 'Grados',
        'descripcion' => 'Gestión de grados escolares',
        'permisos' => [
            'grado.ver',
            'grado.crear',
            'grado.editar',
            'grado.eliminar',
            'grado.verificar',
        ],
    ],

    'area_formacion' => [
        'nombre' => 'Áreas de Formación',
        'descripcion' => 'Gestión de áreas de formación y grupos estables',
        'permisos' => [
            'area_formacion.ver',
            'area_formacion.crear',
            'area_formacion.editar',
            'area_formacion.eliminar',
            'area_formacion.verificar',
            'area_formacion.grupo_estable',
        ],
    ],

    'grado_area_formacion' => [
        'nombre' => 'Grado-Área Formación',
        'descripcion' => 'Asignación de áreas de formación a grados',
        'permisos' => [
            'grado_area_formacion.ver',
            'grado_area_formacion.crear',
            'grado_area_formacion.editar',
            'grado_area_formacion.eliminar',
        ],
    ],

    'estudios_realizados' => [
        'nombre' => 'Estudios Realizados',
        'descripcion' => 'Gestión de niveles de estudios',
        'permisos' => [
            'estudios_realizados.ver',
            'estudios_realizados.crear',
            'estudios_realizados.editar',
            'estudios_realizados.eliminar',
            'estudios_realizados.verificar',
        ],
    ],

    'area_estudio_realizado' => [
        'nombre' => 'Área Estudio Realizado',
        'descripcion' => 'Asignación de áreas a estudios realizados',
        'permisos' => [
            'area_estudio_realizado.ver',
            'area_estudio_realizado.crear',
            'area_estudio_realizado.editar',
            'area_estudio_realizado.eliminar',
        ],
    ],

    'bloque_horario' => [
        'nombre' => 'Bloques Horarios',
        'descripcion' => 'Gestión de bloques horarios',
        'permisos' => [
            'bloque_horario.ver',
            'bloque_horario.crear',
            'bloque_horario.editar',
            'bloque_horario.eliminar',
            'bloque_horario.verificar',
        ],
    ],

    'seccion_aula' => [
        'nombre' => 'Sección-Aula',
        'descripcion' => 'Gestión de secciones por aula',
        'permisos' => [
            'seccion_aula.ver',
            'seccion_aula.crear',
            'seccion_aula.editar',
            'seccion_aula.eliminar',
            'seccion_aula.verificar',
        ],
    ],

    // ===== MÓDULO: PERSONAL =====
    'docente' => [
        'nombre' => 'Docentes',
        'descripcion' => 'Gestión de docentes',
        'permisos' => [
            'docente.ver',
            'docente.crear',
            'docente.editar',
            'docente.eliminar',
            'docente.estudios',
            'docente.disponibilidad',
            'docente.reporte',
        ],
    ],

    'docente_area_grado' => [
        'nombre' => 'Docente-Área-Grado',
        'descripcion' => 'Asignación de docentes a áreas y grados',
        'permisos' => [
            'docente_area_grado.ver',
            'docente_area_grado.crear',
            'docente_area_grado.editar',
            'docente_area_grado.eliminar',
            'docente_area_grado.reporte',
        ],
    ],

    // ===== MÓDULO: ESTUDIANTES =====
    'alumnos' => [
        'nombre' => 'Alumnos',
        'descripcion' => 'Gestión de alumnos',
        'permisos' => [
            'alumnos.ver',
            'alumnos.crear',
            'alumnos.editar',
            'alumnos.eliminar',
            'alumnos.reporte',
        ],
    ],

    'representante' => [
        'nombre' => 'Representantes',
        'descripcion' => 'Gestión de representantes',
        'permisos' => [
            'representante.ver',
            'representante.crear',
            'representante.editar',
            'representante.eliminar',
        ],
    ],

    // ===== MÓDULO: INSCRIPCIONES =====
    'inscripcion' => [
        'nombre' => 'Inscripciones (Nuevo Ingreso)',
        'descripcion' => 'Gestión de inscripciones de nuevo ingreso',
        'permisos' => [
            'inscripcion.ver',
            'inscripcion.crear',
            'inscripcion.editar',
            'inscripcion.eliminar',
            'inscripcion.restaurar',
            'inscripcion.reporte',
        ],
    ],

    'inscripcion_prosecucion' => [
        'nombre' => 'Inscripción Prosecución',
        'descripcion' => 'Gestión de inscripciones por prosecución',
        'permisos' => [
            'inscripcion_prosecucion.ver',
            'inscripcion_prosecucion.crear',
            'inscripcion_prosecucion.eliminar',
            'inscripcion_prosecucion.restaurar',
            'inscripcion_prosecucion.reporte',
        ],
    ],

    'percentil' => [
        'nombre' => 'Percentil',
        'descripcion' => 'Cálculo de percentiles',
        'permisos' => [
            'percentil.ver',
            'percentil.ejecutar',
        ],
    ],

    // ===== MÓDULO: CONTROL DE ESTUDIOS =====
    'historico' => [
        'nombre' => 'Histórico',
        'descripcion' => 'Gestión de datos históricos',
        'permisos' => [
            'historico.ver',
        ],
    ],

    'acta_entrevista' => [
        'nombre' => 'Acta de Entrevista',
        'descripcion' => 'Gestión de actas de entrevista',
        'permisos' => [
            'acta_entrevista.ver',
            'acta_entrevista.crear',
            'acta_entrevista.editar',
            'acta_entrevista.eliminar',
        ],
    ],

    'permisos_reposos' => [
        'nombre' => 'Permisos y Reposos',
        'descripcion' => 'Gestión de permisos y reposos del personal',
        'permisos' => [
            'permisos_reposos.ver',
            'permisos_reposos.crear',
            'permisos_reposos.editar',
            'permisos_reposos.eliminar',
            'permisos_reposos.verificar',
        ],
    ],

    'inasistencia' => [
        'nombre' => 'Inasistencia',
        'descripcion' => 'Gestión de inasistencias',
        'permisos' => [
            'inasistencia.ver',
            'inasistencia.justificar',
        ],
    ],

    // ===== MÓDULO: HORARIOS =====
    'horario' => [
        'nombre' => 'Horarios',
        'descripcion' => 'Gestión de horarios de clases',
        'permisos' => [
            'horario.ver',
            'horario.crear',
            'horario.editar',
            'horario.eliminar',
        ],
    ],

    // ===== MÓDULO: ADMINISTRACIÓN =====
    'roles' => [
        'nombre' => 'Roles y Permisos',
        'descripcion' => 'Gestión de roles y permisos del sistema',
        'permisos' => [
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',
            'roles.permisos',
        ],
    ],

    'usuarios' => [
        'nombre' => 'Usuarios',
        'descripcion' => 'Gestión de usuarios del sistema',
        'permisos' => [
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'usuarios.modulos',
        ],
    ],

    'auditoria' => [
        'nombre' => 'Auditoría',
        'descripcion' => 'Consulta de registros de auditoría',
        'permisos' => [
            'auditoria.ver',
        ],
    ],

    // ===== MÓDULO: PORTAL REPRESENTANTE =====
    'portal_representante' => [
        'nombre' => 'Portal del Representante',
        'descripcion' => 'Acceso al portal para representantes',
        'permisos' => [
            'portal_representante.acceder',
            'portal_representante.prosecucion',
            'portal_representante.carnet',
        ],
    ],

    // ===== MÓDULO: DASHBOARD =====
    'dashboard' => [
        'nombre' => 'Dashboard',
        'descripcion' => 'Acceso a dashboard según rol',
        'permisos' => [
            'dashboard.admin',
            'dashboard.subdirectora',
            'dashboard.docente',
        ],
    ],
];
