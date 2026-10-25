<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - mes_pagina: solo servía para agrupar la antigua pestaña "Lista" y
     *   para el orden de la extracción de PDF. Ya nada lo lee.
     * - categoria 'dudoso': solo lo generaba el clasificador de PDF. Se
     *   quita del enum de MySQL, dejando únicamente no_laborable/laborable.
     *
     * Si quedaran filas viejas con 'dudoso' (restos de pruebas con PDF),
     * se pasan a 'laborable' antes de cambiar el enum: es lo seguro, porque
     * un día dudoso nunca llegó a bloquear clases ni trabajo.
     */
    public function up(): void
    {
        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->dropColumn('mes_pagina');
        });

        DB::table('calendario_dias')
            ->where('categoria', 'dudoso')
            ->update(['categoria' => 'laborable']);

        DB::statement("ALTER TABLE calendario_dias MODIFY categoria ENUM('no_laborable', 'laborable') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE calendario_dias MODIFY categoria ENUM('no_laborable', 'dudoso', 'laborable') NOT NULL");

        Schema::table('calendario_dias', function (Blueprint $table) {
            $table->string('mes_pagina', 20)->nullable();
        });
    }
};