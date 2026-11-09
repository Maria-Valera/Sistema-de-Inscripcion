<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // 1. horario_asignaciones: eliminar dia_id y bloque_id (o hacer nullable en SQLite)
        if (Schema::hasTable('horario_asignaciones')) {
            if ($isSqlite) {
                Schema::table('horario_asignaciones', function (Blueprint $table) {
                    if (Schema::hasColumn('horario_asignaciones', 'dia_id')) {
                        $table->unsignedBigInteger('dia_id')->nullable()->change();
                    }
                    if (Schema::hasColumn('horario_asignaciones', 'bloque_id')) {
                        $table->unsignedBigInteger('bloque_id')->nullable()->change();
                    }
                });
            } else {
                try {
                    Schema::table('horario_asignaciones', function (Blueprint $table) {
                        $table->dropForeign('horario_asignaciones_dia_id_foreign');
                    });
                } catch (\Throwable $e) {}

                try {
                    Schema::table('horario_asignaciones', function (Blueprint $table) {
                        $table->dropForeign('horario_asignaciones_bloque_id_foreign');
                    });
                } catch (\Throwable $e) {}

                try {
                    Schema::table('horario_asignaciones', function (Blueprint $table) {
                        $table->dropIndex('horario_asignaciones_anio_escolar_id_dia_id_bloque_id_index');
                    });
                } catch (\Throwable $e) {}

                Schema::table('horario_asignaciones', function (Blueprint $table) {
                    $columnsToDrop = [];
                    if (Schema::hasColumn('horario_asignaciones', 'dia_id')) {
                        $columnsToDrop[] = 'dia_id';
                    }
                    if (Schema::hasColumn('horario_asignaciones', 'bloque_id')) {
                        $columnsToDrop[] = 'bloque_id';
                    }
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }

        // 2. docente_indisponibilidad: eliminar dias_semana_id e id_bloque_hora
        if (Schema::hasTable('docente_indisponibilidad')) {
            if ($isSqlite) {
                Schema::table('docente_indisponibilidad', function (Blueprint $table) {
                    if (Schema::hasColumn('docente_indisponibilidad', 'dias_semana_id')) {
                        $table->unsignedBigInteger('dias_semana_id')->nullable()->change();
                    }
                    if (Schema::hasColumn('docente_indisponibilidad', 'id_bloque_hora')) {
                        $table->unsignedBigInteger('id_bloque_hora')->nullable()->change();
                    }
                });
            } else {
                try {
                    Schema::table('docente_indisponibilidad', function (Blueprint $table) {
                        $table->dropForeign('_docente__no_disponibilidad_dias_semana_id_foreign');
                    });
                } catch (\Throwable $e) {}

                try {
                    Schema::table('docente_indisponibilidad', function (Blueprint $table) {
                        $table->dropForeign('_docente__no_disponibilidad_id_bloque_hora_foreign');
                    });
                } catch (\Throwable $e) {}

                Schema::table('docente_indisponibilidad', function (Blueprint $table) {
                    $columnsToDrop = [];
                    if (Schema::hasColumn('docente_indisponibilidad', 'dias_semana_id')) {
                        $columnsToDrop[] = 'dias_semana_id';
                    }
                    if (Schema::hasColumn('docente_indisponibilidad', 'id_bloque_hora')) {
                        $columnsToDrop[] = 'id_bloque_hora';
                    }
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }

        // 3. area_formacions: eliminar bloques_maximos_por_dia y dias_max_semana
        if (Schema::hasTable('area_formacions')) {
            Schema::table('area_formacions', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('area_formacions', 'bloques_maximos_por_dia')) {
                    $columnsToDrop[] = 'bloques_maximos_por_dia';
                }
                if (Schema::hasColumn('area_formacions', 'dias_max_semana')) {
                    $columnsToDrop[] = 'dias_max_semana';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        // 4. Eliminar tablas antiguas en orden por dependencias foráneas
        Schema::dropIfExists('horario__bloque_hora');
        Schema::dropIfExists('horario__dias_semana');
        Schema::dropIfExists('horario__docente__area__formacion');
        Schema::dropIfExists('horario__seccion');
        Schema::dropIfExists('horario');

        if (! $isSqlite) {
            Schema::dropIfExists('bloque_horarios');
            Schema::dropIfExists('dias_semana');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migración de fase "Contract": irreversible
    }
};
