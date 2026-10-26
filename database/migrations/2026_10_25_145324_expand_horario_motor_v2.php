<?php

use App\Enums\Dia;
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
        // ---------------------------------------------------------------------
        // 1. CREACIÓN DE NUEVAS TABLAS Y COLUMNAS
        // ---------------------------------------------------------------------

        // a) configuracion_horario
        Schema::create('configuracion_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->unique()->constrained('anio_escolars')->onDelete('cascade');
            $table->time('hora_inicio');
            $table->tinyInteger('numero_bloques');
            $table->smallInteger('duracion_bloque_min');
            $table->smallInteger('minutos_entre_bloques')->default(0);
            $table->timestamps();
        });

        // b) receso_horario
        Schema::create('receso_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuracion_horario_id')->constrained('configuracion_horario')->onDelete('cascade');
            $table->tinyInteger('despues_de_bloque');
            $table->smallInteger('duracion_min');
            $table->timestamps();

            $table->unique(['configuracion_horario_id', 'despues_de_bloque']);
        });

        // c) bloque_reservados
        Schema::create('bloque_reservados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->onDelete('cascade');
            $table->tinyInteger('dia');
            $table->tinyInteger('bloque');
            $table->timestamps();

            $table->unique(['anio_escolar_id', 'dia', 'bloque']);
        });

        // d) horario_asignaciones: agregar dia, bloque, fijada; cambiar aula_id a nullable con nullOnDelete
        Schema::table('horario_asignaciones', function (Blueprint $table) {
            $table->tinyInteger('dia')->nullable()->after('aula_id');
            $table->tinyInteger('bloque')->nullable()->after('dia');
            $table->boolean('fijada')->default(false)->after('motivo_conflicto');
        });

        Schema::table('horario_asignaciones', function (Blueprint $table) {
            $table->dropForeign(['aula_id']);
        });

        Schema::table('horario_asignaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('aula_id')->nullable()->change();
            $table->foreign('aula_id')->references('id_aula')->on('aulas')->nullOnDelete();
        });

        // e) Renombrar _docente__no_disponibilidad a docente_indisponibilidad y agregar columnas
        Schema::rename('_docente__no_disponibilidad', 'docente_indisponibilidad');

        Schema::table('docente_indisponibilidad', function (Blueprint $table) {
            $table->tinyInteger('dia')->nullable()->after('id_bloque_hora');
            $table->tinyInteger('bloque')->nullable()->after('dia');
            $table->string('tipo', 20)->default('prohibido')->after('bloque');
            $table->string('estado', 20)->default('aprobada')->after('tipo');
        });

        // f) area_formacions: renombrar horas_semanales a bloques_semanales
        Schema::table('area_formacions', function (Blueprint $table) {
            $table->renameColumn('horas_semanales', 'bloques_semanales');
        });

        Schema::table('area_formacions', function (Blueprint $table) {
            $table->integer('bloques_semanales')->nullable()->change();
        });

        // ---------------------------------------------------------------------
        // 2. MIGRACIÓN DE DATOS
        // ---------------------------------------------------------------------
        $diasMap = DB::table('dias_semana')->pluck('nombre_dia', 'id');

        // Migración de horario_asignaciones
        $asignaciones = DB::table('horario_asignaciones')->get();
        foreach ($asignaciones as $asignacion) {
            $nombreDia = $diasMap[$asignacion->dia_id] ?? null;
            $diaEnum = $nombreDia ? Dia::fromNombre($nombreDia) : null;
            if (!$diaEnum) {
                throw new \RuntimeException("No se pudo mapear dia_id {$asignacion->dia_id} en horario_asignaciones id {$asignacion->id}");
            }

            $nuevoBloque = (int) ceil($asignacion->bloque_id / 2);

            DB::table('horario_asignaciones')
                ->where('id', $asignacion->id)
                ->update([
                    'dia' => $diaEnum->value,
                    'bloque' => $nuevoBloque,
                ]);
        }

        // Migración de docente_indisponibilidad
        $indisponibilidades = DB::table('docente_indisponibilidad')->get();
        foreach ($indisponibilidades as $indisp) {
            $nombreDia = $diasMap[$indisp->dias_semana_id] ?? null;
            $diaEnum = $nombreDia ? Dia::fromNombre($nombreDia) : null;
            if (!$diaEnum) {
                throw new \RuntimeException("No se pudo mapear dias_semana_id {$indisp->dias_semana_id} en docente_indisponibilidad id {$indisp->id}");
            }

            $nuevoBloque = (int) ceil($indisp->id_bloque_hora / 2);

            DB::table('docente_indisponibilidad')
                ->where('id', $indisp->id)
                ->update([
                    'dia' => $diaEnum->value,
                    'bloque' => $nuevoBloque,
                ]);
        }

        // Registro inicial en configuracion_horario para el año escolar activo
        $anioActivo = DB::table('anio_escolars')
            ->whereIn('status', ['Activo', 'Extendido'])
            ->orderByDesc('id')
            ->first();

        if ($anioActivo) {
            DB::table('configuracion_horario')->updateOrInsert(
                ['anio_escolar_id' => $anioActivo->id],
                [
                    'hora_inicio' => '07:00:00',
                    'numero_bloques' => 4,
                    'duracion_bloque_min' => 90,
                    'minutos_entre_bloques' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Eliminar tablas nuevas en orden de dependencia
        Schema::dropIfExists('bloque_reservados');
        Schema::dropIfExists('receso_horario');
        Schema::dropIfExists('configuracion_horario');

        // 2. Revertir columnas y FK de horario_asignaciones
        if (Schema::hasTable('horario_asignaciones')) {
            Schema::table('horario_asignaciones', function (Blueprint $table) {
                $table->dropForeign(['aula_id']);
            });

            Schema::table('horario_asignaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('aula_id')->nullable(false)->change();
                $table->foreign('aula_id')->references('id_aula')->on('aulas')->onDelete('cascade');
                $table->dropColumn(['dia', 'bloque', 'fijada']);
            });
        }

        // 3. Revertir cambios en docente_indisponibilidad y renombrar a _docente__no_disponibilidad
        if (Schema::hasTable('docente_indisponibilidad')) {
            Schema::table('docente_indisponibilidad', function (Blueprint $table) {
                $table->dropColumn(['dia', 'bloque', 'tipo', 'estado']);
            });

            Schema::rename('docente_indisponibilidad', '_docente__no_disponibilidad');
        }

        // 4. Revertir renombre de columna en area_formacions
        if (Schema::hasTable('area_formacions') && Schema::hasColumn('area_formacions', 'bloques_semanales')) {
            Schema::table('area_formacions', function (Blueprint $table) {
                $table->renameColumn('bloques_semanales', 'horas_semanales');
            });
        }
    }
};
