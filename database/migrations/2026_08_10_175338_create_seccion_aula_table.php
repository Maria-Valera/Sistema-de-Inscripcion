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
        Schema::create('seccion_aula', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_aula')->references('id_aula')->on('aula')->onDelete('cascade');
            $table->unsignedBigInteger('id_seccion')->references('id')->on('seccions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seccion_aula');
    }
};
