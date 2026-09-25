<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

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
            // nullable por si se siembra el diccionario base mediante un seeder
            // es inicial del sistema, no tiene una persona asociada por ahora
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
