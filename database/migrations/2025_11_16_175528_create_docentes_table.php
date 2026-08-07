<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->nullable()
                  ->constrained('anio_escolars')
                  ->nullOnDelete();
            $table->string('primer_telefono')->nullable(); 
            $table->string('codigo')->nullable();
            $table->string('dependencia')->nullable();
            $table->string('horas_academicas')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};