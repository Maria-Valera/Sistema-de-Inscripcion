<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (DB::getDriverName() === 'sqlite') {
            Schema::dropIfExists('horario_asignaciones');
            Schema::create('horario_asignaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('anio_escolar_id')->constrained('anio_escolars')->onDelete('cascade');
                $table->foreignId('carga_academica_id')->constrained('cargas_academicas')->onDelete('cascade');
                $table->foreignId('aula_id')->nullable()->constrained('aulas', 'id_aula')->nullOnDelete();
                $table->foreignId('dia_id')->nullable()->constrained('dias_semana')->nullOnDelete();
                $table->foreignId('bloque_id')->nullable()->constrained('bloque_horarios')->nullOnDelete();
                $table->tinyInteger('dia')->nullable();
                $table->tinyInteger('bloque')->nullable();
                $table->boolean('fijada')->default(false);
                $table->boolean('conflicto_manual')->default(false);
                $table->text('motivo_conflicto')->nullable();
                $table->timestamps();

                $table->index(['anio_escolar_id', 'dia_id', 'bloque_id']);
                $table->index(['anio_escolar_id', 'dia', 'bloque']);
            });
        } else {
            // 1. Truncar tabla de pruebas
            DB::table('horario_asignaciones')->truncate();

            // 2. Eliminar índices y llaves foráneas antiguas
            Schema::table('horario_asignaciones', function (Blueprint $table) {
                // Foráneas
                $table->dropForeign(['docente_id']);
                $table->dropForeign(['materia_id']);
                $table->dropForeign(['seccion_id']);

                // Índices compuestos
                $table->dropIndex(['anio_escolar_id', 'docente_id']);
                $table->dropIndex(['anio_escolar_id', 'seccion_id']);
                $table->dropIndex(['docente_id', 'dia_id', 'bloque_id']);
                $table->dropIndex(['seccion_id', 'dia_id', 'bloque_id']);
            });

            // 3. Eliminar columnas redundantes
            Schema::table('horario_asignaciones', function (Blueprint $table) {
                $columnas = ['docente_id', 'materia_id', 'seccion_id'];
                if (Schema::hasColumn('horario_asignaciones', 'slot_docente')) {
                    $columnas[] = 'slot_docente';
                }
                if (Schema::hasColumn('horario_asignaciones', 'slot_seccion')) {
                    $columnas[] = 'slot_seccion';
                }
                $table->dropColumn($columnas);
            });

            // 4. Agregar carga_academica_id como FK cascade
            Schema::table('horario_asignaciones', function (Blueprint $table) {
                $table->foreignId('carga_academica_id')
                    ->after('anio_escolar_id')
                    ->constrained('cargas_academicas')
                    ->onDelete('cascade');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (DB::getDriverName() === 'sqlite') {
            Schema::dropIfExists('horario_asignaciones');
        } else {
            Schema::table('horario_asignaciones', function (Blueprint $table) {
                $table->dropForeign(['carga_academica_id']);
                $table->dropColumn('carga_academica_id');

                $table->foreignId('docente_id')->nullable()->constrained('docentes')->onDelete('cascade');
                $table->foreignId('materia_id')->nullable()->constrained('area_formacions')->onDelete('cascade');
                $table->foreignId('seccion_id')->nullable()->constrained('seccions')->onDelete('cascade');
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
