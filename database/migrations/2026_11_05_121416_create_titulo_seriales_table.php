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
        Schema::create('titulo_seriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->cascadeOnDelete();
            $table->string('serial', 30)->unique();
            $table->date('fecha_recepcion')->nullable();
            $table->enum('estado', ['disponible', 'asignado', 'anulado', 'devuelto'])->default('disponible');
            $table->string('motivo_anulacion')->nullable();
            $table->date('fecha_liquidacion')->nullable();  // fecha en que se devolvió a la Zona Educativa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titulo_seriales');
    }
};
