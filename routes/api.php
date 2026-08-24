<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BloqueHorarioController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\Dias_semanaController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\GeneradorHorarioController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/bloques-horario', [BloqueHorarioController::class, 'apiIndex']);
Route::get('/grado', [GradoController::class, 'apiIndex']);
Route::get('/dias-semana', [Dias_semanaController::class, 'apiIndex']);
Route::get('/seccion', [SeccionController::class, 'apiIndex']);
Route::get('/docente', [DocenteController::class, 'apiIndex']);
Route::get('/persona', [PersonaController::class, 'apiIndex']);
Route::get('/horario', [HorarioController::class, 'IndexApi']);
Route::get('/horario/asignaciones', [GeneradorHorarioController::class, 'obtenerAsignaciones']);
Route::get('/areas-formacion/grado', [GeneradorHorarioController::class, 'obtenerAreasPorGrado']);
Route::post('/horario/guardar-asignaciones', [GeneradorHorarioController::class, 'guardarAsignaciones']);
Route::post('/horario/generar', [GeneradorHorarioController::class, 'generar']);
Route::get('/anio-escolar/activo', [GeneradorHorarioController::class, 'getAnioEscolarActivoAPI']);
Route::put('/horario/{id}/aula', [GeneradorHorarioController::class, 'actualizarAula']);
