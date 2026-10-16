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
        Schema::create('asignacion_nota_miembros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_nota_id')
                ->constrained('asignacion_notas');
            $table->foreignId('inscripcion_id')
                ->constrained('inscripcions');
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_retiro')->nullable();
            $table->timestamps();

            // Nombre de índice explícito para respetar el límite de 64 caracteres de MySQL
            $table->unique(['asignacion_nota_id', 'inscripcion_id'], 'asig_nota_miembros_asig_insc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_nota_miembros');
    }
};
