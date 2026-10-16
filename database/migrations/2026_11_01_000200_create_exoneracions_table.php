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
        Schema::create('exoneracions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapso_id')
                ->constrained('lapsos')
                ->restrictOnDelete();
            $table->string('alcance', 30);
            $table->foreignId('inscripcion_id')
                ->nullable()
                ->constrained('inscripcions')
                ->restrictOnDelete();
            $table->foreignId('seccion_id')
                ->nullable()
                ->constrained('seccions')
                ->restrictOnDelete();
            $table->foreignId('grupo_estable_id')
                ->nullable()
                ->constrained('grupo_estables')
                ->restrictOnDelete();
            $table->foreignId('area_formacion_id')
                ->nullable()
                ->constrained('area_formacions')
                ->restrictOnDelete();
            $table->text('motivo');
            $table->decimal('nota_otorgada', 5, 2)->nullable();
            $table->string('estado', 25)->default('solicitada');
            $table->tinyInteger('validaciones_requeridas')->default(1);
            $table->foreignId('solicitada_por')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('aplicada_at')->nullable();
            $table->timestamps();

            $table->index(['lapso_id', 'alcance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exoneracions');
    }
};
