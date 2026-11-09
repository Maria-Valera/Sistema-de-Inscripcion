<?php

// Maestros de Stephania Linarez: Calendario académico, Eventos, Configuración de evaluación, Lapsos, Cortes.
// Solo Stephania edita este archivo. Se carga dentro del grupo admin de routes/web.php.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Horario\SeleccionarAnioEscolarController;
use App\Http\Controllers\Horario\AnioEscolarController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\EscalaLiteralController;
use App\Http\Controllers\AutoridadFirmanteController;

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

// Maestro S2: Escala Literal
Route::middleware('permission:escala_literal.ver')->group(function () {
    Route::get('escala-literal', [EscalaLiteralController::class, 'index'])->name('escala-literal.index');
});
Route::middleware('permission:escala_literal.gestionar')->group(function () {
    Route::resource('escala-literal', EscalaLiteralController::class)->except(['index', 'show']);
});

// Maestro S4: Autoridades Firmantes
Route::middleware('permission:autoridad_firmante.ver')->group(function () {
    Route::get('autoridad-firmante', [AutoridadFirmanteController::class, 'index'])->name('autoridad-firmante.index');
});
Route::middleware('permission:autoridad_firmante.gestionar')->group(function () {
    Route::resource('autoridad-firmante', AutoridadFirmanteController::class)->except(['index', 'show']);
});

// Maestro S0: Gestión de Años Escolares
Route::middleware(['permission:anio_escolar.gestionar'])->group(function () {
    Route::resource('anio-escolar', AnioEscolarController::class);
});
