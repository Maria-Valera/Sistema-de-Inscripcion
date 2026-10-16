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
            $table->foreignId('anio_escolar_id')
                ->constrained('anio_escolars');
            $table->tinyInteger('numero');
            $table->string('nombre', 50);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->dateTime('fecha_limite_carga');
            $table->string('estado', 20)->default('planificado');
            $table->foreignId('cerrado_por')
                ->nullable()
                ->constrained('users');
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
