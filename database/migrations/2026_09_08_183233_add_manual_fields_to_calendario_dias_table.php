<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calendario_dias', function (Blueprint $table) {
            //este campo es independiente de "categoria" : ya que un dia puede ser ser efemeride y laborable,pero tambien ser efemeride y NO LABORABLE (como los dias nacionales)
            $table->boolean('es_efemeride')->default(false)->after('categoria');

            // en este campo se guarda la clave de color,no el color en si,con el fin de poder ajustar el color en un solo lugar
            // (el enum ColorEvento) sin tocar filas
            $table->string('color',30)->nullable()->after('es_efemeride');
        });

        // "categoria" es un enum de mysql de valores tipicos. en la ruta del pdf solo se genera "no laborable" o "dudoso" que requiere revision humano
    // mientras tanto en el camino manual tiene que tener "laborable" como opcion tambien
    DB::statement("ALTER TABLE calendario_dias MODIFY categoria ENUM('no_laborable', 'dudoso','laborable') NOT NULL");
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendario_dias', function (Blueprint $table) {
            //
            $table->dropColumn(['es_efemeride','color']);
        });

        DB::statement("ALTER TABLE calendario_dias MODIFY categoria ENUM('no_laborable','dudoso') NOT NULL");
    }
};
