<?php
 
namespace Database\Seeders;
 
use App\Enums\CategoriaPalabraClave;
use App\Models\CalendarioPalabraClave;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
 
class CalendarioPalabraClaveSeeder extends Seeder
{
    /**
     * Diccionario base. Sin esto, el algoritmo 3 (clasificarLinea) nunca
     * tiene nada que comparar y todo cae en "dudoso" por defecto.
     */
    public function run(): void
    {
        $palabrasNoLaborable = [
            'asueto',
            'vacaciones',
            'año nuevo',
            'noche buena',
            'día de navidad',
            'fin de año',
            'inicio de año escolar',
            'fin de año escolar',
            'regreso a clases',
        ];
 
        $palabrasEfemeride = [
            'natalicio de',
            'día internacional de',
            'día mundial de',
            'día nacional de',
            'declaratoria de',
            'batalla de',
        ];
 
        foreach ($palabrasNoLaborable as $palabra) {
            CalendarioPalabraClave::firstOrCreate([
                'palabra' => $palabra,
                'categoria' => CategoriaPalabraClave::NoLaborable,
            ], [
                'activa' => true,
            ]);
        }
 
        foreach ($palabrasEfemeride as $palabra) {
            CalendarioPalabraClave::firstOrCreate([
                'palabra' => $palabra,
                'categoria' => CategoriaPalabraClave::Efemeride,
            ], [
                'activa' => true,
            ]);
        }
    }
}
