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
        Schema::create('area_formacion_posiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_formacion_id')->constrained('area_formacions')->cascadeOnDelete();
            $table->foreignId('grado_id')->nullable()->constrained('grados')->cascadeOnDelete();
            $table->unsignedBigInteger('grado_key')->storedAs('IFNULL(grado_id, 0)');
            $table->unsignedTinyInteger('dia');
            $table->unsignedTinyInteger('bloque');
            $table->string('tipo'); // 'prohibido', 'no_preferente', 'no_preferente_fuerte'
            $table->timestamps();

            $table->unique(['area_formacion_id', 'grado_key', 'dia', 'bloque'], 'unique_area_grado_dia_bloque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_formacion_posiciones');
    }
};
