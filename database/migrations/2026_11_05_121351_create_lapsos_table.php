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
        Schema::create('lapsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->cascadeOnDelete();
            $table->foreignId('calendario_academico_id')->nullable()->constrained('calendario_academico')->nullOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->dateTime('fecha_limite_carga');
            $table->enum('estado', ['planificado', 'abierto', 'cerrado'])->default('planificado');
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrado_at')->nullable();
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapsos');
    }
};
