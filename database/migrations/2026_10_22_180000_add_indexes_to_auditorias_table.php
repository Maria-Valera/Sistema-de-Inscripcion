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
        Schema::table('auditorias', function (Blueprint $table) {
            $table->index('created_at', 'auditorias_created_at_index');
            // accion ya tiene index en create_auditorias_table, pero agregamos índice compuesto si no existe o verificamos
            // La instrucción pide: created_at, accion, auditable_type
            $table->index('auditable_type', 'auditorias_auditable_type_index');
            $table->index(['auditable_type', 'auditable_id'], 'auditorias_auditable_type_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropIndex('auditorias_created_at_index');
            $table->dropIndex('auditorias_auditable_type_index');
            $table->dropIndex('auditorias_auditable_type_id_index');
        });
    }
};
