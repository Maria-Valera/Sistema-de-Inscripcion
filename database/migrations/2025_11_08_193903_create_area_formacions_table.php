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
        Schema::create('area_formacions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_area_formacion');
            $table->integer('codigo_area');
            $table->string('siglas');
            $table->integer('horas_semanales');
            $table->unsignedBigInteger('aula_id');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_formacions');
    }
};
