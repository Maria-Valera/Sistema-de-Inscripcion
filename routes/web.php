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
use App\Http\Controllers\AulaController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\EntradasPercentilController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\InscripcionProsecucionController;
use App\Http\Controllers\InstitucionProcedenciaController;
use App\Http\Controllers\PermisoReposoController;
use App\Http\Controllers\BloqueHorarioController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\InasistenciaController;
use App\Http\Controllers\GeneradorHorarioController;
use App\Http\Controllers\Permisos_RepososController;
use App\Http\Controllers\ActaEntrevistaController;
use App\Http\Controllers\Seccion_AulaController;
use App\Http\Controllers\DocenteNoDisponibilidadController;
use App\Models\Historico;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify' => true]);

// Rutas para cambio de contraseña (requiere auth y verified, sin forzar.cambio.password para evitar bucles)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'change'])->name('password.change.post');
});

// Rutas para Dashboards por rol
Route::middleware(['auth', 'verified', 'forzar.cambio.password'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/subdirectora', [App\Http\Controllers\HomeController::class, 'subdirectora'])->middleware('role:Subdirectora')->name('subdirectora');
    Route::get('/docente', [App\Http\Controllers\HomeController::class, 'docente'])->middleware('role:Docente')->name('docente');
});

// Página neutra para usuarios autenticados sin módulos ni dashboard asignado
Route::middleware(['auth', 'verified'])->get('/sin-acceso', function () {
    return view('auth.sin-acceso');
})->name('sin-acceso');

// Home (Dashboard administrativo general: HomeController redirige a su dashboard respectivo si no es Admin)
Route::middleware(['auth', 'verified', 'forzar.cambio.password', 'role:Admin'])->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');




//HomePage!!

Route::get("/", function () {
    return view('HomePage.Home');
})->name("home.principal");

Route::get("/vidaEstudiantil", function () {
    return view('HomePage.vidaEstudiantil');
})->name("home.vidaEstudiantil");



// ============================================
// RUTAS API EN WEB CON SESIÓN Y THROTTLE
// ============================================

Route::middleware(['web', 'throttle:60,1', 'auth', 'verified', 'forzar.cambio.password'])->group(function () {
    // ===== DOCENTE NO DISPONIBILIDAD API =====
    Route::middleware(['verificar.anio.escolar'])->group(function () {
        Route::get('api/docente-no-disponibilidad', [DocenteNoDisponibilidadController::class, 'index'])
            ->middleware('permission:horarios.ver')
            ->name('api.docente-no-disponibilidad.index');
        Route::post('api/docente-no-disponibilidad', [DocenteNoDisponibilidadController::class, 'store'])
            ->middleware('permission:horarios.gestionar')
            ->name('api.docente-no-disponibilidad.store');
        Route::delete('api/docente-no-disponibilidad/{id}', [DocenteNoDisponibilidadController::class, 'destroy'])
            ->middleware('permission:horarios.gestionar')
            ->name('api.docente-no-disponibilidad.destroy');
    });
});

Route::middleware(['auth', 'verified', 'forzar.cambio.password'])->prefix('admin')->name('admin.')->group(function () {


    Route::get(
        'historico/secciones-por-grado/{grado}',
        [HistoricoController::class, 'seccionesPorGrado']
    )->middleware('permission:historico.ver')->name('admin.historico.secciones-por-grado');

    // Rutas para Institución de Procedencia
    Route::get('institucion-procedencia', [InstitucionProcedenciaController::class, 'index'])->middleware('permission:instituciones.ver')->name('institucion-procedencia.index');
    Route::get('institucion-procedencia/verificar', [InstitucionProcedenciaController::class, 'verificarExistencia'])->middleware('permission:instituciones.ver')->name('institucion-procedencia.verificar');
    Route::middleware(['permission:instituciones.gestionar'])->group(function () {
        Route::post('institucion-procedencia', [InstitucionProcedenciaController::class, 'store'])->name('institucion-procedencia.store');
        Route::put('institucion-procedencia/{institucionProcedencia}', [InstitucionProcedenciaController::class, 'update'])->name('institucion-procedencia.update');
        Route::delete('institucion-procedencia/{institucionProcedencia}', [InstitucionProcedenciaController::class, 'destroy'])->name('institucion-procedencia.destroy');
    });

    // ===== ACTA ENTREVISTA =====
    Route::get('acta_entrevista', [ActaEntrevistaController::class, 'index'])->middleware('permission:actas.ver')->name('acta.index');
    Route::get('acta_entrevista/{actaEntrevista}', [ActaEntrevistaController::class, 'show'])->middleware('permission:actas.ver')->name('acta.show');
    Route::middleware(['permission:actas.generar'])->group(function () {
        Route::get('acta_entrevista/crear', [ActaEntrevistaController::class, 'create'])->name('acta.create');
        Route::post('acta_entrevista', [ActaEntrevistaController::class, 'store'])->name('acta.store');
        Route::get('acta_entrevista/{actaEntrevista}/editar', [ActaEntrevistaController::class, 'edit'])->name('acta.edit');
        Route::put('acta_entrevista/{actaEntrevista}', [ActaEntrevistaController::class, 'update'])->name('acta.update');
        Route::delete('acta_entrevista/{actaEntrevista}', [ActaEntrevistaController::class, 'destroy'])->name('acta.destroy');
    });

    // ===== Calendario Escolar (SIEMPRE ACCESIBLE - SIN VERIFICACIÓN DE AÑO) =====
    Route::get('anio_escolar', [AnioEscolarController::class, 'index'])->middleware('permission:calendario.ver')->name('anio_escolar.index');
    Route::middleware(['permission:calendario.gestionar'])->group(function () {
        Route::post('anio_escolar/modales/store', [AnioEscolarController::class, 'store'])->name('anio_escolar.modales.store');
        Route::post('anio_escolar/{id}/extender', [AnioEscolarController::class, 'extender'])->name('anio_escolar.modales.extender');
        Route::delete('anio_escolar/{id}', [AnioEscolarController::class, 'destroy'])->name('anio_escolar.destroy');
    });

    // ===== BANCOS =====
    Route::get('banco', [BancoController::class, 'index'])->middleware('permission:catalogos.ver')->name('banco.index');
    Route::get('banco/verificar', [BancoController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('banco.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('banco/modales/store', [BancoController::class, 'store'])->name('banco.modales.store');
        Route::post('banco/{id}/update', [BancoController::class, 'update'])->name('banco.modales.update');
        Route::delete('banco/{id}', [BancoController::class, 'destroy'])->name('banco.destroy');
    });

    // ===== ETNIA INDÍGENA =====
    Route::get('etnia_indigena', [EtniaIndigenaController::class, 'index'])->middleware('permission:catalogos.ver')->name('etnia_indigena.index');
    Route::get('etnia_indigena/verificar', [EtniaIndigenaController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('etnia_indigena.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('etnia_indigena/modales/store', [EtniaIndigenaController::class, 'store'])->name('etnia_indigena.modales.store');
        Route::post('etnia_indigena/{id}/update', [EtniaIndigenaController::class, 'update'])->name('etnia_indigena.modales.update');
        Route::delete('etnia_indigena/{id}', [EtniaIndigenaController::class, 'destroy'])->name('etnia_indigena.destroy');
    });

    // ===== OCUPACIÓN =====
    Route::get('ocupacion', [OcupacionController::class, 'index'])->middleware('permission:catalogos.ver')->name('ocupacion.index');
    Route::get('ocupacion/verificar', [OcupacionController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('ocupacion.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('ocupacion/modales/store', [OcupacionController::class, 'store'])->name('ocupacion.modales.store');
        Route::post('ocupacion/{id}/update', [OcupacionController::class, 'update'])->name('ocupacion.modales.update');
        Route::delete('ocupacion/{id}', [OcupacionController::class, 'destroy'])->name('ocupacion.destroy');
    });


    // ===== UBICACIÓN GEOGRÁFICA (LIVEWIRE Y VERIFICACIONES) =====
    Route::middleware(['permission:ubicacion_geografica.ver'])->group(function () {
        Route::get('pais', function () {
            return view('admin.pais.index', [
                'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                    ->where('cierre_anio_escolar', '>=', now())
                    ->exists()
            ]);
        })->name('pais.index');
        Route::get('pais/verificar', [PaisController::class, 'verificarExistencia'])->name('pais.verificar');

        Route::get('estado', function () {
            return view('admin.estado.index', [
                'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                    ->where('cierre_anio_escolar', '>=', now())
                    ->exists()
            ]);
        })->name('estado.index');
        Route::get('estado/verificar', [EstadoController::class, 'verificarExistencia'])->name('estado.verificar');

        Route::get('municipio', function () {
            return view('admin.municipio.index', [
                'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                    ->where('cierre_anio_escolar', '>=', now())
                    ->exists()
            ]);
        })->name('municipio.index');
        Route::get('municipio/verificar', [MunicipioController::class, 'verificarExistencia'])->name('municipio.verificar');

        Route::get('localidad', function () {
            return view('admin.localidad.index', [
                'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                    ->where('cierre_anio_escolar', '>=', now())
                    ->exists()
            ]);
        })->name('localidad.index');
        Route::get('localidad/verificar', [LocalidadController::class, 'verificarExistencia'])->name('localidad.verificar');
    });

    // ===== INSTITUCIÓN DE PROCEDENCIA (LIVEWIRE)=====
    Route::get('institucion_procedencia', function () {
        return view('admin.institucion_procedencia.index', [
            'anioEscolarActivo' => \App\Models\AnioEscolar::activos()
                ->where('cierre_anio_escolar', '>=', now())
                ->exists()
        ]);
    })->middleware('permission:instituciones.ver')->name('institucion_procedencia.index');

    // ===== GRADO =====
    Route::get('grado', [GradoController::class, 'index'])->middleware('permission:grados.ver')->name('grado.index');
    Route::get('grado/verificar', [GradoController::class, 'verificarExistencia'])->middleware('permission:grados.ver')->name('grado.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:grados.gestionar'])->group(function () {
        Route::post('grado/modales/store', [GradoController::class, 'store'])->name('grado.modales.store');
        Route::post('grado/{id}/update', [GradoController::class, 'update'])->name('grado.modales.update');
        Route::delete('grado/{id}', [GradoController::class, 'destroy'])->name('grado.destroy');
    });

    // ===== BLOQUE HORARIO =====
    Route::get('bloque_horario', [BloqueHorarioController::class, 'index'])->middleware('permission:bloques_horarios.ver')->name('bloque_horario.index');
    Route::get('bloque_horario/verificar', [BloqueHorarioController::class, 'verificarExistencia'])->middleware('permission:bloques_horarios.ver')->name('bloque_horario.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:bloques_horarios.gestionar'])->group(function () {
        Route::post('bloque_horario/modales/store', [BloqueHorarioController::class, 'store'])->name('bloque_horario.modales.store');
        Route::match(['post', 'put'], 'bloque_horario/{id}/update', [BloqueHorarioController::class, 'update'])->name('bloque_horario.modales.update');
        Route::delete('bloque_horario/{id}', [BloqueHorarioController::class, 'destroy'])->name('bloque_horario.destroy');
    });

    // ===== ÁREA DE FORMACIÓN =====
    Route::get('area_formacion', [AreaFormacionController::class, 'index'])->middleware('permission:areas_formacion.ver')->name('area_formacion.index');
    Route::get('area_formacion/verificar', [AreaFormacionController::class, 'verificarExistencia'])->middleware('permission:areas_formacion.ver')->name('area_formacion.verificar');
    Route::get('area_formacion/verificar-grupo-estable', [AreaFormacionController::class, 'verificarExistenciaGrupoEstable'])->middleware('permission:areas_formacion.ver')->name('area_formacion.verificar_grupo_estable');

    Route::middleware(['verificar.anio.escolar', 'permission:areas_formacion.gestionar'])->group(function () {
        Route::post('area_formacion/modales/store', [AreaFormacionController::class, 'store'])->name('area_formacion.modales.store');
        Route::post('area_formacion/{id}/update', [AreaFormacionController::class, 'update'])->name('area_formacion.modales.update');
        Route::delete('area_formacion/{id}', [AreaFormacionController::class, 'destroy'])->name('area_formacion.destroy');
    });

    // ===== GRUPO ESTABLE =====
    Route::middleware(['verificar.anio.escolar', 'permission:grupos_estables.gestionar'])->group(function () {
        Route::post('area_formacion/modalesGrupoEstable/storeGrupoEstable', [AreaFormacionController::class, 'storeGrupoEstable'])->name('area_formacion.modalesGrupoEstable.storeGrupoEstable');
        Route::post('area_formacion/{id}/updateGrupoEstable', [AreaFormacionController::class, 'updateGrupoEstable'])->name('area_formacion.modalesGrupoEstable.updateGrupoEstable');
        Route::delete('grupo_estable/{id}', [AreaFormacionController::class, 'destroyGrupoEstable'])->name('area_formacion.modalesGrupoEstable.destroyGrupoEstable');
    });


    // ===== EXPRESIÓN LITERARIA =====
    Route::get('expresion_literaria', [ExpresionLiterariaController::class, 'index'])->middleware('permission:catalogos.ver')->name('expresion_literaria.index');
    Route::get('expresion_literaria/verificar', [ExpresionLiterariaController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('expresion_literaria.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('expresion_literaria/modales/store', [ExpresionLiterariaController::class, 'store'])->name('expresion_literaria.modales.store');
        Route::post('expresion_literaria/{id}/update', [ExpresionLiterariaController::class, 'update'])->name('expresion_literaria.modales.update');
        Route::delete('expresion_literaria/{id}', [ExpresionLiterariaController::class, 'destroy'])->name('expresion_literaria.destroy');
    });

    // ===== DISCAPACIDAD =====
    Route::get('discapacidad', [DiscapacidadController::class, 'index'])->middleware('permission:catalogos.ver')->name('discapacidad.index');
    Route::get('discapacidad/verificar', [DiscapacidadController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('discapacidad.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('discapacidad/modales/store', [DiscapacidadController::class, 'store'])->name('discapacidad.modales.store');
        Route::post('discapacidad/{id}/update', [DiscapacidadController::class, 'update'])->name('discapacidad.modales.update');
        Route::delete('discapacidad/{id}', [DiscapacidadController::class, 'destroy'])->name('discapacidad.destroy');
    });

    // ===== GRADO ÁREA FORMACIÓN (TRANSACCIÓN) =====
    Route::get('transacciones/grado_area_formacion', [GradoAreaFormacionController::class, 'index'])->middleware('permission:grado_area_formacion.ver')->name('transacciones.grado_area_formacion.index');

    Route::middleware(['verificar.anio.escolar', 'permission:grado_area_formacion.gestionar'])->group(function () {
        Route::post('transacciones/grado_area_formacion/modales/store', [GradoAreaFormacionController::class, 'store'])->name('transacciones.grado_area_formacion.modales.store');
        Route::post('transacciones/grado_area_formacion/{id}/update', [GradoAreaFormacionController::class, 'update'])->name('transacciones.grado_area_formacion.modales.update');
        Route::delete('transacciones/grado_area_formacion/{id}', [GradoAreaFormacionController::class, 'destroy'])->name('transacciones.grado_area_formacion.destroy');
    });

    // ===== PREFIJO DE TELÉFONO =====
    Route::get('prefijo_telefono', [PrefijoTelefonoController::class, 'index'])->middleware('permission:catalogos.ver')->name('prefijo_telefono.index');
    Route::get('prefijo_telefono/verificar', [PrefijoTelefonoController::class, 'verificarExistencia'])->middleware('permission:catalogos.ver')->name('prefijo_telefono.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:catalogos.gestionar'])->group(function () {
        Route::post('prefijo_telefono/modales/store', [PrefijoTelefonoController::class, 'store'])->name('prefijo_telefono.modales.store');
        Route::post('prefijo_telefono/{id}/update', [PrefijoTelefonoController::class, 'update'])->name('prefijo_telefono.modales.update');
        Route::delete('prefijo_telefono/{id}', [PrefijoTelefonoController::class, 'destroy'])->name('prefijo_telefono.destroy');
    });

    // ===== ROLES Y ACCESOS =====
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:usuarios_accesos.ver')->name('roles.index');
    Route::get('roles/permisos/{id}', [RoleController::class, 'permisos'])->middleware('permission:usuarios_accesos.ver')->name('roles.permisos');
    Route::get('usuarios-accesos', function () {
        return view('admin.usuarios.index');
    })->middleware('permission:usuarios.gestionar_modulos')
      ->name('usuarios.index');

    Route::middleware(['verificar.anio.escolar', 'permission:usuarios_accesos.gestionar'])->group(function () {
        Route::post('roles/modales/store', [RoleController::class, 'store'])->name('roles.modales.store');
        Route::post('roles/{id}/update', [RoleController::class, 'update'])->name('roles.modales.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // ===== ESTUDIOS REALIZADOS =====
    Route::get('estudios_realizados', [EstudiosRealizadoController::class, 'index'])->middleware('permission:estudios_realizados.ver')->name('estudios_realizados.index');
    Route::get('estudios_realizados/verificar', [EstudiosRealizadoController::class, 'verificarExistencia'])->middleware('permission:estudios_realizados.ver')->name('estudios_realizados.verificar');

    Route::middleware(['verificar.anio.escolar', 'permission:estudios_realizados.gestionar'])->group(function () {
        Route::post('estudios_realizados/modales/store', [EstudiosRealizadoController::class, 'store'])->name('estudios_realizados.modales.store');
        Route::post('estudios_realizados/{id}/update', [EstudiosRealizadoController::class, 'update'])->name('estudios_realizados.modales.update');
        Route::delete('estudios_realizados/{id}', [EstudiosRealizadoController::class, 'destroy'])->name('estudios_realizados.destroy');
    });

    // ===== ÁREA ESTUDIO REALIZADO (TRANSACCIÓN) =====
    Route::get('transacciones/area_estudio_realizado', [AreaEstudioRealizadoController::class, 'index'])->middleware('permission:area_estudio_realizado.ver')->name('transacciones.area_estudio_realizado.index');

    Route::middleware(['verificar.anio.escolar', 'permission:area_estudio_realizado.gestionar'])->group(function () {
        Route::post('transacciones/area_estudio_realizado/modales/store', [AreaEstudioRealizadoController::class, 'store'])->name('transacciones.area_estudio_realizado.modales.store');
        Route::post('transacciones/area_estudio_realizado/{id}/update', [AreaEstudioRealizadoController::class, 'update'])->name('transacciones.area_estudio_realizado.modales.update');
        Route::delete('transacciones/area_estudio_realizado/{id}', [AreaEstudioRealizadoController::class, 'destroy'])->name('transacciones.area_estudio_realizado.destroy');
    });

    // ===== DOCENTE ======
    Route::get('docente', [DocenteController::class, 'index'])->middleware('permission:personal.ver')->name('docente.index');
    Route::get('docente/{id}/estudios', [DocenteController::class, 'estudios'])->middleware('permission:personal.ver')->name('docente.estudios');
    Route::get('docente/{id}/disponibilidad', [DocenteController::class, 'disponibilidad'])->middleware('permission:personal.ver')->name('docente.disponibilidad');
    Route::get('docente/reporte/{id}', [DocenteController::class, 'reportePDF'])->middleware('permission:personal.ver')->name('docente.reportePDF');
    Route::get('docente/reporte-general', [DocenteController::class, 'reporteGeneralPDF'])->middleware('permission:personal.ver')->name('docente.reporteGeneralPDF');
    Route::get('docente/reporte-materia', [DocenteController::class, 'DocenteMateria'])->middleware('permission:personal.ver')->name('docente.DocenteMateria');
    Route::get('docente/reporte-materia/{id}', [DocenteController::class, 'DocenteMateria'])->middleware('permission:personal.ver')->name('docente.DocenteMateriaIndividual');

    // Búsquedas y autocompletado por cédula (devuelven datos personales completos -> personal.gestionar)
    Route::get('docente/verificar-cedula', [DocenteController::class, 'verificarCedula'])
        ->middleware('permission:personal.gestionar')
        ->name('docente.verificar_cedula');

    Route::middleware(['verificar.anio.escolar', 'permission:personal.gestionar'])->group(function () {
        Route::get('docente/create', [DocenteController::class, 'create'])->name('docente.create');
        Route::post('docente/store', [DocenteController::class, 'store'])->name('docente.store');
        Route::get('docente/{id}/edit', [DocenteController::class, 'edit'])->name('docente.edit');
        Route::post('docente/{id}/update', [DocenteController::class, 'update'])->name('docente.update');
        Route::delete('docente/{id}', [DocenteController::class, 'destroy'])->name('docente.destroy');
        Route::post('docente/{id}/estudiosEdit', [DocenteController::class, 'estudiosEdit'])->name('docente.estudiosEdit');
    });

    // ===== TRANSACCION DOCENTE ======
    Route::get('transacciones/docente_area_grado', [DocenteAreaGradoController::class, 'index'])->middleware('permission:asignacion_docente.ver')->name('transacciones.docente_area_grado.index');
    Route::get('transacciones/docente_area_grado/reporte-general', [DocenteAreaGradoController::class, 'reportePDFGeneral'])->middleware('permission:asignacion_docente.ver')->name('transacciones.docente_area_grado.reportePDFGeneral');

    Route::middleware(['verificar.anio.escolar', 'permission:asignacion_docente.gestionar'])->group(function () {
        Route::get('transacciones/docente_area_grado/create', [DocenteAreaGradoController::class, 'create'])->name('transacciones.docente_area_grado.create');
        Route::post('transacciones/docente_area_grado/store', [DocenteAreaGradoController::class, 'store'])->name('transacciones.docente_area_grado.store');
        Route::get('transacciones/docente_area_grado/{id}/edit', [DocenteAreaGradoController::class, 'edit'])->name('transacciones.docente_area_grado.edit');
        Route::post('transacciones/docente_area_grado/{id}/update', [DocenteAreaGradoController::class, 'update'])->name('transacciones.docente_area_grado.update');
        Route::delete('transacciones/docente_area_grado/{id}', [DocenteAreaGradoController::class, 'destroy'])->name('transacciones.docente_area_grado.destroy');
        Route::delete('transacciones/docente_area_grado/{id}/destroy-asignacion', [DocenteAreaGradoController::class, 'destroyAsignacion'])->name('transacciones.docente_area_grado.destroyAsignacion');
    });


    // ===== ALUMNOS ======
    Route::get('alumnos',  [AlumnoController::class, 'index'])->middleware('permission:alumnos.ver')->name('alumnos.index');
    Route::get('alumnos/reporte/{id}', [AlumnoController::class, 'reportePDF'])->middleware('permission:alumnos.ver')->name('alumnos.reporte.individual');
    Route::get('alumnos/reportes/general', [AlumnoController::class, 'reporteGeneralPDF'])->middleware('permission:alumnos.ver')->name('alumnos.reporteGeneralPDF');
    Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->middleware('role_or_permission:alumnos.ver|Representante|Docente')->name('alumnos.show');

    Route::middleware(['verificar.anio.escolar', 'permission:alumnos.gestionar'])->group(function () {
        Route::get('alumnos/create', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos/store', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::get('alumnos/{alumno}/edit', [AlumnoController::class, 'edit'])->name('alumnos.edit');
        Route::post('alumnos/{alumno}/update', [AlumnoController::class, 'update'])->name('alumnos.update');
        Route::delete('alumnos/{id}', [AlumnoController::class, 'destroy'])->name('alumnos.destroy');
    });

    // ================== TRANSACCIONES ==================
    Route::prefix('transacciones')->name('transacciones.')->group(function () {

        // ========= INSCRIPCIONES (NUEVO INGRESO) =========
        Route::get('inscripcion', [InscripcionController::class, 'index'])->middleware('permission:inscripciones.ver')->name('inscripcion.index');
        Route::get('inscripcion/reporte/{id}', [InscripcionController::class, 'reporte'])->middleware('permission:inscripciones.ver')->name('inscripcion.reporte');
        Route::get('inscripcion/reportes/nuevo-ingreso', [InscripcionController::class, 'reporteGeneralNuevoIngresoPDF'])->middleware('permission:inscripciones.ver')->name('inscripcion.reporteGeneralNuevoIngresoPDF');
        Route::get('secciones-por-grado/{grado}', [InscripcionController::class, 'seccionesPorGrado'])->middleware('permission:inscripciones.ver')->name('secciones.por-grado');

        Route::middleware(['permission:inscripciones.gestionar'])->group(function () {
            Route::get('inscripcion/create', function () {
                return view('admin.transacciones.inscripcion.create');
            })->name('inscripcion.create');
            Route::get('inscripcion/create-alumno', function () {
                return view('admin.transacciones.inscripcion.create-alumno');
            })->name('inscripcion.create-alumno');
            Route::get('/inscripcion/{id}/edit', [InscripcionController::class, 'edit'])->name('inscripcion.edit');
            Route::delete('inscripcion/{id}', [InscripcionController::class, 'destroy'])->name('inscripcion.destroy');
            Route::get('inscripcion/{id}/restore', [InscripcionController::class, 'restore'])->name('inscripcion.restore');
        });

        // ========= INSCRIPCIÓN PROSECUCIÓN =========
        Route::get('inscripcion_prosecucion', [InscripcionProsecucionController::class, 'index'])->middleware('permission:prosecucion.ver')->name('inscripcion_prosecucion.index');
        Route::get('inscripcion-prosecucion/{id}/reporte', [InscripcionProsecucionController::class, 'reporte'])->middleware('permission:prosecucion.ver')->name('inscripcion_prosecucion.reporte');
        Route::get('inscripcion_prosecucion/reportes/general', [InscripcionProsecucionController::class, 'reporteGeneralProsecucionPDF'])->middleware('permission:prosecucion.ver')->name('inscripcion_prosecucion.reporteGeneralProsecucionPDF');

        Route::middleware(['permission:prosecucion.gestionar'])->group(function () {
            Route::get('inscripcion_prosecucion/create', function () {
                return view('admin.transacciones.inscripcion_prosecucion.create');
            })->name('inscripcion_prosecucion.create');
            Route::get('inscripcion_prosecucion/create-alumno', function () {
                return view('admin.transacciones.inscripcion_prosecucion.create-alumno');
            })->name('inscripcion_prosecucion.create-alumno');
            Route::delete('inscripcion-prosecucion/{inscripcion}', [InscripcionProsecucionController::class, 'destroy'])->name('inscripcion_prosecucion.destroy');
            Route::get('inscripcion-prosecucion/{id}/restore', [InscripcionProsecucionController::class, 'restore'])->name('inscripcion_prosecucion.restore');
        });

        // ========= PERCENTIL =========
        Route::get('percentil', [EntradasPercentilController::class, 'index'])->middleware('permission:percentil.ver')->name('percentil.index');
        Route::post('percentil/boton-percentil', [EntradasPercentilController::class, 'ejecutar'])->middleware('permission:percentil.gestionar')->name('percentil.boton-percentil');
    });

    // ================== HISTÓRICO ==================
    Route::get('historico', [HistoricoController::class, 'index'])->middleware('permission:historico.ver')->name('historico.index');

    // ================== PERMISOS Y REPOSOS ==================
    Route::get('permisos_reposos', [Permisos_RepososController::class, 'index'])->middleware('permission:permisos_reposos.ver')->name('permisos_reposos.index');
    Route::get('permisos_reposos/verificar', [Permisos_RepososController::class, 'verificarExistencia'])->middleware('permission:permisos_reposos.ver')->name('permisos_reposos.verificar');

    Route::middleware(['permission:permisos_reposos.gestionar'])->group(function () {
        Route::get('permisos_reposos/create', [Permisos_RepososController::class, 'create'])->name('permisos_reposos.create');
        Route::middleware(['verificar.anio.escolar'])->group(function () {
            Route::post('permisos_reposos/modales/store', [Permisos_RepososController::class, 'store'])->name('permisos_reposos.modales.store');
            Route::post('permisos_reposos/{id}/update', [Permisos_RepososController::class, 'update'])->name('permisos_reposos.modales.update');
            Route::delete('permisos_reposos/{id}', [Permisos_RepososController::class, 'destroy'])->name('permisos_reposos.destroy');
        });
    });

    // ================== SECCION_AULA (AULAS) ==================
    Route::get('seccion_aula', [Seccion_AulaController::class, 'index'])->middleware('permission:aulas.ver')->name('seccion_aula.index');
    Route::get('seccion_aula/verificar', [Seccion_AulaController::class, 'verificarExistencia'])->middleware('permission:aulas.ver')->name('seccion_aula.verificar');
    Route::get('seccion_aula/secciones-por-grado/{grado}', [Seccion_AulaController::class, 'seccionesPorGrado'])->middleware('permission:aulas.ver')->name('seccion_aula.secciones-por-grado');

    Route::middleware(['verificar.anio.escolar', 'permission:aulas.gestionar'])->group(function () {
        Route::post('seccion_aula/modales/store', [Seccion_AulaController::class, 'store'])->name('seccion_aula.modales.store');
        Route::put('seccion_aula/{id}', [Seccion_AulaController::class, 'update'])->name('seccion_aula.modales.update');
        Route::delete('seccion_aula/{id}', [Seccion_AulaController::class, 'destroy'])->name('seccion_aula.destroy');
    });

    // ================== INASISTENCIA ==================
    Route::get('inasistencia', [InasistenciaController::class, 'index'])->middleware('permission:inasistencia_docente.ver')->name('inasistencia.index');
    Route::get('inasistencia/inasistencia_justificacion', [InasistenciaController::class, 'inasistencia_justificacion'])->middleware('permission:justificaciones.ver')->name('inasistencia.justificacion');

    // ================== HORARIO ==================
    Route::get('horario', [HorarioController::class, 'index'])->middleware('permission:horarios.ver')->name('horario.index');
    Route::get('horario/{grado_id}/{seccion_id}/show-api', [HorarioController::class, 'showApi'])->middleware('permission:horarios.ver')->name('horario.show-api');

    Route::middleware(['permission:horarios.gestionar'])->group(function () {
        Route::get('horario/create', [HorarioController::class, 'create'])->name('horario.create');
        Route::get('horario/{grado_id}/{seccion_id}/edit', [HorarioController::class, 'edit'])->name('horario.edit');
        Route::put('horario/{grado_id}/{seccion_id}', [HorarioController::class, 'update'])->name('horario.update');
        Route::delete('horario/{grado_id}/{seccion_id}', [HorarioController::class, 'destroy'])->name('horario.destroy');
    });

});

// ====== PORTAL DEL REPRESENTANTE ======
Route::middleware(['auth', 'verified', 'forzar.cambio.password', 'role:Representante'])->prefix('portal-representante')->name('portal-representante.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PortalRepresentanteController::class, 'index'])->name('index');
    
    Route::prefix('prosecucion')->name('prosecucion.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PortalRepresentanteController::class, 'prosecucionIndex'])
            ->middleware('permission:portal_prosecucion.ver_planilla')
            ->name('index');
        Route::get('/registrar', [\App\Http\Controllers\PortalRepresentanteController::class, 'prosecucionCreate'])
            ->middleware('permission:portal_prosecucion.registrar')
            ->name('create');
    });
    
    Route::prefix('carnet')->name('carnet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PortalRepresentanteController::class, 'carnetIndex'])
            ->middleware('permission:portal_carnets.ver_estudiante')
            ->name('index');
        Route::get('/imprimir', [\App\Http\Controllers\PortalRepresentanteController::class, 'carnetImprimir'])
            ->middleware('permission:portal_carnets.generar_estudiante')
            ->name('imprimir');
    });
});


// ======  AULAS  ======
Route::middleware(['auth', 'verified', 'forzar.cambio.password'])->group(function () {
    Route::get('aulas', [AulaController::class, 'index'])->middleware('permission:aulas.ver')->name('aulas.index');
    
    Route::middleware(['permission:aulas.gestionar'])->group(function () {
        Route::get('aulas/create', [AulaController::class, 'create'])->name('aulas.create');
        Route::post('aulas', [AulaController::class, 'store'])->name('aulas.store');
        Route::get('aulas/{aula}/edit', [AulaController::class, 'edit'])->name('aulas.edit');
        Route::match(['put', 'patch'], 'aulas/{aula}', [AulaController::class, 'update'])->name('aulas.update');
        Route::delete('aulas/{aula}', [AulaController::class, 'destroy'])->name('aulas.destroy');
        Route::post('aulas/verificar-existencia', [AulaController::class, 'verificarExistencia'])->name('aulas.verificarExistencia');
    });

    Route::get('aulas/{aula}', [AulaController::class, 'show'])->middleware('permission:aulas.ver')->name('aulas.show');
});


// ======  REPRESENTANTE (PANEL ADMINISTRATIVO)  ======
Route::middleware(['auth', 'verified', 'forzar.cambio.password'])->prefix('representante')->name('representante.')->group(function () {
    // Lecturas y consultas
    Route::get('/', [RepresentanteController::class, 'index'])->middleware('permission:representantes.ver')->name('index');
    Route::get('/eliminados', [RepresentanteController::class, 'eliminados'])->middleware('permission:representantes.ver')->name('eliminados');
    Route::get('/consultar', [RepresentanteController::class, 'consultar'])->middleware('permission:representantes.ver')->name('consultar');
    Route::get('/filtrar', [RepresentanteController::class, 'filtar'])->middleware('permission:representantes.ver')->name('filtrar');
    Route::get('/reporte-pdf', [RepresentanteController::class, 'reportePDF'])->middleware('permission:representantes.ver')->name('reporte_pdf');

    // Búsqueda por cédula y verificación (devuelven datos personales completos, usadas también en inscripción y prosecución)
    Route::get('/buscar-numero_documento', [RepresentanteController::class, 'buscarPornumero_documento'])
        ->middleware('permission:representantes.gestionar|inscripciones.gestionar|prosecucion.gestionar')
        ->name('buscar_numero_documento');
    Route::get('/verificar-numero_documento', [RepresentanteController::class, 'verificarnumero_documento'])
        ->middleware('permission:representantes.gestionar|inscripciones.gestionar|prosecucion.gestionar')
        ->name('verificar_numero_documento');

    // Gestión completa (crear, editar, eliminar, restaurar)
    Route::middleware(['permission:representantes.gestionar'])->group(function () {
        Route::get('/formulario', [RepresentanteController::class, 'mostrarFormulario'])->name('formulario');
        Route::post('/save', [RepresentanteController::class, 'save'])->name('save');
        Route::get('/{id}/editar', [RepresentanteController::class, 'mostrarFormularioEditar'])->name('editar');
        Route::put('/{id}', [RepresentanteController::class, 'update'])->name('update');
        Route::delete('/{id}', [RepresentanteController::class, 'delete'])->name('destroy');
        Route::post('/{id}/restaurar', [RepresentanteController::class, 'restaurar'])->name('restaurar');
    });
});

