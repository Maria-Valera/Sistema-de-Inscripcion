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
        Schema::create('_docente__no_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->onDelete('cascade');
            $table->foreignId('dias_semana_id')->constrained('dias_semana')->onDelete('cascade');
            $table->foreignId('id_bloque_hora')->constrained('bloque_horarios')->onDelete('cascade');
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_docente__no_disponibilidad');
    }
};
