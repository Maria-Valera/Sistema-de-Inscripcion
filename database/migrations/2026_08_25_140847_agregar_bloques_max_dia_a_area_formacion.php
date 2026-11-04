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
        Schema::table('area_formacions', function (Blueprint $table) {
            $table->unsignedInteger('bloques_maximos_por_dia')->default(2)->after('horas_semanales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_formacions', function (Blueprint $table) {
            $table->dropColumn('bloques_maximos_por_dia');
        });
    }
};
