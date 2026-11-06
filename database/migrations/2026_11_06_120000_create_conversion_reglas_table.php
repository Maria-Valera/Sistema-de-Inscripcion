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
        Schema::create('conversion_reglas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pensum_origen_id')
                  ->constrained('pensums')
                  ->cascadeOnDelete()
                  ->comment('Plan de estudio desde donde viene el estudiante (ej. 31059)');

            $table->foreignId('pensum_destino_id')
                  ->constrained('pensums')
                  ->cascadeOnDelete()
                  ->comment('Plan de estudio al que se convierte/homologa (ej. 31060)');

            $table->foreignId('grado_destino_id')
                  ->constrained('grados')
                  ->cascadeOnDelete()
                  ->comment('Grado o año en el pensum destino');

            $table->foreignId('area_destino_id')
                  ->constrained('area_formacions')
                  ->cascadeOnDelete()
                  ->comment('Asignatura resultante en el pensum destino');

            $table->enum('tipo_regla', ['directa', 'promedio', 'exonerada', 'no_cuenta'])
                  ->comment('Regla MPPE: directa (1 origen), promedio (>=2 orígenes), exonerada (0 orígenes), no_cuenta (0 orígenes)');

            $table->string('observacion', 255)->nullable()
                  ->comment('Base legal o cita oficial (ej. Circular 01 de enero 2024)');

            $table->boolean('status')->default(true);
            $table->timestamps();

            // Evita reglas duplicadas para la misma asignatura destino en una transición dada
            $table->unique(
                ['pensum_origen_id', 'pensum_destino_id', 'grado_destino_id', 'area_destino_id'],
                'conv_regla_destino_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_reglas');
    }
};

