<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      /**
     * Tabla "padre" del módulo: representa el proceso de carga del calendario
     * para un año escolar específico, sin importar si se hizo por PDF o manual.
     */
    public function up(): void
    {
        Schema::create('calendario_academico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')
            ->unique()
            ->constrained('anio_escolars')
            ->cascadeOnDelete();

            // este es el 'modo' de origen que eligio el usuario este año para crear el año escolar
            $table->enum('origen', ['pdf', 'manual']);

            // solo aplica si el atributo origen es = 'pdf' se queda nulo o nullable si se elige el modo manual
            // nunca se subo el archivo pdf
            $table->string('pdf_original')->nullable();

            // pendiente_revision : este atributo tiene sentido si el usuario tomo el camino de pdf y no manual, esto significa que se esta esperando calificacion humana
            // confirmado : este atributo significa que el calendario ya esta listo para que otros modulos del sistema lo usen
            // en el apartado manual, este estado puede pasar a "confirmado" apenas el usuario guarda su primer evento, o mantenerse en "pendiente_revision", hasta que el mismo usuario decida la carga del año.
            $table->enum('estado',['pendiente_revision', 'confirmado'])->default('pendiente_revision');

            $table->date('fecha_confirmacion')->nullable();




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_academico');
    }
};
