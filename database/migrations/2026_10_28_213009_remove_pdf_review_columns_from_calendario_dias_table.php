<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina las columnas que solo servían para la revisión de candidatos
     * extraídos de un PDF:
     *  - confianza:       alta / dudosa / manual (sin PDF, todo es manual).
     *  - es_mes_completo: entradas "mes completo" del PDF, sin día puntual.
     *  - confirmado:      aprobado/no aprobado en la revisión (sin PDF, todo
     *                     evento queda registrado de una vez).
     *
     * El índice compuesto (calendario_id, confirmado, fecha) incluye
     * "confirmado", así que se reemplaza por (calendario_id, fecha).
     * La llave foránea de calendario_id sigue cubierta por el índice único
     * (calendario_id, fecha, texto_extraido).
     */
    public function up(): void
    {
        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->dropIndex(['calendario_id', 'confirmado', 'fecha']);
        });

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->dropColumn(['confianza', 'es_mes_completo', 'confirmado']);
        });

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->index(['calendario_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->dropIndex(['calendario_id', 'fecha']);
        });

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->enum('confianza', ['alta', 'dudosa', 'manual'])->default('manual');
            $table->boolean('es_mes_completo')->default(false);
            $table->boolean('confirmado')->default(true);
        });

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->index(['calendario_id', 'confirmado', 'fecha']);
        });
    }
};
