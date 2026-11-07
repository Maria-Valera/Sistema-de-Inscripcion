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
            $table->foreignId('lapso_id')->constrained('lapsos')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->string('nombre', 50);
            $table->decimal('peso_lapso', 5, 2)->default(5);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->dateTime('fecha_limite_carga');
            $table->enum('estado', ['planificado', 'abierto', 'cerrado'])->default('planificado');
            $table->foreignId('abierto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('abierto_at')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
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
