<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
      * Tabla principal del módulo: cada fila es una fecha (o rango) de calendario,
     * sin importar si llegó por extracción de PDF o por registro manual.
     */
    public function up(): void
    {
        Schema::create('calendario_dias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendario_id')
            ->constrained('calendario_academico')
            ->cascadeOnDelete();

            // camino pdf : la linea original del pdf es extraida , sin procesar todavia.
            // camino manual : la descripcion que el usuario escribio ( ej = "reunion pedagogica")
            // en ambos casos es el texto que identifica el evento
            $table->string('texto_extraido',500);

            // este atributo se llena solo si el usuario se va por el camino de pdf. queda nulo si el usuario se va por el camino manual.
            $table->string('mes_pagina',20)->nullable();

            // solo se llena si el usuario va por el camino del pdf. este atributo quedaq nulo si el usuario se va por el camino manual,
            // por que el usuario ya selecciona la fecha directamente en el formulario.
            $table->string('dia_extraido', 30)->nullable();

            $table->date('fecha')->nullable();

            $table->enum('categoria' , ['no_laborable','dudoso']);

            // alta : en el camino del pdf, coincidio con palabra clave no_laborable
            // dudosa : en el camino del pdf, no coincidio con nada conocido, asi que necesita revision humana
            // manual : el usuario lo registro directamente, ya sea desde el camino manual completo o agregando un evento suelto durante la revision de el pdf.
            $table->enum('confianza', ['alta','dudosa','manual']);

            $table->boolean('es_mes_completo')->default(false);

            // camino pdf :empieza en false, se activa tras la revision humana
            // camino manual : se guarda en true de una vez, por que el usuario ya confirmo el dato al escribirlo directamente.
            $table->boolean('confirmado')->default(false);

            $table->boolean('aplica_personal')->default(true);
            $table->boolean('aplica_estudiantes')->default(true);

            $table->timestamps();

            // se agrega "fecha" a la combinación única: en el camino manual , la misma descripcion ( ej = "reunion pedagogica") puede repetirse en meses distintos y eso es valido,
            // lo que no puede repetirse es la misma fecha con el mismo texto para el mismo calendario. ( ej = "reunion pedagogica" el 15 de marzo y otra "reunion pedagogica" el 16 de marzo es valido, pero dos "reunion pedagogica" el 15 de marzo no es valido)
            $table->unique(['calendario_id', 'fecha', 'texto_extraido'], 'calendario_dias_evitar_duplicados');

            $table->index(['calendario_id','confirmado', 'fecha']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_dias');
    }
};
