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
            $table->unsignedInteger('dias_max_semana')->nullable()->after('bloques_maximos_por_dia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_formacions', function (Blueprint $table) {
            $table->dropColumn('dias_max_semana');
        });
    }
};
