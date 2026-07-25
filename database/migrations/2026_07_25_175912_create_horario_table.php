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
        Schema::create('horario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aula_id');
            $table->foreign('aula_id')->references('id_aula')->on('aulas')->onDelete('cascade');
            $table->unsignedBigInteger('anio_escolar_id');
            $table->foreign('anio_escolar_id')->references('id')->on('anio_escolars')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};
