<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargas_academicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('area_formacion_id')->constrained('area_formacions')->onDelete('cascade');
            $table->foreignId('seccion_id')->constrained('seccions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(
                ['anio_escolar_id', 'docente_id', 'area_formacion_id', 'seccion_id'],
                'uq_carga_academica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargas_academicas');
    }
};
