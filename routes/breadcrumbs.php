<?php

use App\Models\Alumno;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;
use App\Models\Docente;
use App\Models\Inscripcion;
use App\Models\Representante;

// Inicio (Home)
Breadcrumbs::for('home', function (Trail $trail) {
    $trail->push('Inicio', route('home'));
});

// Calendario Escolar
Breadcrumbs::for('admin.anio_escolar.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Calendario Escolar', route('admin.anio_escolar.index'));
});

// Banco
Breadcrumbs::for('admin.banco.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Banco', route('admin.banco.index'));
});

// Pais
Breadcrumbs::for('admin.pais.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Pais', route('admin.pais.index'));
});

// Estado
Breadcrumbs::for('admin.estado.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Estado', route('admin.estado.index'));
});

// Municipio
Breadcrumbs::for('admin.municipio.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Municipio', route('admin.municipio.index'));
});

// Localidad
Breadcrumbs::for('admin.localidad.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Localidad', route('admin.localidad.index'));
});

// Etnia Indigena
Breadcrumbs::for('admin.etnia_indigena.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Etnia Indigena', route('admin.etnia_indigena.index'));
});

// Ocupacion
Breadcrumbs::for('admin.ocupacion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Ocupacion', route('admin.ocupacion.index'));
});

// Grado
Breadcrumbs::for('admin.grado.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Nivel Academico', route('admin.grado.index'));
});

// Area de Formacion
Breadcrumbs::for('admin.area_formacion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Area de Formacion', route('admin.area_formacion.index'));
});

// Expresion Literaria
Breadcrumbs::for('admin.expresion_literaria.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Expresion Literaria', route('admin.expresion_literaria.index'));
});

// Discapacidad
Breadcrumbs::for('admin.discapacidad.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Discapacidad', route('admin.discapacidad.index'));
});

// Grado Area Formacion
Breadcrumbs::for('admin.transacciones.grado_area_formacion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Nivel Academico a Area de Formacion', route('admin.transacciones.grado_area_formacion.index'));
});

// Prefijo de telefono
Breadcrumbs::for('admin.prefijo_telefono.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Prefijo de telefono', route('admin.prefijo_telefono.index'));
});

// Rol
Breadcrumbs::for('admin.rol.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Roles', route('admin.rol.index'));
});

// Estudios Realizados
Breadcrumbs::for('admin.estudios_realizados.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Estudios Realizados', route('admin.estudios_realizados.index'));
});

// Area Estudio Realizado
Breadcrumbs::for('admin.transacciones.area_estudio_realizado.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Area de Formacion a Estudio Realizado', route('admin.transacciones.area_estudio_realizado.index'));
});

// Institucion de procedencia
Breadcrumbs::for('admin.institucion_procedencia.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Institucion de procedencia', route('admin.institucion_procedencia.index'));
});

// Docente
Breadcrumbs::for('admin.docente.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Docentes', route('admin.docente.index'));
});

// Docente crear 
Breadcrumbs::for('admin.docente.create', function (Trail $trail) {
    $trail->parent('admin.docente.index');
    $trail->push('Crear Docente', route('admin.docente.create'));
});

// Docente editar
Breadcrumbs::for('admin.docente.edit', function (Trail $trail, $docenteId) {
    $docente = Docente::with('persona')->findOrFail($docenteId);
    $trail->parent('admin.docente.index');
    $trail->push('Editar Docente',route('admin.docente.edit', $docente->id));
});

// Docente estudios
Breadcrumbs::for('admin.docente.estudios', function (Trail $trail, $docenteId) {
    $docente = Docente::with('persona')->findOrFail($docenteId);
    $trail->parent('admin.docente.edit', $docente->id);
    $trail->push('Estudios Académicos',route('admin.docente.estudios', $docente->id));
});

// Transaccion Docente
Breadcrumbs::for('admin.transacciones.docente_area_grado.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Asignacion de Areas', route('admin.transacciones.docente_area_grado.index'));
});

// Docente crear 
Breadcrumbs::for('admin.transacciones.docente_area_grado.create', function (Trail $trail) {
    $trail->parent('admin.transacciones.docente_area_grado.index');
    $trail->push('Crear Asignacion', route('admin.transacciones.docente_area_grado.create'));
});

// Docente editar 
Breadcrumbs::for('admin.transacciones.docente_area_grado.edit', function (Trail $trail, $docenteId) {
    $docente = Docente::with('persona')->findOrFail($docenteId);
    $trail->parent('admin.transacciones.docente_area_grado.index');
    $trail->push('Editar Asignacion', route('admin.transacciones.docente_area_grado.edit', $docente->id));
});

//Representantes
Breadcrumbs::for('admin.representante.representante', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Representantes', route('admin.representante.index'));
});

// Representantes - listado
Breadcrumbs::for('representante.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Representantes', route('representante.index'));
});

// Representante - crear
Breadcrumbs::for('representante.formulario', function (Trail $trail) {
    $trail->parent('representante.index');
    $trail->push('Nuevo Representante', route('representante.formulario'));
});

// Representante - ver / editar
Breadcrumbs::for('representante.editar', function (Trail $trail, $representanteId) {

    $representante = Representante::with('persona')->findOrFail($representanteId);

    $trail->parent('representante.index');
    $trail->push('Editar Representante', route('representante.editar', $representante->id));
});

// Representante - eliminados
Breadcrumbs::for('representante.eliminados', function (Trail $trail) {
    $trail->parent('representante.index');
    $trail->push('Representantes Eliminados', route('representante.eliminados'));
});

// Alumnos - listado
Breadcrumbs::for('admin.alumnos.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Alumnos', route('admin.alumnos.index'));
});

// Alumnos - editar
Breadcrumbs::for('admin.alumnos.edit', function (Trail $trail, Alumno $alumno) {
    $trail->parent('admin.alumnos.index');

    $trail->push('Editar Alumno', route('admin.alumnos.edit', $alumno));
}); 

// Historico
Breadcrumbs::for('admin.historico.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Historico', route('admin.historico.index'));
});

// Historial del percentil
Breadcrumbs::for('admin.transacciones.percentil.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Historial del Percentil', route('admin.transacciones.percentil.index'));
});



// Listado
Breadcrumbs::for('admin.transacciones.inscripcion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Inscripciones',route('admin.transacciones.inscripcion.index'));
});

// Crear inscripción
Breadcrumbs::for('admin.transacciones.inscripcion.create', function (Trail $trail) {
    $trail->parent('admin.transacciones.inscripcion.index');
    $trail->push('Nueva Inscripción', route('admin.transacciones.inscripcion.create'));
});

// Crear alumno desde inscripción
Breadcrumbs::for('admin.transacciones.inscripcion.create-alumno', function (Trail $trail) {
    $trail->parent('admin.transacciones.inscripcion.create');
    $trail->push('Crear Alumno', route('admin.transacciones.inscripcion.create-alumno'));
});

// Editar inscripción
Breadcrumbs::for('admin.transacciones.inscripcion.edit', function (Trail $trail, $id) {

    $inscripcion = Inscripcion::with('alumno.persona')->findOrFail($id);

    $trail->parent('admin.transacciones.inscripcion.index');

    $trail->push('Editar Inscripcion', route('admin.transacciones.inscripcion.edit', $inscripcion->id));
});


// Listado
Breadcrumbs::for('admin.transacciones.inscripcion_prosecucion.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push(
        'Inscripción Prosecución',
        route('admin.transacciones.inscripcion_prosecucion.index')
    );
});

// Crear
Breadcrumbs::for('admin.transacciones.inscripcion_prosecucion.create', function (Trail $trail) {
    $trail->parent('admin.transacciones.inscripcion_prosecucion.index');
    $trail->push(
        'Nueva Prosecución',
        route('admin.transacciones.inscripcion_prosecucion.create')
    );
});

// Horario
Breadcrumbs::for('admin.horario.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Gestión de Horarios', route('admin.horario.index'));
});

// Horario crear
Breadcrumbs::for('admin.horario.create', function (Trail $trail) {
    $trail->parent('admin.horario.index');
    $trail->push('Nuevo Horario', route('admin.horario.create'));
});

// Inasistencia
Breadcrumbs::for('admin.inasistencia.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Gestión de Inasistencias', route('admin.inasistencia.index'));
});

// Inasistencia justificación
Breadcrumbs::for('admin.inasistencia.justificacion', function (Trail $trail) {
    $trail->parent('admin.inasistencia.index');
    $trail->push('Inasistencias Diarias', route('admin.inasistencia.justificacion'));
});

// Bloque Horario
Breadcrumbs::for('admin.bloque_horario.index', function (Trail $trail) {
    $trail->parent('home');
    $trail->push('Gestión de Bloques Horarios', route('admin.bloque_horario.index'));
});
