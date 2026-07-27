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
        Schema::create('horario__docente__area__formacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('horario_id');
            $table->foreign('horario_id')->references('id')->on('horario')->onDelete('cascade');
            $table->unsignedBigInteger('docente_area_grado_id');
            $table->foreign('docente_area_grado_id')->references('id')->on('docente_area_grados')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario__docente__area__formacion');
    }
};
