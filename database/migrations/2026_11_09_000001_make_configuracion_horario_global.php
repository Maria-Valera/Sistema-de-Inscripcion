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
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
            Schema::create('configuracion_horario_temp', function (Blueprint $table) {
                $table->id();
                $table->time('hora_inicio');
                $table->tinyInteger('numero_bloques');
                $table->smallInteger('duracion_bloque_min');
                $table->smallInteger('minutos_entre_bloques')->default(0);
                $table->timestamps();
            });
            DB::statement('INSERT INTO configuracion_horario_temp (id, hora_inicio, numero_bloques, duracion_bloque_min, minutos_entre_bloques, created_at, updated_at) SELECT id, hora_inicio, numero_bloques, duracion_bloque_min, minutos_entre_bloques, created_at, updated_at FROM configuracion_horario');
            Schema::drop('configuracion_horario');
            Schema::rename('configuracion_horario_temp', 'configuracion_horario');
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            Schema::table('configuracion_horario', function (Blueprint $table) {
                $table->dropForeign(['anio_escolar_id']);
                $table->dropUnique(['anio_escolar_id']);
                $table->dropColumn('anio_escolar_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_horario', function (Blueprint $table) {
            $table->foreignId('anio_escolar_id')->nullable()->constrained('anio_escolars')->nullOnDelete();
            if (DB::getDriverName() !== 'sqlite') {
                $table->unique('anio_escolar_id');
            }
        });
    }
};
