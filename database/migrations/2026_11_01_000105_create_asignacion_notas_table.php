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
        Schema::create('asignacion_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')
                ->constrained('anio_escolars');
            $table->foreignId('docente_id')
                ->constrained('docentes');
            $table->string('tipo', 20)->default('area');
            $table->foreignId('area_formacion_id')
                ->nullable()
                ->constrained('area_formacions');
            $table->foreignId('grado_id')
                ->constrained('grados');
            $table->foreignId('seccion_id')
                ->nullable()
                ->constrained('seccions');
            $table->foreignId('grupo_estable_id')
                ->nullable()
                ->constrained('grupo_estables');
            $table->foreignId('docente_area_grado_id')
                ->nullable()
                ->constrained('docente_area_grados');
            $table->boolean('activa')->default(true);
            $table->timestamp('sincronizada_at');
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'docente_area_grado_id']);
            $table->index(['anio_escolar_id', 'docente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_notas');
    }
};
