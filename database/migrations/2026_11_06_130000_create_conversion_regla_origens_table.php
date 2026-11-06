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
        Schema::create('conversion_regla_origens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversion_regla_id')
                  ->constrained('conversion_reglas')
                  ->cascadeOnDelete()
                  ->comment('Regla de conversión a la que pertenece este origen');

            $table->foreignId('grado_origen_id')
                  ->constrained('grados')
                  ->cascadeOnDelete()
                  ->comment('Grado cursado en el plan de origen');

            $table->foreignId('area_origen_id')
                  ->constrained('area_formacions')
                  ->cascadeOnDelete()
                  ->comment('Área cursada en el plan de origen que aporta a la conversión');

            $table->timestamps();

            // Evita duplicar el mismo origen en una misma regla de conversión
            $table->unique(
                ['conversion_regla_id', 'grado_origen_id', 'area_origen_id'],
                'conv_regla_origen_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_regla_origens');
    }
};

