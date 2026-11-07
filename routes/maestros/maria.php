<?php

// Maestros de María Valera. Solo María edita este archivo.
// Se carga dentro del grupo admin de routes/web.php: prefijo /admin, nombre admin.*,
// auth, verified y forzar.cambio.password.

use App\Http\Controllers\CalendarioAcademicoController;
use App\Http\Controllers\ConfiguracionEvaluacionController;
use Illuminate\Support\Facades\Route;

// ===== M1 y M2: Calendario académico y sus eventos =====
// Sin verificar.anio.escolar: se puede cargar el calendario de un año que todavía no esté activo.
Route::middleware(['permission:calendario_academico.ver'])->group(function () {
    Route::get('calendario_academico', [CalendarioAcademicoController::class, 'index'])->name('calendario_academico.index');
});

Route::middleware(['permission:calendario_academico.gestionar'])->group(function () {
    Route::get('calendario_academico/nuevo/manual', [CalendarioAcademicoController::class, 'createManual'])->name('calendario_academico.create_manual');
    Route::post('calendario_academico/manual', [CalendarioAcademicoController::class, 'storeManual'])->name('calendario_academico.store_manual');
    Route::put('calendario_academico/{calendarioAcademico}', [CalendarioAcademicoController::class, 'update'])->name('calendario_academico.update');
    Route::delete('calendario_academico/{calendarioAcademico}', [CalendarioAcademicoController::class, 'destroy'])->name('calendario_academico.destroy');

    Route::post('calendario_academico/{calendarioAcademico}/eventos', [CalendarioAcademicoController::class, 'eventoStore'])->name('calendario_academico.eventos.store');
    Route::put('calendario_academico/{calendarioAcademico}/eventos/{calendarioDia}', [CalendarioAcademicoController::class, 'eventoUpdate'])->name('calendario_academico.eventos.update');
    Route::delete('calendario_academico/{calendarioAcademico}/eventos/{calendarioDia}', [CalendarioAcademicoController::class, 'eventoDestroy'])->name('calendario_academico.eventos.destroy');
});

// Va al final: si fuera primero, 'calendario_academico/{calendarioAcademico}' atraparia 'nuevo/manual'.
Route::get('calendario_academico/{calendarioAcademico}', [CalendarioAcademicoController::class, 'show'])
    ->middleware('permission:calendario_academico.ver')->name('calendario_academico.show');

// ===== M3: Configuración de evaluación =====
Route::get('configuracion_evaluacion', [ConfiguracionEvaluacionController::class, 'index'])
    ->middleware('permission:configuracion_evaluacion.ver')->name('configuracion_evaluacion.index');

Route::middleware(['permission:configuracion_evaluacion.gestionar'])->group(function () {
    Route::post('configuracion_evaluacion', [ConfiguracionEvaluacionController::class, 'store'])->name('configuracion_evaluacion.store');
    Route::put('configuracion_evaluacion/{configuracionEvaluacion}', [ConfiguracionEvaluacionController::class, 'update'])->name('configuracion_evaluacion.update');
    Route::delete('configuracion_evaluacion/{configuracionEvaluacion}', [ConfiguracionEvaluacionController::class, 'destroy'])->name('configuracion_evaluacion.destroy');
});
