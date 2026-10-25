<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina por completo la tabla "calendario_palabras_clave": existía
     * solo para que el extractor de PDF clasificara líneas de texto como
     * "no laborable" o "efeméride". Sin camino PDF, el diccionario no
     * tiene ningún uso.
     */
    public function up(): void
    {
        Schema::dropIfExists('calendario_palabras_clave');
    }

    public function down(): void
    {
        Schema::create('calendario_palabras_clave', function (Blueprint $table) {
            $table->id();
            $table->string('palabra', 100);
            $table->enum('categoria', ['no_laborable', 'efemeride']);
            $table->boolean('activa')->default(true);
            $table->foreignId('agregada_por')
                ->nullable()
                ->constrained('personas')
                ->nullOnDelete();
            $table->timestamps();
            $table->unique(['palabra', 'categoria']);
        });
    }
};