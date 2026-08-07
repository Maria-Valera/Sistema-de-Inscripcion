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
        Schema::create('horario__seccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('horario_id');
            $table->foreign('horario_id')->references('id')->on('horario')->onDelete('cascade');
            $table->unsignedBigInteger('seccion_id');
            $table->foreign('seccion_id')->references('id')->on('seccions')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario__seccion');
    }
};
