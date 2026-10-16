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
        Schema::create('cortes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapso_id')
                ->constrained('lapsos');
            $table->tinyInteger('numero');
            $table->string('nombre', 50);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->dateTime('fecha_limite_carga');
            $table->decimal('ponderacion', 5, 2)->nullable();
            $table->string('estado', 20)->default('planificado');
            $table->foreignId('abierto_por')
                ->nullable()
                ->constrained('users');
            $table->timestamp('abierto_at')->nullable();
            $table->foreignId('cerrado_por')
                ->nullable()
                ->constrained('users');
            $table->timestamp('cerrado_at')->nullable();
            $table->timestamps();

            $table->unique(['lapso_id', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes');
    }
};
