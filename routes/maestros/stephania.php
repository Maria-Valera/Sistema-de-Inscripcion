<?php

// Maestros de Stephania Linarez: Calendario académico, Eventos, Configuración de evaluación, Lapsos, Cortes.
// Solo Stephania edita este archivo. Se carga dentro del grupo admin de routes/web.php.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Horario\SeleccionarAnioEscolarController;
use App\Http\Controllers\InstitucionController;

Route::middleware(['permission:anio_escolar.cambiar'])->group(function () {
    Route::get('seleccionar-anio', [SeleccionarAnioEscolarController::class, 'index'])
        ->name('anio-escolar.seleccionar');

    Route::post('seleccionar-anio', [SeleccionarAnioEscolarController::class, 'guardar'])
        ->name('anio-escolar.guardar');
});

// Maestro S1: Datos del Plantel (Instituciones)
Route::middleware(['permission:institucion.ver|institucion.gestionar'])->group(function () {
    Route::resource('institucion', InstitucionController::class);
});
