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
        Schema::create('carga_lapsos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_nota_id')
                ->constrained('asignacion_notas')
                ->restrictOnDelete();
            $table->foreignId('lapso_id')
                ->constrained('lapsos')
                ->restrictOnDelete();
            $table->string('estado', 20)->default('planificacion');
            $table->timestamp('entregada_at')->nullable();
            $table->foreignId('cerrada_por')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();

            $table->unique(['asignacion_nota_id', 'lapso_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_lapsos');
    }
};
