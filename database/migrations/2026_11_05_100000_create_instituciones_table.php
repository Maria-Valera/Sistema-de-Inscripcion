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
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_plantel', 50)->unique();
            $table->string('nombre', 255);
            $table->string('direccion', 500)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->foreignId('localidad_id')->nullable()->constrained('localidads')->nullOnDelete();
            $table->string('zona_educativa', 100)->nullable();
            $table->foreignId('director_persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->boolean('director_encargado')->default(false);
            $table->string('logo_ruta', 255)->nullable();
            $table->string('cdcee', 100)->nullable();
            $table->string('dependencia', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instituciones');
    }
};

