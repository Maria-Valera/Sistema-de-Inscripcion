<?php

// Maestros de José Gonzales: Pensums, Malla curricular, Reglas de conversión, Menciones.
// Solo José edita este archivo. Se carga dentro del grupo admin de routes/web.php.

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PensumController;
use App\Http\Controllers\MallaCurricularController;
use App\Http\Controllers\ConversionReglaController;
use App\Http\Controllers\MencionController;

// Maestro J1: Planes de Estudio (Pensums)
Route::middleware('permission:pensums.ver')->group(function () {
    Route::get('pensums', [PensumController::class, 'index'])->name('pensums.index');
});
Route::middleware('permission:pensums.gestionar')->group(function () {
    Route::resource('pensums', PensumController::class)->except(['index', 'show']);
});

// Maestro J2: Malla Curricular
Route::middleware('permission:malla_curricular.ver')->group(function () {
    Route::get('malla-curricular', [MallaCurricularController::class, 'index'])->name('malla-curricular.index');
});
Route::middleware('permission:malla_curricular.gestionar')->group(function () {
    Route::resource('malla-curricular', MallaCurricularController::class)->except(['index', 'show']);
});

// Maestro J3: Reglas de Conversión entre Pensums
Route::middleware('permission:conversion_reglas.ver')->group(function () {
    Route::get('conversion-reglas', [ConversionReglaController::class, 'index'])->name('conversion-reglas.index');
});
Route::middleware('permission:conversion_reglas.gestionar')->group(function () {
    Route::resource('conversion-reglas', ConversionReglaController::class)->except(['index', 'show']);
});

// Maestro J4: Menciones Curriculares
Route::middleware('permission:menciones.ver')->group(function () {
    Route::get('menciones', [MencionController::class, 'index'])->name('menciones.index');
});
Route::middleware('permission:menciones.gestionar')->group(function () {
    Route::resource('menciones', MencionController::class)->except(['index', 'show']);
    Route::put('secciones/{seccion}/asignar-mencion', [MencionController::class, 'asignarSeccion'])->name('secciones.asignar-mencion');
});
