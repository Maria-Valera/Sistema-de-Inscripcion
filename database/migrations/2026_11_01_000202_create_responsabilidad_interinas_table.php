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
        Schema::create('responsabilidad_interinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_nota_id')
                ->constrained('asignacion_notas')
                ->restrictOnDelete();
            $table->foreignId('usuario_interino_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('motivo', 300);
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->string('estado', 20)->default('activa');
            $table->foreignId('autorizada_por')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('autorizada_at');
            $table->timestamps();

            $table->index('asignacion_nota_id');
            $table->index('usuario_interino_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responsabilidad_interinas');
    }
};
