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
        Schema::create('configuracion_evaluacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')
                ->unique()
                ->constrained('anio_escolars');
            $table->tinyInteger('nota_minima')->default(1);
            $table->tinyInteger('nota_maxima')->default(20);
            $table->tinyInteger('nota_aprobatoria')->default(10);
            $table->string('regla_redondeo', 20)->default('half_up');
            $table->tinyInteger('num_lapsos')->default(3);
            $table->boolean('aplica_art_112')->default(false);
            $table->decimal('umbral_aplazados_pct', 5, 2)->default(30.00);
            $table->tinyInteger('max_areas_pendientes')->default(2);
            $table->tinyInteger('min_actividades_revision')->default(3);
            $table->tinyInteger('max_formas_revision')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_evaluacions');
    }
};
