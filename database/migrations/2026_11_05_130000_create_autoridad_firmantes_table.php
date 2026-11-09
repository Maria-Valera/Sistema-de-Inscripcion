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
        Schema::create('autoridad_firmantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->cascadeOnDelete();
            $table->enum('cargo', [
                'director',
                'subdirector_academico',
                'coordinador_control_estudios',
                'coordinador_evaluacion',
                'docente_firmante_mppe',
            ]);
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->boolean('encargado')->default(false);
            $table->string('gaceta', 100)->nullable();
            $table->date('desde');
            $table->date('hasta')->nullable();
            $table->timestamps();

            $table->index(['anio_escolar_id', 'cargo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autoridad_firmantes');
    }
};

