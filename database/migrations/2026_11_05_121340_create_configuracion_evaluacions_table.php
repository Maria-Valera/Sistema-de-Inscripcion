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
            $table->foreignId('anio_escolar_id')->unique()->constrained('anio_escolars')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota_minima')->default(1);
            $table->unsignedTinyInteger('nota_maxima')->default(20);
            $table->unsignedTinyInteger('nota_aprobatoria')->default(10);
            $table->enum('regla_redondeo', ['half_up', 'truncar', 'techo'])->default('half_up');
            $table->unsignedTinyInteger('num_cortes')->default(4);
            $table->unsignedTinyInteger('puntos_corte')->default(20);
            $table->unsignedTinyInteger('num_lapsos')->default(3);        // 3 por defecto, minimo 2 (se valida en el FormRequest)
            $table->unsignedTinyInteger('momentos_quedada')->default(4);  // octubre, diciembre, enero, junio
            $table->unsignedTinyInteger('max_areas_pendientes')->default(2);
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
