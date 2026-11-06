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
        // Paso 1: En MySQL/InnoDB toda clave foránea exige un índice que comience por dicha columna.
        // Como el índice único anterior empezaba por grado_id, MySQL lo utilizaba para respaldar
        // la FK 'grado_id'. Creamos un índice regular para grado_id antes de eliminar el unique.
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            $table->index('grado_id', 'grado_area_formacions_grado_id_index');
        });

        // Paso 2: Con la FK respaldada, eliminamos el unique anterior y agregamos las nuevas columnas y unique
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            $table->dropUnique(['grado_id', 'area_formacion_id']);

            // Llave foránea hacia el pensum
            $table->foreignId('pensum_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('pensums')
                  ->cascadeOnDelete()
                  ->comment('Plan de estudio al que pertenece esta configuración curricular');

            // Columnas del DBML v2611
            $table->unsignedTinyInteger('horas')->nullable()->after('area_formacion_id')->comment('Carga horaria o bloques semanales');
            $table->decimal('nota_aprobatoria', 4, 2)->default(10.00)->after('horas')->comment('Calificación mínima aprobatoria');
            $table->enum('escala', ['numerica', 'literal'])->default('numerica')->after('nota_aprobatoria')->comment('Escala de evaluación: literal solo para OC, PGCRP y Orientación Vocacional');
            $table->string('componente', 100)->nullable()->after('escala')->comment('Componente curricular (ej. Formación General)');
            $table->unsignedSmallInteger('orden')->default(1)->after('componente')->comment('Orden de visualización e impresión oficial');

            // Nueva restricción única por pensum, grado y área
            $table->unique(['pensum_id', 'grado_id', 'area_formacion_id'], 'gaf_pensum_grado_area_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grado_area_formacions', function (Blueprint $table) {
            $table->dropUnique('gaf_pensum_grado_area_unique');
            $table->dropForeign(['pensum_id']);
            $table->dropColumn([
                'pensum_id',
                'horas',
                'nota_aprobatoria',
                'escala',
                'componente',
                'orden',
            ]);

            $table->unique(['grado_id', 'area_formacion_id']);
            $table->dropIndex('grado_area_formacions_grado_id_index');
        });
    }
};
