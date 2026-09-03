<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaFormacionSeeder extends Seeder
{
    public function run(): void
    {
        // REGLA DE NEGOCIO: aula_id en AreaFormacion solo se usa para materias que requieren un espacio físico especializado.
        // El aula regular la determina la sección (tabla seccion_aula), NO la materia.
        // Solo estas materias especializadas tienen aula_id asignado:
        // - Arte y Patrimonio → Sala de Música (id 10)
        // - Ciencias Naturales, Biología, Física, Química → Laboratorio de Computación (id 9)
        // El resto de materias usan aula_id = null y su aula se define por la sección.

        DB::table('area_formacions')->insert([
            ['nombre_area_formacion' =>  'Castellano', 'codigo_area' => '1', 'siglas' => 'CAST', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true], /* 1 */
            ['nombre_area_formacion' => 'Arte y Patrimonio', 'codigo_area' => '2', 'siglas' => 'ART', 'horas_semanales' => 10, 'bloques_maximos_por_dia' => 2, 'aula_id' => 10, 'status' => true], /* 2 - Sala de Música */
            ['nombre_area_formacion' => 'Ciencias Naturales', 'codigo_area' => '3', 'siglas' => 'CIN', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => 9, 'status' => true], /* 3 - Laboratorio de Computación */
            ['nombre_area_formacion' => 'Biología', 'codigo_area' => '4', 'siglas' => 'BIO', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => 9, 'status' => true], /* 4 - Laboratorio de Computación */
            ['nombre_area_formacion' => 'Matemáticas', 'codigo_area' => '5', 'siglas' => 'MAT', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true], /* 5 */
            ['nombre_area_formacion' => 'Educación Física', 'codigo_area' => '6', 'siglas' => 'EDF', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true], /* 6 */
            ['nombre_area_formacion' => 'Inglés y otras lenguas extranjeras', 'codigo_area' => '7', 'siglas' => 'IEL', 'horas_semanales' => 6, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true],/* 7 */
            ['nombre_area_formacion' => 'Geografía, Historia y Ciudadanía', 'codigo_area' => '8', 'siglas' => 'GHC', 'horas_semanales' => 10, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true],/* 8 */
            ['nombre_area_formacion' => 'Ciencias de la Tierra', 'codigo_area' => '9', 'siglas' => 'CTI', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true],/* 9 */
            ['nombre_area_formacion' => 'Física', 'codigo_area' => '10', 'siglas' => 'FIS', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => 9, 'status' => true],/* 10 - Laboratorio de Computación */
            ['nombre_area_formacion' => 'Química', 'codigo_area' => '11', 'siglas' => 'QUI', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => 9, 'status' => true],/* 11 - Laboratorio de Computación */
            ['nombre_area_formacion' => 'Formación para la Soberania Nacional', 'codigo_area' => '12', 'siglas' => 'FSN', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true],/* 12 */
            ['nombre_area_formacion' => 'Orientación y Convivencia', 'codigo_area' => '13', 'siglas' => 'OC', 'horas_semanales' => 5, 'bloques_maximos_por_dia' => 2, 'aula_id' => null, 'status' => true],/* 13 */
        ]);
        $this->command->info('Areas de Formación insertados correctamente.');

        DB::table('grupo_estables')->insert([
            ['nombre_grupo_estable' =>  'Domino', 'status' => true], /* 1 */
            ['nombre_grupo_estable' => 'Ajedrez', 'status' => true], /* 2 */
            ['nombre_grupo_estable' => 'Kikimbol', 'status' => true], /* 3 */
            ['nombre_grupo_estable' => 'Taekwondo', 'status' => true], /* 4 */
            ['nombre_grupo_estable' => 'Futbol', 'status' => true], /* 5 */
            ['nombre_grupo_estable' => 'Baloncesto', 'status' => true], /* 6 */
            ['nombre_grupo_estable' => 'Manos a la Siembra', 'status' => true], /* 7 */

        ]);
        $this->command->info('Grupos estables insertados correctamente.');
    }
}