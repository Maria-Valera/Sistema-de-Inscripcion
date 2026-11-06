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
            $table->string('codigo', 5)->unique()->comment('Código oficial de 5 dígitos del MPPE (ej. 31059, 31060)');
            $table->string('nombre', 150)->comment('Nombre del plan de estudio');
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('anio_inicio')->comment('Año de inicio de vigencia');
            $table->unsignedSmallInteger('anio_fin')->nullable()->comment('Año de fin de vigencia (null = vigente)');
            $table->boolean('status')->default(true);
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

