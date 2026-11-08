<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina las columnas que solo existían para soportar el camino PDF
     * (extracción automática + revisión de candidatos). El sistema se queda
     * únicamente con el camino manual, así que ya no hacen falta.
     */
    public function up(): void
    {
        Schema::table('calendario_academico', function (Blueprint $table) {
            $table->dropColumn(['pdf_original', 'origen']);
        });

        // "mes_pagina" NO se elimina: aunque nació para el camino PDF, el
        // controlador lo sigue usando también en el camino manual (ver
        // eventoStore/eventoUpdate) para agrupar los eventos por mes en la
        // pestaña "Lista". Eliminarlo rompería el flujo manual.
        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->dropColumn(['dia_extraido']);
        });
    }

    public function down(): void
    {
        Schema::table('calendario_academico', function (Blueprint $table) {
            $table->string('pdf_original')->nullable();
            $table->enum('origen', ['pdf', 'manual'])->default('manual');
        });

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->string('dia_extraido', 30)->nullable();
        });
    }
};