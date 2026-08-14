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
        Schema::table('_docente__no_disponibilidad', function (Blueprint $table) {
            $table->foreignId('anio_escolar_id')->after('docente_id')->constrained('anio_escolars')->onDelete('cascade');
            $table->string('motivo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_docente__no_disponibilidad', function (Blueprint $table) {
            $table->dropForeign(['anio_escolar_id']);
            $table->dropColumn('anio_escolar_id');
            $table->string('motivo')->nullable(false)->change();
        });
    }
};
