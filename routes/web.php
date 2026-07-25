<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AnioEscolarController;
use App\Http\Controllers\RepresentanteController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\EtniaIndigenaController;
use App\Http\Controllers\OcupacionController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\AreaFormacionController;
use App\Http\Controllers\ExpresionLiterariaController;
use App\Http\Controllers\DiscapacidadController;
use App\Http\Controllers\GradoAreaFormacionController;
use App\Http\Controllers\PrefijoTelefonoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EstudiosRealizadoController;
use App\Http\Controllers\AreaEstudioRealizadoController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\NuevoIngresoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\DocenteAreaGradoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\EntradasPercentilController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\InscripcionProsecucionController;
use App\Http\Controllers\InstitucionProcedenciaController;
use App\Http\Controllers\BloqueHorarioController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\InasistenciaController;
use App\Http\Controllers\Permisos_RepososController;
use App\Models\Historico;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/check-email', function (Illuminate\Http\Request $request) {
    $email = $request->get('email');
    $exists = \App\Models\User::where('email', $email)->exists();
    return response()->json(['exists' => $exists]);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rutas para Dashboards por rol
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/subdirectora', [App\Http\Controllers\HomeController::class, 'subdirectora'])->name('subdirectora');
});

//HomePage!!

Route::get("/", function () {
    return view('HomePage.Home');
})->name("home.principal");

Route::get("/vidaEstudiantil", function () {
    return view('HomePage.vidaEstudiantil');
})->name("home.vidaEstudiantil");



// ============================================
// RUTAS PROTEGIDAS POR AUTENTICACIÓN
// ============================================

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get(
        'historico/secciones-por-grado/{grado}',
        [HistoricoController::class, 'seccionesPorGrado']
    )->name('admin.historico.secciones-por-grado');
    // Rutas para Institución de Procedencia
    Route::get('institucion-procedencia', [InstitucionProcedenciaController::class, 'index'])->name('institucion-procedencia.index');
    Route::post('institucion-procedencia', [InstitucionProcedenciaController::class, 'store'])->name('institucion-procedencia.store');
    Route::get('institucion-procedencia/verificar', [InstitucionProcedenciaController::class, 'verificarExistencia'])->name('institucion-procedencia.verificar');
    Route::put('institucion-procedencia/{institucionProcedencia}', [InstitucionProcedenciaController::class, 'update'])->name('institucion-procedencia.update');
    Route::delete('institucion-procedencia/{institucionProcedencia}', [InstitucionProcedenciaController::class, 'destroy'])->name('institucion-procedencia.destroy');


    // ===== Calendario Escolar (SIEMPRE ACCESIBLE - SIN VERIFICACIÓN) =====
    Route::get('anio_escolar', [AnioEscolarController::class, 'index'])->name('anio_escolar.index');
    Route::post('anio_escolar/modales/store', [AnioEscolarController::class, 'store'])->name('anio_escolar.modales.store');
    Route::post('anio_escolar/{id}/extender', [AnioEscolarController::class, 'extender'])->name('anio_escolar.modales.extender');
    Route::delete('anio_escolar/{id}', [AnioEscolarController::class, 'destroy'])->name('anio_escolar.destroy');

    // ===== BANCOS =====
    Route::get('banco', [BancoController::class, 'index'])->name('banco.index');
    Route::get('banco/verificar', [BancoController::class, 'verificarExistencia'])->name('banco.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('banco/modales/store', [BancoController::class, 'store'])->name('banco.modales.store');
        Route::post('banco/{id}/update', [BancoController::class, 'update'])->name('banco.modales.update');
        Route::delete('banco/{id}', [BancoController::class, 'destroy'])->name('banco.destroy');
    });

    // ===== ETNIA INDÍGENA =====
    Route::get('etnia_indigena', [EtniaIndigenaController::class, 'index'])->name('etnia_indigena.index');
    Route::get('etnia_indigena/verificar', [EtniaIndigenaController::class, 'verificarExistencia'])->name('etnia_indigena.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('etnia_indigena/modales/store', [EtniaIndigenaController::class, 'store'])->name('etnia_indigena.modales.store');
        Route::post('etnia_indigena/{id}/update', [EtniaIndigenaController::class, 'update'])->name('etnia_indigena.modales.update');
        Route::delete('etnia_indigena/{id}', [EtniaIndigenaController::class, 'destroy'])->name('etnia_indigena.destroy');
    });

    // ===== OCUPACIÓN =====
    Route::get('ocupacion', [OcupacionController::class, 'index'])->name('ocupacion.index');
    Route::get('ocupacion/verificar', [OcupacionController::class, 'verificarExistencia'])->name('ocupacion.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('ocupacion/modales/store', [OcupacionController::class, 'store'])->name('ocupacion.modales.store');
        Route::post('ocupacion/{id}/update', [OcupacionController::class, 'update'])->name('ocupacion.modales.update');
        Route::delete('ocupacion/{id}', [OcupacionController::class, 'destroy'])->name('ocupacion.destroy');
    });

    // ===== PAIS (LIVEWIRE) =====
    Route::get('pais', function () {
        return view('admin.pais.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->name('pais.index');

    // Ruta para verificar la existencia de un estado
    Route::get('pais/verificar', [PaisController::class, 'verificarExistencia'])->name('pais.verificar');
    // ===== ESTADO (LIVEWIRE) =====
    Route::get('estado', function () {
        return view('admin.estado.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->name('estado.index');

    // Ruta para verificar la existencia de un estado
    Route::get('estado/verificar', [EstadoController::class, 'verificarExistencia'])->name('estado.verificar');

    // ===== MUNICIPIO (LIVEWIRE) =====
    Route::get('municipio', function () {
        return view('admin.municipio.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->name('municipio.index');

    // Ruta para verificar la existencia de un municipio
    Route::get('municipio/verificar', [MunicipioController::class, 'verificarExistencia'])->name('municipio.verificar');

    // ===== LOCALIDAD (LIVEWIRE) =====
    Route::get('localidad', function () {
        return view('admin.localidad.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->name('localidad.index');

    // Ruta para verificar la existencia de una localidad
    Route::get('localidad/verificar', [LocalidadController::class, 'verificarExistencia'])->name('localidad.verificar');

    // ===== INSTITUCIÓN DE PROCEDENCIA (LIVEWIRE)=====
    Route::get('institucion_procedencia', function () {
        return view('admin.institucion_procedencia.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->name('institucion_procedencia.index');


    // ===== GRADO =====
    Route::get('grado', [GradoController::class, 'index'])->name('grado.index');
    Route::get('grado/verificar', [GradoController::class, 'verificarExistencia'])->name('grado.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('grado/modales/store', [GradoController::class, 'store'])->name('grado.modales.store');
        Route::post('grado/{id}/update', [GradoController::class, 'update'])->name('grado.modales.update');
        Route::delete('grado/{id}', [GradoController::class, 'destroy'])->name('grado.destroy');
    });

    // ===== BLOQUE HORARIO =====
    Route::get('bloque_horario', [BloqueHorarioController::class, 'index'])->name('bloque_horario.index');
    Route::get('bloque_horario/verificar', [BloqueHorarioController::class, 'verificarExistencia'])->name('bloque_horario.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('bloque_horario/modales/store', [BloqueHorarioController::class, 'store'])->name('bloque_horario.modales.store');
        Route::match(['post', 'put'], 'bloque_horario/{id}/update', [BloqueHorarioController::class, 'update'])->name('bloque_horario.modales.update');
        Route::delete('bloque_horario/{id}', [BloqueHorarioController::class, 'destroy'])->name('bloque_horario.destroy');
    });

    // ===== ÁREA DE FORMACIÓN =====
    Route::get('area_formacion', [AreaFormacionController::class, 'index'])->name('area_formacion.index');
    Route::get('area_formacion/verificar', [AreaFormacionController::class, 'verificarExistencia'])->name('area_formacion.verificar');
    Route::get('area_formacion/verificar-grupo-estable', [AreaFormacionController::class, 'verificarExistenciaGrupoEstable'])->name('area_formacion.verificar_grupo_estable');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('area_formacion/modales/store', [AreaFormacionController::class, 'store'])->name('area_formacion.modales.store');
        Route::post('area_formacion/{id}/update', [AreaFormacionController::class, 'update'])->name('area_formacion.modales.update');
        Route::delete('area_formacion/{id}', [AreaFormacionController::class, 'destroy'])->name('area_formacion.destroy');
    });

    // ===== GRUPO ESTABLE =====
    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('area_formacion/modalesGrupoEstable/storeGrupoEstable', [AreaFormacionController::class, 'storeGrupoEstable'])->name('area_formacion.modalesGrupoEstable.storeGrupoEstable');
        Route::post('area_formacion/{id}/updateGrupoEstable', [AreaFormacionController::class, 'updateGrupoEstable'])->name('area_formacion.modalesGrupoEstable.updateGrupoEstable');
        Route::delete('grupo_estable/{id}', [AreaFormacionController::class, 'destroyGrupoEstable'])->name('area_formacion.modalesGrupoEstable.destroyGrupoEstable');
    });

    // ===== EXPRESIÓN LITERARIA =====
    Route::get('expresion_literaria', [ExpresionLiterariaController::class, 'index'])->name('expresion_literaria.index');
    Route::get('expresion_literaria/verificar', [ExpresionLiterariaController::class, 'verificarExistencia'])->name('expresion_literaria.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('expresion_literaria/modales/store', [ExpresionLiterariaController::class, 'store'])->name('expresion_literaria.modales.store');
        Route::post('expresion_literaria/{id}/update', [ExpresionLiterariaController::class, 'update'])->name('expresion_literaria.modales.update');
        Route::delete('expresion_literaria/{id}', [ExpresionLiterariaController::class, 'destroy'])->name('expresion_literaria.destroy');
    });

    // ===== DISCAPACIDAD =====
    Route::get('discapacidad', [DiscapacidadController::class, 'index'])->name('discapacidad.index');
    Route::get('discapacidad/verificar', [DiscapacidadController::class, 'verificarExistencia'])->name('discapacidad.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('discapacidad/modales/store', [DiscapacidadController::class, 'store'])->name('discapacidad.modales.store');
        Route::post('discapacidad/{id}/update', [DiscapacidadController::class, 'update'])->name('discapacidad.modales.update');
        Route::delete('discapacidad/{id}', [DiscapacidadController::class, 'destroy'])->name('discapacidad.destroy');
    });

    // ===== GRADO ÁREA FORMACIÓN (TRANSACCIÓN) =====
    Route::get('transacciones/grado_area_formacion', [GradoAreaFormacionController::class, 'index'])->name('transacciones.grado_area_formacion.index');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('transacciones/grado_area_formacion/modales/store', [GradoAreaFormacionController::class, 'store'])->name('transacciones.grado_area_formacion.modales.store');
        Route::post('transacciones/grado_area_formacion/{id}/update', [GradoAreaFormacionController::class, 'update'])->name('transacciones.grado_area_formacion.modales.update');
        Route::delete('transacciones/grado_area_formacion/{id}', [GradoAreaFormacionController::class, 'destroy'])->name('transacciones.grado_area_formacion.destroy');
    });

    // ===== PREFIJO DE TELÉFONO =====
    Route::get('prefijo_telefono', [PrefijoTelefonoController::class, 'index'])->name('prefijo_telefono.index');
    Route::get('prefijo_telefono/verificar', [PrefijoTelefonoController::class, 'verificarExistencia'])->name('prefijo_telefono.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('prefijo_telefono/modales/store', [PrefijoTelefonoController::class, 'store'])->name('prefijo_telefono.modales.store');
        Route::post('prefijo_telefono/{id}/update', [PrefijoTelefonoController::class, 'update'])->name('prefijo_telefono.modales.update');
        Route::delete('prefijo_telefono/{id}', [PrefijoTelefonoController::class, 'destroy'])->name('prefijo_telefono.destroy');
    });

    // ===== ROLES =====
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/permisos/{id}', [RoleController::class, 'permisos'])->name('roles.permisos');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('roles/modales/store', [RoleController::class, 'store'])->name('roles.modales.store');
        Route::post('roles/{id}/update', [RoleController::class, 'update'])->name('roles.modales.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // ===== ESTUDIOS REALIZADOS =====
    Route::get('estudios_realizados', [EstudiosRealizadoController::class, 'index'])->name('estudios_realizados.index');
    Route::get('estudios_realizados/verificar', [EstudiosRealizadoController::class, 'verificarExistencia'])->name('estudios_realizados.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('estudios_realizados/modales/store', [EstudiosRealizadoController::class, 'store'])->name('estudios_realizados.modales.store');
        Route::post('estudios_realizados/{id}/update', [EstudiosRealizadoController::class, 'update'])->name('estudios_realizados.modales.update');
        Route::delete('estudios_realizados/{id}', [EstudiosRealizadoController::class, 'destroy'])->name('estudios_realizados.destroy');
    });

    // ===== ÁREA ESTUDIO REALIZADO (TRANSACCIÓN) =====
    Route::get('transacciones/area_estudio_realizado', [AreaEstudioRealizadoController::class, 'index'])->name('transacciones.area_estudio_realizado.index');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('transacciones/area_estudio_realizado/modales/store', [AreaEstudioRealizadoController::class, 'store'])->name('transacciones.area_estudio_realizado.modales.store');
        Route::post('transacciones/area_estudio_realizado/{id}/update', [AreaEstudioRealizadoController::class, 'update'])->name('transacciones.area_estudio_realizado.modales.update');
        Route::delete('transacciones/area_estudio_realizado/{id}', [AreaEstudioRealizadoController::class, 'destroy'])->name('transacciones.area_estudio_realizado.destroy');
    });

    // ===== DOCENTE ======
    Route::get('docente', [DocenteController::class, 'index'])->name('docente.index');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::get('docente/create', [DocenteController::class, 'create'])->name('docente.create');
        Route::post('docente/store', [DocenteController::class, 'store'])->name('docente.store');
        Route::get('docente/{id}/edit', [DocenteController::class, 'edit'])->name('docente.edit');
        Route::post('docente/{id}/update', [DocenteController::class, 'update'])->name('docente.update');
        Route::delete('docente/{id}', [DocenteController::class, 'destroy'])->name('docente.destroy');
        Route::get('docente/{id}/estudios', [DocenteController::class, 'estudios'])->name('docente.estudios');
        Route::post('docente/{id}/estudiosEdit', [DocenteController::class, 'estudiosEdit'])->name('docente.estudiosEdit');
    });

    // ===== TRANSACCION DOCENTE ======
    Route::get('transacciones/docente_area_grado', [DocenteAreaGradoController::class, 'index'])->name('transacciones.docente_area_grado.index');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::get('transacciones/docente_area_grado/create', [DocenteAreaGradoController::class, 'create'])->name('transacciones.docente_area_grado.create');
        Route::post('transacciones/docente_area_grado/store', [DocenteAreaGradoController::class, 'store'])->name('transacciones.docente_area_grado.store');
        Route::get('transacciones/docente_area_grado/{id}/edit', [DocenteAreaGradoController::class, 'edit'])->name('transacciones.docente_area_grado.edit');
        Route::post('transacciones/docente_area_grado/{id}/update', [DocenteAreaGradoController::class, 'update'])->name('transacciones.docente_area_grado.update');
        Route::delete('transacciones/docente_area_grado/{id}', [DocenteAreaGradoController::class, 'destroy'])->name('transacciones.docente_area_grado.destroy');
        Route::delete('transacciones/docente_area_grado/{id}/destroy-asignacion', [DocenteAreaGradoController::class, 'destroyAsignacion'])->name('transacciones.docente_area_grado.destroyAsignacion');
    });

    //===== RUTA REPORTES DOCENTE_AREA_GRADO ======
    Route::get('transacciones/docente_area_grado/reporte-general', [DocenteAreaGradoController::class, 'reportePDFGeneral'])->name('transacciones.docente_area_grado.reportePDFGeneral');

    //===== RUTA REPORTES DOCENTE ======
    Route::get('docente/reporte/{id}', [DocenteController::class, 'reportePDF'])->name('docente.reportePDF');
    Route::get('docente/reporte-general', [DocenteController::class, 'reporteGeneralPDF'])->name('docente.reporteGeneralPDF');
    Route::get('docente/reporte-materia', [DocenteController::class, 'DocenteMateria'])->name('docente.DocenteMateria');
    Route::get('docente/reporte-materia/{id}', [DocenteController::class, 'DocenteMateria'])->name('docente.DocenteMateriaIndividual');

    // Verificar cédula duplicada (AJAX)
    Route::get('docente/verificar-cedula', [DocenteController::class, 'verificarCedula'])->name('docente.verificar_cedula');

    // ===== ALUMNOS ======
    Route::get('alumnos',  [AlumnoController::class, 'index'])->name('alumnos.index');
    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::get('alumnos/create', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos/store', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::get('alumnos/{alumno}/edit', [AlumnoController::class, 'edit'])
            ->name('alumnos.edit');

        Route::post('alumnos/{alumno}/update', [AlumnoController::class, 'update'])
            ->name('alumnos.update');

        Route::delete('alumnos/{id}', [AlumnoController::class, 'destroy'])->name('alumnos.destroy');
    });

    //Reportes
    //Route::get('alumnos/reportes', [AlumnoController::class, 'reportePDF'])->name('alumnos.reportePDF');
    Route::get('alumnos/reporte/{id}', [AlumnoController::class, 'reportePDF'])->name('alumnos.reporte.individual');
    Route::get('alumnos/reportes/general', [AlumnoController::class, 'reporteGeneralPDF'])->name('alumnos.reporteGeneralPDF');

    // ================== TRANSACCIONES ==================
    Route::prefix('transacciones')->name('transacciones.')->group(function () {

        // ========= INSCRIPCIONES (NUEVO INGRESO) =========
        Route::get('inscripcion', [InscripcionController::class, 'index'])
            ->name('inscripcion.index');

        Route::get('inscripcion/create', function () {
            return view('admin.transacciones.inscripcion.create');
        })->name('inscripcion.create');

        Route::get('inscripcion/create-alumno', function () {
            return view('admin.transacciones.inscripcion.create-alumno');
        })->name('inscripcion.create-alumno');

        Route::get('/inscripcion/{id}/edit', [InscripcionController::class, 'edit'])
            ->name('inscripcion.edit');

        Route::delete('inscripcion/{id}', [InscripcionController::class, 'destroy'])
            ->name('inscripcion.destroy');

        Route::get('inscripcion/{id}/restore', [InscripcionController::class, 'restore'])
            ->name('inscripcion.restore');

        Route::get('inscripcion/reporte/{id}', [InscripcionController::class, 'reporte'])
            ->name('inscripcion.reporte');

        Route::get('inscripcion/reportes/nuevo-ingreso', [InscripcionController::class, 'reporteGeneralNuevoIngresoPDF'])
            ->name('inscripcion.reporteGeneralNuevoIngresoPDF');

        // ========= INSCRIPCIÓN PROSECUCIÓN (LISTADO) =========
        Route::get('inscripcion_prosecucion', [InscripcionProsecucionController::class, 'index'])
            ->name('inscripcion_prosecucion.index');

        // ========= INSCRIPCIÓN PROSECUCIÓN (FORMULARIO) =========
        Route::get('inscripcion_prosecucion/create', function () {
            return view('admin.transacciones.inscripcion_prosecucion.create');
        })->name('inscripcion_prosecucion.create');

        Route::get('inscripcion_prosecucion/create-alumno', function () {
            return view('admin.transacciones.inscripcion_prosecucion.create-alumno');
        })->name('inscripcion_prosecucion.create-alumno');

        Route::delete('inscripcion-prosecucion/{inscripcion}', [InscripcionProsecucionController::class, 'destroy'])
            ->name('inscripcion_prosecucion.destroy');

        Route::get('inscripcion-prosecucion/{id}/restore', [InscripcionProsecucionController::class, 'restore'])
            ->name('inscripcion_prosecucion.restore');

        // ========= REPORTES PROSECUCIÓN =========
        Route::get('inscripcion-prosecucion/{id}/reporte', [InscripcionProsecucionController::class, 'reporte'])
            ->name('inscripcion_prosecucion.reporte');

        Route::get('inscripcion_prosecucion/reportes/general', [InscripcionProsecucionController::class, 'reporteGeneralProsecucionPDF'])
            ->name('inscripcion_prosecucion.reporteGeneralProsecucionPDF');

        // ========= AJAX =========
        Route::get(
            'secciones-por-grado/{grado}',
            [InscripcionController::class, 'seccionesPorGrado']
        )->name('secciones.por-grado');

        // ========= PERCENTIL =========
        Route::get('percentil', [EntradasPercentilController::class, 'index'])
            ->name('percentil.index');

        Route::post(
            'percentil/boton-percentil',
            [EntradasPercentilController::class, 'ejecutar']
        )->name('percentil.boton-percentil');
    });


    // ================== HISTÓRICO ==================
    Route::get('historico', [HistoricoController::class, 'index'])
        ->name('historico.index');

    // ================== HORARIO ==================
    Route::get('horario', [HorarioController::class, 'index'])
        ->name('horario.index');
    Route::get('horario/create', [HorarioController::class, 'create'])
        ->name('horario.create');

    // ================== INASISTENCIA ==================
    Route::get('inasistencia', [InasistenciaController::class, 'index'])
        ->name('inasistencia.index');
    Route::get('inasistencia/inasistencia_justificacion', [InasistenciaController::class, 'inasistencia_justificacion'])
        ->name('inasistencia.justificacion');

        // ================== REPOSOS Y PERMISOS ==================
    Route::get('permisos_reposos', [Permisos_RepososController::class, 'index'])
        ->name('permisos_reposos.index');
    Route::get('permisos_reposos/verificar', [Permisos_RepososController::class, 'verificarExistencia'])
        ->name('permisos_reposos.verificar');

    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::post('permisos_reposos/modales/store', [Permisos_RepososController::class, 'store'])
            ->name('permisos_reposos.modales.store');
        Route::post('permisos_reposos/{id}/update', [Permisos_RepososController::class, 'update'])
            ->name('permisos_reposos.modales.update');
        Route::delete('permisos_reposos/{id}', [Permisos_RepososController::class, 'destroy'])
            ->name('permisos_reposos.destroy');
    });
});



// ======  REPRESENTANTE  ======
Route::middleware(['auth'])->prefix('representante')->name('representante.')->group(function () {
    // Vista principal (listado)
    Route::get('/', [RepresentanteController::class, 'index'])->name('index');

    // Formulario de creación
    Route::get('/formulario', [RepresentanteController::class, 'mostrarFormulario'])->name('formulario');

    // Guardar nuevo representante
    Route::post('/save', [RepresentanteController::class, 'save'])->name('save');

    // Actualizar representante existente
    Route::put('/{id}', [RepresentanteController::class, 'update'])->name('update');

    // Formulario de edición de un representante específico
    Route::get('/{id}/editar', [RepresentanteController::class, 'mostrarFormularioEditar'])->name('editar');

    // Eliminar representante
    Route::delete('/{id}', [RepresentanteController::class, 'delete'])->name('destroy');

    // Rutas para manejo de eliminados
    Route::get('/eliminados', [RepresentanteController::class, 'eliminados'])->name('eliminados');
    Route::post('/{id}/restaurar', [RepresentanteController::class, 'restaurar'])->name('restaurar');

    // Búsqueda por cédula (AJAX)
    Route::get('/buscar-numero_documento', [RepresentanteController::class, 'buscarPornumero_documento'])->name('buscar_numero_documento');

    // Consultar un representante específico (AJAX)
    Route::get('/consultar', [RepresentanteController::class, 'consultar'])->name('consultar');

    // Filtrar representantes (AJAX)
    Route::get('/filtrar', [RepresentanteController::class, 'filtar'])->name('filtrar');

    // Verificar cédula duplicada (AJAX)
    Route::get('/verificar-numero_documento', [RepresentanteController::class, 'verificarnumero_documento'])->name('verificar_numero_documento');

    // Generar reporte PDF
    Route::get('/reporte-pdf', [RepresentanteController::class, 'reportePDF'])->name('reporte_pdf');
});
