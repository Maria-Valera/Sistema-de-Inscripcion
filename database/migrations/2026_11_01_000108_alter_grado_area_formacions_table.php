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
        // 1. Crear en pensums una fila «Plan vigente (migrado)» usando el año escolar más antiguo existente.
        // Documentación: Si no existe ningún año escolar registrado en la base de datos, no es posible
        // insertar el pensum por la restricción de clave foránea anio_escolar_inicio_id NOT NULL.
        // En ese escenario, las columnas se agregan como nullable para dejar la migración lista.
        $anioMasAntiguo = DB::table('anio_escolars')
            ->orderBy('inicio_anio_escolar', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        $pensumId = null;
        if ($anioMasAntiguo) {
            $pensumExistente = DB::table('pensums')
                ->where('nombre', 'Plan vigente (migrado)')
                ->first();

            if ($pensumExistente) {
                $pensumId = $pensumExistente->id;
            } else {
                $pensumId = DB::table('pensums')->insertGetId([
                    'nombre' => 'Plan vigente (migrado)',
                    'resolucion' => null,
                    'anio_escolar_inicio_id' => $anioMasAntiguo->id,
                    'anio_escolar_fin_id' => null,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Agregar pensum_id, horas_semanales (tinyint null) y nota_aprobatoria (tinyint null)
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            $table->foreignId('pensum_id')->nullable()->after('status')->constrained('pensums');
            $table->tinyInteger('horas_semanales')->nullable()->after('pensum_id');
            $table->tinyInteger('nota_aprobatoria')->nullable()->after('horas_semanales');
        });

        // 3. Rellenar pensum_id de todas las filas actuales con ese pensum
        if ($pensumId) {
            DB::table('grado_area_formacions')
                ->whereNull('pensum_id')
                ->update(['pensum_id' => $pensumId]);
        }

        // 4. Volver pensum_id NOT NULL si se pudo asociar a un pensum o si la tabla no tiene filas
        if ($pensumId || DB::table('grado_area_formacions')->count() === 0) {
            Schema::table('grado_area_formacions', function (Blueprint $table) {
                $table->unsignedBigInteger('pensum_id')->nullable(false)->change();
            });
        }

        // 5. Reemplazar el único (grado_id, area_formacion_id) por (pensum_id, grado_id, area_formacion_id)
        // Se añade un índice explícito a grado_id para preservar el requerimiento de clave foránea de InnoDB
        // y permitir la sustitución del índice único sin violar restricciones.
        // Se usa un nombre explícito para la clave única que no supere los 64 caracteres de MySQL.
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            $table->index('grado_id', 'grado_area_formacions_grado_id_index');
            $table->dropUnique(['grado_id', 'area_formacion_id']);
            $table->unique(
                ['pensum_id', 'grado_id', 'area_formacion_id'],
                'grado_area_formacions_pensum_grado_area_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            // Se debe soltar primero la clave foránea pensum_id antes de soltar el índice único
            $table->dropForeign(['pensum_id']);
            $table->unique(['grado_id', 'area_formacion_id']);
            $table->dropUnique('grado_area_formacions_pensum_grado_area_unique');
            $table->dropIndex('grado_area_formacions_grado_id_index');
            $table->dropColumn(['pensum_id', 'horas_semanales', 'nota_aprobatoria']);
        });

        DB::table('pensums')->where('nombre', 'Plan vigente (migrado)')->delete();
    }
};
