<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_area_grados', function (Blueprint $table) {
            $table->id();


            // FK a detalle_docente_estudios
            $table->foreignId('docente_estudio_realizado_id')
                ->constrained('detalle_docente_estudios')
                ->onDelete('cascade');

            // FK a áreas de estudios
            $table->foreignId('area_estudio_realizado_id')
                ->nullable()
                ->constrained('area_estudio_realizados')
                ->onDelete('cascade');

            // FK a grados
            $table->foreignId('grado_id')
                ->nullable()
                ->constrained('grados')
                ->onDelete('cascade');

            // FK a secciones
            $table->foreignId('seccion_id')
                ->nullable()
                ->constrained('seccions')
                ->onDelete('cascade');

            $table->foreignId('grupo_estable_id')
                ->nullable()
                ->constrained('grupo_estables')
                ->onDelete('cascade');

            $table->foreignId('grado_grupo_estable_id')
                ->nullable()
                ->constrained('grados')
                ->onDelete('cascade');

            $table->enum('tipo_asignacion', ['area', 'grupo_estable'])
                ->default('area');

            $table->boolean('status')->default(true);

            

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_area_grados');
    }
};
