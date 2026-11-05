<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('materia_id')->constrained('area_formacions')->onDelete('cascade');
            $table->foreignId('seccion_id')->constrained('seccions')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aulas', 'id_aula')->onDelete('cascade');
            $table->foreignId('dia_id')->constrained('dias_semana')->onDelete('cascade');
            $table->foreignId('bloque_id')->constrained('bloque_horarios')->onDelete('cascade');
            $table->boolean('conflicto_manual')->default(false);
            $table->text('motivo_conflicto')->nullable();
            $table->timestamps();

            // Índices para mejor rendimiento
            $table->index(['anio_escolar_id', 'docente_id']);
            $table->index(['anio_escolar_id', 'seccion_id']);
            $table->index(['anio_escolar_id', 'dia_id', 'bloque_id']);
            $table->index(['docente_id', 'dia_id', 'bloque_id']);
            $table->index(['seccion_id', 'dia_id', 'bloque_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_asignaciones');
    }
};
