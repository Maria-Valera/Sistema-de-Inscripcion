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
        Schema::create('calificacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')
                ->constrained('evaluacions')
                ->restrictOnDelete();
            $table->foreignId('inscripcion_id')
                ->constrained('inscripcions')
                ->restrictOnDelete();
            $table->decimal('nota', 5, 2)->nullable();
            $table->string('estado_asistencia', 25)->default('presente');
            $table->string('justificacion', 500)->nullable();
            $table->foreignId('exoneracion_id')
                ->nullable()
                ->constrained('exoneracions')
                ->restrictOnDelete();
            $table->foreignId('responsabilidad_interina_id')
                ->nullable()
                ->constrained('responsabilidad_interinas')
                ->restrictOnDelete();
            $table->date('fecha_reprogramada')->nullable();
            $table->string('observacion', 500)->nullable();
            $table->foreignId('cargado_por')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('cargado_at');
            $table->timestamps();

            $table->unique(['evaluacion_id', 'inscripcion_id']);
            $table->index('inscripcion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificacions');
    }
};
