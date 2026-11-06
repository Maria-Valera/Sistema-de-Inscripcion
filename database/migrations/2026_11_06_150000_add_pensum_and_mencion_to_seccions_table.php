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
        Schema::table('seccions', function (Blueprint $table) {
            $table->foreignId('pensum_id')
                  ->nullable()
                  ->after('grado_id')
                  ->constrained('pensums')
                  ->cascadeOnDelete()
                  ->comment('Plan de estudio al que pertenece la sección');

            $table->foreignId('mencion_id')
                  ->nullable()
                  ->after('pensum_id')
                  ->constrained('menciones')
                  ->nullOnDelete()
                  ->comment('Mención asociada a la sección (null imprime *****)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seccions', function (Blueprint $table) {
            $table->dropForeign(['pensum_id']);
            $table->dropForeign(['mencion_id']);
            $table->dropColumn(['pensum_id', 'mencion_id']);
        });
    }
};

