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
            $table->dropForeign(['aula_id']);
            $table->unsignedBigInteger('aula_id')->nullable()->change();
            $table->foreign('aula_id')->references('id_aula')->on('aulas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_formacions', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
            $table->unsignedBigInteger('aula_id')->nullable(false)->change();
            $table->foreign('aula_id')->references('id_aula')->on('aulas')->onDelete('restrict');
        });
    }
};
