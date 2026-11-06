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
        Schema::create('menciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique()->comment('Código oficial de mención MPPE (ej. 01)');
            $table->string('nombre', 100)->comment('Nombre de la mención (ej. Educación Media General, Ciencias)');
            $table->string('ciclo', 20)->default('50')->comment('Código de ciclo del MPPE (ej. 50)');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menciones');
    }
};

