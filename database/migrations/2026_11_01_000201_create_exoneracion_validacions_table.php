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
        Schema::create('exoneracion_validacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exoneracion_id')
                ->constrained('exoneracions')
                ->restrictOnDelete();
            $table->tinyInteger('orden');
            $table->foreignId('validado_por')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('decision', 20);
            $table->string('comentario', 500)->nullable();
            $table->timestamp('validated_at');
            $table->timestamps();

            $table->unique(['exoneracion_id', 'orden'], 'exoneracion_validacions_exoneracion_id_orden_unique');
            $table->unique(['exoneracion_id', 'validado_por'], 'exoneracion_validacions_exoneracion_id_validado_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exoneracion_validacions');
    }
};
