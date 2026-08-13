<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Diccionario global e independiente que se usa
     * para decidir si una línea del PDF es un día no laborable o una efeméride.
     *
     * NO tiene llave foránea hacia calendarios_academicos a propósito ya que
     * es un catálogo compartido entre todos los años escolares. Agregar una
     * palabra nueva no afecta calendarios ya confirmados de años anteriores.
     */
    public function up(): void
    {
        Schema::create('calendario_palabras_clave', function (Blueprint $table) {
            $table->id();
            // ej :  "asueto", "vacaciones", "feriado", "inicio de clases", "fin de clases", "natalicio de..."
            $table->string('palabra',100);

            // que es lo que significa encontrar esa palabra dentro de esa linea
            $table->enum('categoria', ['no_laborable','efemeride']);

            // permite "apagar" una palabra sin borrar el historial de que existio.
            $table->boolean('activa')->default(true);

            // trazabilidad : que persona agrego o modifico esta palabra clave.
            // nullable por si se siembra el diccionario base mediante un seeder (planeado)
            // es inicial del sistema, no tiene una persona asociada
            $table->foreignId('agregada_por')
            ->nullable()
            ->constrained('personas')
            ->nullOnDelete();

            $table->timestamps();

            // evita duplicar la misma palabra dos veces en la misma categoria
            $table->unique(['palabra','categoria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_palabras_clave');
    }
};
