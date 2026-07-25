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
        Schema::create('horario__dias_semana', function (Blueprint $table) {
            $table->id("id_horario_dias_semana");
            $table->unsignedBigInteger('horario_id');
            $table->foreign('horario_id')->references('id')->on('horario')->onDelete('cascade');
            $table->unsignedBigInteger('dias_semana_id');
            $table->foreign('dias_semana_id')->references('id')->on('dias_semana')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario__dias_semana');
    }
};
