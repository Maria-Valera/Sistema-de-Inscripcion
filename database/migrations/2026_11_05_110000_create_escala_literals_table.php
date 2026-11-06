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
        Schema::create('escala_literals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->cascadeOnDelete();
            $table->char('letra', 1);
            $table->string('descripcion', 60)->nullable();
            $table->unsignedTinyInteger('nota_desde');
            $table->unsignedTinyInteger('nota_hasta');
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'letra']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escala_literals');
    }
};

