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
        $isSqlite = DB::getDriverName() === 'sqlite';
        $nowExpr = $isSqlite ? "DATETIME('now')" : "NOW()";

        // 2. Poblar grado_seccion desde seccions
        DB::statement("
            INSERT INTO grado_seccion (grado_id, seccion_id, status, created_at, updated_at)
            SELECT DISTINCT grado_id, id, 1, {$nowExpr}, {$nowExpr} FROM seccions
            WHERE NOT EXISTS (
                SELECT 1 FROM grado_seccion gs 
                WHERE gs.grado_id = seccions.grado_id AND gs.seccion_id = seccions.id
            )
        ");

        if ($isSqlite) {
            // En SQLite (usado en tests en memoria), Schema::dropColumn no puede recrear FKs eliminadas internamente
            // por lo que recreamos la tabla limpia con el nuevo esquema preservando los datos existentes
            Schema::create('cargas_academicas_nueva', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('calendario_academico_id');
                $table->unsignedBigInteger('docente_id');
                $table->string('tipo', 20)->default('area');
                $table->unsignedBigInteger('area_formacion_id')->nullable();
                $table->unsignedBigInteger('grado_seccion_id')->nullable();
                $table->unsignedBigInteger('grupo_estable_id')->nullable();
                $table->integer('area_key')->storedAs('COALESCE(area_formacion_id, 0)');
                $table->integer('grado_seccion_key')->storedAs('COALESCE(grado_seccion_id, 0)');
                $table->integer('grupo_key')->storedAs('COALESCE(grupo_estable_id, 0)');
                $table->unsignedBigInteger('docente_area_grado_id')->nullable();
                $table->boolean('activa')->default(true);
                $table->timestamp('sincronizada_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['calendario_academico_id', 'docente_id', 'area_key', 'grado_seccion_key', 'grupo_key'],
                    'uq_cargas_academicas_combinacion'
                );
                $table->unique(
                    ['calendario_academico_id', 'docente_area_grado_id'],
                    'uq_cargas_academicas_cal_dag'
                );
                $table->unique(['id', 'docente_id'], 'uq_cargas_academicas_id_docente');
                $table->unique(['id', 'grado_seccion_id'], 'uq_cargas_academicas_id_gs');
            });

            // Migrar datos existentes mapeando calendario_academico_id y grado_seccion_id
            DB::statement("
                INSERT INTO cargas_academicas_nueva (
                    id, calendario_academico_id, docente_id, tipo, area_formacion_id,
                    grado_seccion_id, grupo_estable_id, docente_area_grado_id, activa,
                    sincronizada_at, created_at, updated_at
                )
                SELECT 
                    ca.id,
                    (SELECT cal.id FROM calendario_academico cal WHERE cal.anio_escolar_id = ca.anio_escolar_id),
                    ca.docente_id,
                    'area',
                    ca.area_formacion_id,
                    (SELECT gs.id FROM grado_seccion gs INNER JOIN seccions s ON s.id = ca.seccion_id WHERE gs.grado_id = s.grado_id AND gs.seccion_id = s.id),
                    NULL,
                    NULL,
                    1,
                    NULL,
                    ca.created_at,
                    ca.updated_at
                FROM cargas_academicas ca
            ");

            Schema::drop('cargas_academicas');
            Schema::rename('cargas_academicas_nueva', 'cargas_academicas');
            return;
        }

        // ==========================================
        // Flujo MySQL / Producción (ALTER paso a paso)
        // ==========================================

        // 3 y 4. DROP UNIQUE uq_carga_academica y DROP FKs viejas de cargas_academicas
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
            $table->dropForeign(['area_formacion_id']);
            $table->dropForeign(['seccion_id']);
            $table->dropForeign(['anio_escolar_id']);
            $table->dropUnique('uq_carga_academica');
        });

        // 5. Agregar columnas nuevas (todas nullable inicialmente)
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->unsignedBigInteger('calendario_academico_id')->nullable()->after('id');
            $table->string('tipo', 20)->default('area')->after('docente_id');
            $table->unsignedBigInteger('grado_seccion_id')->nullable()->after('area_formacion_id');
            $table->unsignedBigInteger('grupo_estable_id')->nullable()->after('grado_seccion_id');
            $table->unsignedBigInteger('docente_area_grado_id')->nullable()->after('grupo_estable_id');
            $table->boolean('activa')->default(true)->after('docente_area_grado_id');
            $table->timestamp('sincronizada_at')->nullable()->after('activa');
        });

        // 6. Backfill grado_seccion_id
        DB::statement("
            UPDATE cargas_academicas ca
            INNER JOIN seccions s ON s.id = ca.seccion_id
            INNER JOIN grado_seccion gs ON gs.grado_id = s.grado_id AND gs.seccion_id = s.id
            SET ca.grado_seccion_id = gs.id
        ");

        // 7. Backfill calendario_academico_id
        DB::statement("
            UPDATE cargas_academicas ca
            INNER JOIN calendario_academico cal ON cal.anio_escolar_id = ca.anio_escolar_id
            SET ca.calendario_academico_id = cal.id
        ");

        // 8. VALIDAR (obligatorio): no omitir. Si > 0, ABORTAR con RuntimeException.
        $huerfanos = DB::table('cargas_academicas')
            ->whereNull('grado_seccion_id')
            ->orWhereNull('calendario_academico_id')
            ->select('id', 'anio_escolar_id', 'seccion_id', 'grado_seccion_id', 'calendario_academico_id')
            ->get();

        if ($huerfanos->count() > 0) {
            $detalles = $huerfanos->map(function ($row) {
                return "Carga ID: {$row->id} (anio_escolar_id: {$row->anio_escolar_id}, seccion_id: {$row->seccion_id})";
            })->implode(', ');

            throw new \RuntimeException("Fallo de migración: Existen registros en cargas_academicas huérfanos sin correspondencia en grado_seccion o calendario_academico: {$detalles}");
        }

        // 9. DROP COLUMNAS viejas
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->dropColumn(['seccion_id', 'anio_escolar_id']);
        });

        // 10 y 11. Modificar calendario_academico_id a NOT NULL, area_formacion_id a nullable
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->unsignedBigInteger('calendario_academico_id')->nullable(false)->change();
            $table->unsignedBigInteger('area_formacion_id')->nullable()->change();
        });

        // 12. Agregar columnas generadas STORED
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->integer('area_key')->storedAs('IFNULL(area_formacion_id, 0)')->after('grupo_estable_id');
            $table->integer('grado_seccion_key')->storedAs('IFNULL(grado_seccion_id, 0)')->after('area_key');
            $table->integer('grupo_key')->storedAs('IFNULL(grupo_estable_id, 0)')->after('grado_seccion_key');
        });

        // 13. CHECKs con DB::statement
        DB::statement("
            ALTER TABLE cargas_academicas 
            ADD CONSTRAINT chk_cargas_academicas_tipo_area 
            CHECK (tipo <> 'area' OR (area_formacion_id IS NOT NULL AND grado_seccion_id IS NOT NULL AND grupo_estable_id IS NULL))
        ");

        DB::statement("
            ALTER TABLE cargas_academicas 
            ADD CONSTRAINT chk_cargas_academicas_tipo_grupo 
            CHECK (tipo <> 'grupo_estable' OR grupo_estable_id IS NOT NULL)
        ");

        // 14 y 15. FKs nuevas con RESTRICT e Índices únicos nuevos
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->foreign('calendario_academico_id')
                ->references('id')
                ->on('calendario_academico')
                ->restrictOnDelete();

            $table->foreign('docente_id')
                ->references('id')
                ->on('docentes')
                ->restrictOnDelete();

            $table->foreign('area_formacion_id')
                ->references('id')
                ->on('area_formacions')
                ->restrictOnDelete();

            $table->foreign('grado_seccion_id')
                ->references('id')
                ->on('grado_seccion')
                ->restrictOnDelete();

            $table->foreign('grupo_estable_id')
                ->references('id')
                ->on('grupo_estables')
                ->restrictOnDelete();

            $table->foreign('docente_area_grado_id')
                ->references('id')
                ->on('docente_area_grados')
                ->restrictOnDelete();

            $table->unique(
                ['calendario_academico_id', 'docente_id', 'area_key', 'grado_seccion_key', 'grupo_key'],
                'uq_cargas_academicas_combinacion'
            );

            $table->unique(
                ['calendario_academico_id', 'docente_area_grado_id'],
                'uq_cargas_academicas_cal_dag'
            );

            $table->unique(['id', 'docente_id'], 'uq_cargas_academicas_id_docente');
            $table->unique(['id', 'grado_seccion_id'], 'uq_cargas_academicas_id_gs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        if ($isSqlite) {
            Schema::create('cargas_academicas_antigua', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('anio_escolar_id');
                $table->unsignedBigInteger('docente_id');
                $table->unsignedBigInteger('area_formacion_id');
                $table->unsignedBigInteger('seccion_id');
                $table->timestamps();

                $table->unique(
                    ['anio_escolar_id', 'docente_id', 'area_formacion_id', 'seccion_id'],
                    'uq_carga_academica'
                );
            });

            DB::statement("
                INSERT INTO cargas_academicas_antigua (
                    id, anio_escolar_id, docente_id, area_formacion_id, seccion_id, created_at, updated_at
                )
                SELECT 
                    ca.id,
                    (SELECT cal.anio_escolar_id FROM calendario_academico cal WHERE cal.id = ca.calendario_academico_id),
                    ca.docente_id,
                    COALESCE(ca.area_formacion_id, 0),
                    (SELECT gs.seccion_id FROM grado_seccion gs WHERE gs.id = ca.grado_seccion_id),
                    ca.created_at,
                    ca.updated_at
                FROM cargas_academicas ca
            ");

            Schema::drop('cargas_academicas');
            Schema::rename('cargas_academicas_antigua', 'cargas_academicas');
            return;
        }

        // 1. Dropear FKs primero y luego los Índices únicos nuevos
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->dropForeign(['calendario_academico_id']);
            $table->dropForeign(['docente_id']);
            $table->dropForeign(['area_formacion_id']);
            $table->dropForeign(['grado_seccion_id']);
            $table->dropForeign(['grupo_estable_id']);
            $table->dropForeign(['docente_area_grado_id']);

            $table->dropUnique('uq_cargas_academicas_combinacion');
            $table->dropUnique('uq_cargas_academicas_cal_dag');
            $table->dropUnique('uq_cargas_academicas_id_docente');
            $table->dropUnique('uq_cargas_academicas_id_gs');
        });

        // 2. Dropear CHECKs
        DB::statement("ALTER TABLE cargas_academicas DROP CHECK chk_cargas_academicas_tipo_area");
        DB::statement("ALTER TABLE cargas_academicas DROP CHECK chk_cargas_academicas_tipo_grupo");

        // 3. Dropear columnas generadas
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->dropColumn(['area_key', 'grado_seccion_key', 'grupo_key']);
        });

        // 4. Recrear anio_escolar_id y seccion_id como nullables temporalmente
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->unsignedBigInteger('anio_escolar_id')->nullable()->after('id');
            $table->unsignedBigInteger('seccion_id')->nullable()->after('docente_id');
        });

        // 5. Backfill anio_escolar_id y seccion_id
        DB::statement("
            UPDATE cargas_academicas ca
            INNER JOIN calendario_academico cal ON cal.id = ca.calendario_academico_id
            SET ca.anio_escolar_id = cal.anio_escolar_id
        ");

        DB::statement("
            UPDATE cargas_academicas ca
            INNER JOIN grado_seccion gs ON gs.id = ca.grado_seccion_id
            SET ca.seccion_id = gs.seccion_id
        ");

        // 6. Eliminar columnas nuevas de TS0
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->dropColumn([
                'calendario_academico_id',
                'tipo',
                'grado_seccion_id',
                'grupo_estable_id',
                'docente_area_grado_id',
                'activa',
                'sincronizada_at',
            ]);
        });

        // 7. Restaurar NOT NULL en seccion_id, anio_escolar_id, area_formacion_id
        Schema::table('cargas_academicas', function (Blueprint $table) {
            $table->unsignedBigInteger('anio_escolar_id')->nullable(false)->change();
            $table->unsignedBigInteger('seccion_id')->nullable(false)->change();
            $table->unsignedBigInteger('area_formacion_id')->nullable(false)->change();

            // Recrear FKs viejas cascade y unique uq_carga_academica
            $table->foreign('anio_escolar_id')->references('id')->on('anio_escolars')->cascadeOnDelete();
            $table->foreign('docente_id')->references('id')->on('docentes')->cascadeOnDelete();
            $table->foreign('area_formacion_id')->references('id')->on('area_formacions')->cascadeOnDelete();
            $table->foreign('seccion_id')->references('id')->on('seccions')->cascadeOnDelete();

            $table->unique(
                ['anio_escolar_id', 'docente_id', 'area_formacion_id', 'seccion_id'],
                'uq_carga_academica'
            );
        });
    }
};
