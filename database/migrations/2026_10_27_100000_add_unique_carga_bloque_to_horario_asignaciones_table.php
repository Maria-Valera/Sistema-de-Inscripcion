<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horario_asignaciones', function (Blueprint $table) {
            $table->unique(
                ['anio_escolar_id', 'carga_academica_id', 'dia', 'bloque'],
                'unique_carga_bloque'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horario_asignaciones', function (Blueprint $table) {
            $table->dropUnique('unique_carga_bloque');
        });
    }
};
