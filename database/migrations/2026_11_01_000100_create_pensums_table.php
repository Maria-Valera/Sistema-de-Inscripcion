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
        Schema::create('pensums', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('resolucion', 150)->nullable();
            $table->foreignId('anio_escolar_inicio_id')
                ->constrained('anio_escolars');
            $table->foreignId('anio_escolar_fin_id')
                ->nullable()
                ->constrained('anio_escolars');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pensums');
    }
};
