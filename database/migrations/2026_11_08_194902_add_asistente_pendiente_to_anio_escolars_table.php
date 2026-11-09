<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Asistente de año nuevo (María): marca el año creado desde la pantalla de seleccionar año
// mientras le falten sus lapsos o su calendario. Al terminar el asistente vuelve a false.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anio_escolars', function (Blueprint $table) {
            $table->boolean('asistente_pendiente')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('anio_escolars', function (Blueprint $table) {
            $table->dropColumn('asistente_pendiente');
        });
    }
};
