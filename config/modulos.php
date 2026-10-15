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
    | Estructura:
    | - clave: identificador único del módulo
    | - nombre: nombre legible para mostrar en UI
    | - icono: icono de FontAwesome (ej: 'fas fa-users')
    | - ruta: nombre de ruta existente en routes/web.php, o null si no tiene ruta
    | - grupo: grupo para organizar el menú (configuracion, academico, asistencia, portal, seguridad)
    | - permisos: array de permisos, el primero es el de acceso
    |
    | Regla de acceso: un módulo aparece en el menú si el usuario tiene CUALQUIER permiso de ese módulo
    |
    */

    // ===== MÓDULOS BASE =====
    'horarios' => [
        'nombre' => 'Horarios',
        'icono' => 'fas fa-calendar-alt',
        'ruta' => 'admin.horario.index',
        'grupo' => 'academico',
        'permisos' => [
            'horarios.ver',
            'horarios.crear',
            'horarios.editar',
            'horarios.eliminar',
        ],
    ],

    'aulas' => [
        'nombre' => 'Aulas',
        'icono' => 'fas fa-chalkboard-teacher',
        'ruta' => 'admin.seccion_aula.index', // Provisional hasta tener ruta específica de aulas
        'grupo' => 'academico',
        'permisos' => [
            'aulas.ver',
            'aulas.crear',
            'aulas.editar',
            'aulas.eliminar',
        ],
    ],

    'personal' => [
        'nombre' => 'Personal',
        'icono' => 'fas fa-user-tie',
        'ruta' => 'admin.docente.index',
        'grupo' => 'academico',
        'permisos' => [
            'personal.ver',
            'personal.crear',
            'personal.editar',
            'personal.eliminar',
            'personal.estudios',
            'personal.disponibilidad',
            'personal.reporte',
        ],
    ],

    'areas_formacion' => [
        'nombre' => 'Áreas de Formación',
        'icono' => 'fas fa-book',
        'ruta' => 'admin.area_formacion.index',
        'grupo' => 'academico',
        'permisos' => [
            'areas_formacion.ver',
            'areas_formacion.crear',
            'areas_formacion.editar',
            'areas_formacion.eliminar',
            'areas_formacion.grupo_estable',
        ],
    ],

    'prosecucion' => [
        'nombre' => 'Prosecución',
        'icono' => 'fas fa-graduation-cap',
        'ruta' => 'admin.transacciones.inscripcion_prosecucion.index',
        'grupo' => 'academico',
        'permisos' => [
            'prosecucion.ver',
            'prosecucion.registrar',
            'prosecucion.consultar',
            'prosecucion.activar',
        ],
    ],

    // ===== MÓDULOS DEL PORTAL =====
    'portal_prosecucion' => [
        'nombre' => 'Prosecución (Portal)',
        'icono' => 'fas fa-graduation-cap',
        'ruta' => 'portal-representante.prosecucion.index',
        'grupo' => 'portal',
        'permisos' => [
            'portal_prosecucion.registrar',
            'portal_prosecucion.ver_planilla',
            'portal_prosecucion.generar_planilla',
        ],
    ],

    'portal_carnets' => [
        'nombre' => 'Carnets (Portal)',
        'icono' => 'fas fa-id-card',
        'ruta' => 'portal-representante.carnet.index',
        'grupo' => 'portal',
        'permisos' => [
            'portal_carnets.ver_estudiante',
            'portal_carnets.generar_estudiante',
            'portal_carnets.ver_docente',
            'portal_carnets.generar_docente',
        ],
    ],

    'portal_horarios' => [
        'nombre' => 'Horarios (Portal)',
        'icono' => 'fas fa-calendar-alt',
        'ruta' => null,
        'grupo' => 'portal',
        'permisos' => [
            'portal_horarios.ver_estudiante',
            'portal_horarios.generar_estudiante',
            'portal_horarios.ver_docente',
            'portal_horarios.generar_docente',
        ],
    ],

    'portal_constancias' => [
        'nombre' => 'Constancias (Portal)',
        'icono' => 'fas fa-file-alt',
        'ruta' => null,
        'grupo' => 'portal',
        'permisos' => [
            'portal_constancias.ver',
            'portal_constancias.generar',
        ],
    ],

    'portal_carga_academica' => [
        'nombre' => 'Carga Académica (Portal)',
        'icono' => 'fas fa-book',
        'ruta' => null,
        'grupo' => 'portal',
        'permisos' => [
            'portal_carga_academica.ver',
        ],
    ],

    'asistencia' => [
        'nombre' => 'Asistencia',
        'icono' => 'fas fa-clock',
        'ruta' => null, // Funcionalidad de entrada/salida del personal aún no existe
        'grupo' => 'asistencia',
        'permisos' => [
            'asistencia.ver',
            'asistencia.entrada',
            'asistencia.salida',
            'asistencia.escaneos',
        ],
    ],

    'justificaciones' => [
        'nombre' => 'Justificaciones',
        'icono' => 'fas fa-file-medical-alt',
        'ruta' => 'admin.inasistencia.justificacion',
        'grupo' => 'asistencia',
        'permisos' => [
            'justificaciones.ver',
            'justificaciones.crear',
            'justificaciones.editar',
            'justificaciones.eliminar',
        ],
    ],

    'reportes_asistencia' => [
        'nombre' => 'Reportes de Asistencia',
        'icono' => 'fas fa-chart-line',
        'ruta' => null,
        'grupo' => 'asistencia',
        'permisos' => [
            'reportes_asistencia.ver',
            'reportes_asistencia.estadisticas',
            'reportes_asistencia.alertas',
        ],
    ],

    'actas' => [
        'nombre' => 'Actas',
        'icono' => 'fas fa-file-signature',
        'ruta' => 'admin.acta.index',
        'grupo' => 'academico',
        'permisos' => [
            'actas.ver',
            'actas.crear',
            'actas.editar',
            'actas.eliminar',
        ],
    ],

    'usuarios_accesos' => [
        'nombre' => 'Usuarios y Accesos',
        'icono' => 'fas fa-users-cog',
        'ruta' => 'admin.roles.index',
        'grupo' => 'seguridad',
        'permisos' => [
            'usuarios_accesos.ver',
            'usuarios_accesos.roles',
            'usuarios_accesos.permisos',
            'usuarios_accesos.usuarios',
        ],
    ],

    'auditoria' => [
        'nombre' => 'Auditoría',
        'icono' => 'fas fa-history',
        'ruta' => null,
        'grupo' => 'seguridad',
        'permisos' => [
            'auditoria.ver',
        ],
    ],

    // ===== MÓDULOS SIN DUEÑO CLARO (ASIGNADOS A ADMIN) =====
    'calendario' => [
        'nombre' => 'Calendario Escolar',
        'icono' => 'fas fa-calendar',
        'ruta' => 'admin.anio_escolar.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'calendario.ver',
            'calendario.crear',
            'calendario.editar',
            'calendario.eliminar',
            'calendario.extender',
        ],
    ],

    'inscripciones' => [
        'nombre' => 'Inscripciones',
        'icono' => 'fas fa-user-plus',
        'ruta' => 'admin.transacciones.inscripcion.index',
        'grupo' => 'academico',
        'permisos' => [
            'inscripciones.ver',
            'inscripciones.crear',
            'inscripciones.editar',
            'inscripciones.eliminar',
            'inscripciones.restaurar',
            'inscripciones.reporte',
        ],
    ],

    'alumnos' => [
        'nombre' => 'Alumnos',
        'icono' => 'fas fa-user-graduate',
        'ruta' => 'admin.alumnos.index',
        'grupo' => 'academico',
        'permisos' => [
            'alumnos.ver',
            'alumnos.crear',
            'alumnos.editar',
            'alumnos.eliminar',
            'alumnos.reporte',
        ],
    ],

    'grados' => [
        'nombre' => 'Grados',
        'icono' => 'fas fa-layer-group',
        'ruta' => 'admin.grado.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'grados.ver',
            'grados.crear',
            'grados.editar',
            'grados.eliminar',
        ],
    ],

    'secciones' => [
        'nombre' => 'Secciones',
        'icono' => 'fas fa-th-large',
        'ruta' => 'admin.seccion.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'secciones.ver',
            'secciones.crear',
            'secciones.editar',
            'secciones.eliminar',
        ],
    ],

    'bloques_horarios' => [
        'nombre' => 'Bloques Horarios',
        'icono' => 'fas fa-clock',
        'ruta' => 'admin.bloque_horario.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'bloques_horarios.ver',
            'bloques_horarios.crear',
            'bloques_horarios.editar',
            'bloques_horarios.eliminar',
        ],
    ],

    'asignacion_docente' => [
        'nombre' => 'Asignación Docente',
        'icono' => 'fas fa-chalkboard-teacher',
        'ruta' => 'admin.transacciones.docente_area_grado.index',
        'grupo' => 'academico',
        'permisos' => [
            'asignacion_docente.ver',
            'asignacion_docente.crear',
            'asignacion_docente.editar',
            'asignacion_docente.eliminar',
            'asignacion_docente.reporte',
        ],
    ],

    'grado_area_formacion' => [
        'nombre' => 'Grado-Área Formación',
        'icono' => 'fas fa-project-diagram',
        'ruta' => 'admin.transacciones.grado_area_formacion.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'grado_area_formacion.ver',
            'grado_area_formacion.crear',
            'grado_area_formacion.editar',
            'grado_area_formacion.eliminar',
        ],
    ],

    'estudios_realizados' => [
        'nombre' => 'Estudios Realizados',
        'icono' => 'fas fa-university',
        'ruta' => 'admin.estudios_realizados.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'estudios_realizados.ver',
            'estudios_realizados.crear',
            'estudios_realizados.editar',
            'estudios_realizados.eliminar',
        ],
    ],

    'area_estudio_realizado' => [
        'nombre' => 'Área Estudio Realizado',
        'icono' => 'fas fa-book-open',
        'ruta' => 'admin.transacciones.area_estudio_realizado.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'area_estudio_realizado.ver',
            'area_estudio_realizado.crear',
            'area_estudio_realizado.editar',
            'area_estudio_realizado.eliminar',
        ],
    ],

    'historico' => [
        'nombre' => 'Histórico',
        'icono' => 'fas fa-archive',
        'ruta' => 'admin.historico.index',
        'grupo' => 'academico',
        'permisos' => [
            'historico.ver',
        ],
    ],

    'percentil' => [
        'nombre' => 'Percentil',
        'icono' => 'fas fa-chart-bar',
        'ruta' => 'admin.transacciones.percentil.index',
        'grupo' => 'academico',
        'permisos' => [
            'percentil.ver',
            'percentil.ejecutar',
        ],
    ],

    'permisos_reposos' => [
        'nombre' => 'Permisos y Reposos',
        'icono' => 'fas fa-user-clock',
        'ruta' => 'admin.permisos_reposos.index',
        'grupo' => 'academico',
        'permisos' => [
            'permisos_reposos.ver',
            'permisos_reposos.crear',
            'permisos_reposos.editar',
            'permisos_reposos.eliminar',
        ],
    ],

    'inasistencia_docente' => [
        'nombre' => 'Inasistencia Docente',
        'icono' => 'fas fa-clipboard-list',
        'ruta' => 'admin.inasistencia.index',
        'grupo' => 'asistencia',
        'permisos' => [
            'inasistencia_docente.ver',
            'inasistencia_docente.crear',
            'inasistencia_docente.editar',
            'inasistencia_docente.eliminar',
        ],
    ],

    // ===== MÓDULOS DE CATÁLOGOS (AGRUPADOS) =====
    'ubicacion_geografica' => [
        'nombre' => 'Ubicación Geográfica',
        'icono' => 'fas fa-map-marker-alt',
        'ruta' => 'admin.pais.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'ubicacion_geografica.ver',
            'ubicacion_geografica.pais',
            'ubicacion_geografica.estado',
            'ubicacion_geografica.municipio',
            'ubicacion_geografica.localidad',
        ],
    ],

    'catalogos' => [
        'nombre' => 'Catálogos',
        'icono' => 'fas fa-list',
        'ruta' => 'admin.banco.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'catalogos.ver',
            'catalogos.banco',
            'catalogos.etnia',
            'catalogos.ocupacion',
            'catalogos.expresion_literaria',
            'catalogos.discapacidad',
            'catalogos.prefijo_telefono',
            'catalogos.genero',
            'catalogos.lateralidad',
            'catalogos.orden_nacimiento',
            'catalogos.tipo_documento',
            'catalogos.indice_edad',
            'catalogos.indice_peso',
            'catalogos.indice_estatura',
            'catalogos.talla',
        ],
    ],

    'instituciones' => [
        'nombre' => 'Instituciones',
        'icono' => 'fas fa-school',
        'ruta' => 'admin.institucion_procedencia.index',
        'grupo' => 'configuracion',
        'permisos' => [
            'instituciones.ver',
            'instituciones.crear',
            'instituciones.editar',
            'instituciones.eliminar',
        ],
    ],
];
