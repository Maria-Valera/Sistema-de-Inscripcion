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
        Schema::create('horario__bloque_hora', function (Blueprint $table) {
            $table->id("id_horario_bloque_hora");
            $table->unsignedBigInteger('horario_id');
            $table->foreign('horario_id')->references('id')->on('horario')->onDelete('cascade');
            $table->unsignedBigInteger('bloque_hora_id');
            $table->foreign('bloque_hora_id')->references('id')->on('bloque_horarios')->onDelete('cascade');
            $table->timestamps();
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario__bloque_hora');
    }
};
