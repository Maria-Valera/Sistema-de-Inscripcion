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
        Schema::create('grado_seccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')
                ->constrained('grados')
                ->restrictOnDelete();
            $table->foreignId('seccion_id')
                ->constrained('seccions')
                ->restrictOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['grado_id', 'seccion_id'], 'uq_grado_seccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grado_seccion');
    }
};
