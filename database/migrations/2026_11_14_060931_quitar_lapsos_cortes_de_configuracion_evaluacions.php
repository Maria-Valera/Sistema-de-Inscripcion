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
        Schema::table('configuracion_evaluacions', function (Blueprint $table) {
            $table->dropColumn(['num_cortes', 'puntos_corte']);
            $table->renameColumn('num_lapsos', 'max_lapsos');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_evaluacions', function (Blueprint $table) {
            $table->renameColumn('max_lapsos', 'num_lapsos');
            $table->unsignedTinyInteger('num_cortes')->default(4)->after('regla_redondeo');
            $table->unsignedTinyInteger('puntos_corte')->default(20)->after('num_cortes');
        });
    }
};
