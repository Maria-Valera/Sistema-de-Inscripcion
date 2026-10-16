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
        Schema::create('discusion_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapso_id')
                ->constrained('lapsos');
            $table->foreignId('seccion_id')
                ->constrained('seccions');
            $table->date('fecha_discusion')->nullable();
            $table->string('estado', 20)->default('programada');
            $table->text('acta')->nullable();
            $table->foreignId('registrada_por')
                ->nullable()
                ->constrained('users');
            $table->timestamp('cerrada_at')->nullable();
            $table->timestamps();

            $table->unique(['lapso_id', 'seccion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discusion_notas');
    }
};
