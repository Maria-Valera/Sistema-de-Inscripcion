<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BloqueHorarioController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\Dias_semanaController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\GeneradorHorarioController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Las rutas API operan dentro del pipeline de sesión ('web'), con límite
| de intentos ('throttle:60,1') y protección estricta por autenticación,
| verificación y permisos.
|
*/

Route::middleware(['web', 'throttle:60,1', 'auth', 'verified', 'forzar.cambio.password'])->group(function () {

    // ===== SELECTORES SIN DATOS PERSONALES =====
    Route::get('/bloques-horario', [BloqueHorarioController::class, 'apiIndex']);
    Route::get('/grado', [GradoController::class, 'apiIndex']);
    Route::get('/dias-semana', [Dias_semanaController::class, 'apiIndex']);
    Route::get('/seccion', [SeccionController::class, 'apiIndex']);
    Route::get('/areas-formacion/grado', [GeneradorHorarioController::class, 'obtenerAreasPorGrado']);
    Route::get('/anio-escolar/activo', [GeneradorHorarioController::class, 'getAnioEscolarActivoAPI']);

    // ===== DOCENTE PARA SELECTORES DE HORARIOS / GESTIÓN PERSONAL =====
    Route::get('/docente', [DocenteController::class, 'apiIndex'])
        ->middleware('permission:horarios.ver|personal.ver');

    // ===== GENERADOR DE HORARIOS API =====
    Route::get('/horario', [HorarioController::class, 'IndexApi'])
        ->middleware('permission:horarios.ver');

    Route::get('/horario/asignaciones', [GeneradorHorarioController::class, 'obtenerAsignaciones'])
        ->middleware('permission:horarios.ver')
        ->name('api.horario.obtener_asignaciones');

    // Operaciones de modificación de horarios
    Route::middleware(['permission:horarios.gestionar'])->group(function () {
        Route::post('/horario/guardar-asignaciones', [GeneradorHorarioController::class, 'guardarAsignaciones']);
        Route::post('/horario/generar', [GeneradorHorarioController::class, 'generar'])->name('api.horario.generar');
        Route::post('/horario/reacomodar', [GeneradorHorarioController::class, 'reacomodar'])->name('api.horario.reacomodar');
        Route::put('/horario/{id}/aula', [GeneradorHorarioController::class, 'actualizarAula']);
        Route::put('/horario/{id}/posicion', [GeneradorHorarioController::class, 'actualizarPosicion']);
        Route::put('/horario/intercambiar', [GeneradorHorarioController::class, 'intercambiar']);
        Route::delete('/horario/{id}', [GeneradorHorarioController::class, 'eliminarAsignacion']);
        Route::post('/horario/eliminar-multiples', [GeneradorHorarioController::class, 'eliminarAsignacionesMultiples']);
    });
});
