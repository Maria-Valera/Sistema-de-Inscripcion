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
