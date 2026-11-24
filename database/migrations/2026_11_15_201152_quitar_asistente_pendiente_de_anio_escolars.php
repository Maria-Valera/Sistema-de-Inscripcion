<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::table('anio_escolars', function (Blueprint $table) {
            $table->dropColumn('asistente_pendiente');
        });
    }

    public function down(): void
    {
        Schema::table('anio_escolars', function (Blueprint $table) {
            $table->boolean('asistente_pendiente')->default(false)->after('status');
        });
    }
};
