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
        Schema::create('evaluacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_nota_id')
                ->constrained('asignacion_notas')
                ->restrictOnDelete();
            $table->foreignId('corte_id')
                ->constrained('cortes')
                ->restrictOnDelete();
            $table->foreignId('tipo_evaluacion_id')
                ->constrained('tipo_evaluacions')
                ->restrictOnDelete();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->date('fecha_evaluacion');
            $table->decimal('ponderacion', 5, 2);
            $table->boolean('activa')->default(true);
            $table->foreignId('creado_por')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index(['asignacion_nota_id', 'corte_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacions');
    }
};
