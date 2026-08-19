<?php

namespace App\Services;

use App\Enums\CategoriaDia;
use App\Enums\CategoriaPalabraClave;
use App\Enums\ConfianzaDia;
use App\Enums\EstadoCalendario;
use App\Enums\OrigenCalendario;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioDia;
use App\Models\CalendarioPalabraClave;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class CalendarioPdfExtractorService
{
    /*
    aqui se tiene los meses validos y su respectivo numero, para reconstruir lo que seria la fecha completa
    debe de coincidir con como aparecen en mayusculas en el encabezado de cada pagina del pdf (ej : "SEPTIEMBRE", "OCTUBRE", etc)
     */
    private const MESES = [
        'ENERO' => 1,
        'FEBRERO' => 2,
        'MARZO' => 3,
        'ABRIL' => 4,
        'MAYO' => 5,
        'JUNIO' => 6,
        'JULIO' => 7,
        'AGOSTO' => 8,
        'SEPTIEMBRE' => 9,
        'OCTUBRE' => 10,
        'NOVIEMBRE' => 11,
        'DICIEMBRE' => 12
    ];

    /*
    punto de entrada del servicio : se procesa un pdf completo para un año escolar y deja los candidatos guardados, listos solo ya de revision humana
    aqui se ejecuta en orden los procesos del 1 al 6
    */

    public function procesarPdf(UploadedFile $archivo, AnioEscolar $anioEscolar): CalendarioAcademico
    {
        return DB::transaction(function () use ($archivo, $anioEscolar) {
            $rutaRelativa = $archivo->store('calendarios', 'local');

            $calendario = CalendarioAcademico::create([
                'anio_escolar_id' => $anioEscolar->id,
                'origen' => OrigenCalendario::Pdf,
                'pdf_original' => $rutaRelativa,
                'estado' => EstadoCalendario::PendienteRevision,
            ]);

            $palabrasNoLaborable = CalendarioPalabraClave::palabrasActivasPorCategoria(
                CategoriaPalabraClave::NoLaborable
            );
            $palabrasEfemeride = CalendarioPalabraClave::palabrasActivasPorCategoria(
                CategoriaPalabraClave::Efemeride
            );

            $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);
            $paginas = $this->extraerPaginasDeTexto($rutaAbsoluta);

            foreach ($paginas as $pagina) {
                $this->procesarPagina($calendario, $anioEscolar, $pagina, $palabrasNoLaborable, $palabrasEfemeride);
            }

            return $calendario->fresh('dias');
        });
    }

    /*
    procesa una sola pagina : detectando el mes y se clasifica cada linea dentro de ella
    */

    private function procesarPagina(
        CalendarioAcademico $calendario,
        AnioEscolar $anioEscolar,
        array $pagina,
        array $palabrasNoLaborable,
        array $palabrasEfemeride,
    ):void {
        $mes = $this->detectarMes($pagina['texto']);

        // si no se pudo detectar el mes, no hay forma de construir fechas en esta pagina;
        // por lo tanto se omite en vez de arriesgar fechas mal calculadas.
        if($mes === null){
            Log::info("no se detecto el mes en la pagina {$pagina['numero']} del calendario {$calendario->id} ");
            return;
        }

        $lineas = preg_split('/\r\n|\r|\n/', $pagina['texto']) ?: [];

        foreach($lineas as $lineaOriginal){
            $linea = trim($lineaOriginal);

            if ($linea === ''){
                continue;
            }

            $categoria = $this->clasificarLinea($linea, $palabrasNoLaborable, $palabrasEfemeride);

            //las efemerides se descartan aqui mismo, nunca llega a guardarse.
            if ($categoria === 'efemeride'){
                    continue;
            }

            $this->procesarLinea($calendario, $anioEscolar, $linea, $mes , $categoria);

        }

    }

    /*
    extra el o los dias de una linea que ya fue clasificada, construye su fecha y la guarda como candidato.
    cubre el caso especial de "mes completo".
    */

    private function procesarLinea(
        CalendarioAcademico $calendario,
        AnioEscolar $anioEscolar,
        string $linea,
        string $mes,
        string $categoria,
    ) : void {
        $extraccion = $this->extraerDias($linea);

        //caso especial : "todo el mes de agosto : escuelas abiertas".
        //no tiene un dia puntual , se guarda sin fecha pero marcado

        if ($extraccion['es_mes_completo']){
            $this->guardarCandidato(
                calendario : $calendario,
                texto : $linea,
                fecha : null,
                categoria : $categoria,
                confianza : ConfianzaDia::Alta->value,
                mesPagina : $mes,
                diaExtraidoTexto : null,
                esMesCompleto : true,

            );
            return;
        }

        $confianza = $categoria === CategoriaDia::NoLaborable->value?ConfianzaDia::Alta->value:ConfianzaDia::Dudosa->value;

        foreach($extraccion['dias'] as $dia){
                if($dia < 1 || $dia > 31){
                    continue; // ruido de extraccion , no es un dia valido

                }

                $fecha = $this->construirFecha($anioEscolar, $mes, $dia);

                $this->guardarCandidato(
                    calendario : $calendario,
                    texto : $linea,
                    fecha : $fecha,
                    categoria : $categoria,
                    confianza : $confianza,
                    mesPagina : $mes,
                    diaExtraidoTexto : implode('y', $extraccion['dias']),
                    esMesCompleto : false,

                );

        }

    }


// convierte el pdf en una coleccion de paginas de texto plano
private function extraerPaginasDeTexto(string $rutaAbsoluta) : array
{
$parser = new Parser();
$documento = $parser->parseFile($rutaAbsoluta);

$paginas = [];

foreach ($documento->getPages() as $indice => $pagina){
    try{
        $texto = $pagina->getText();
    } catch (\Throwable $e){
        // pagina sin texto extraible (ejemplo , es una imagen dentro del pdf)
        // se registra vacia en vez de detener todo el proceso

        Log::warning("pagina {$indice} sin texto extraible ", ['error' =>$e->getMessage()]);
        $texto = '';

    }

    $paginas[] = ['numero' => $indice + 1, 'texto' => $texto];

}

return $paginas;

}
// busca el nombre del mes en mayusculas dentro del texto de una pagina
// tal como aparece en el encabezado  de la cuadricula del calendario

private function detectarMes(string $texto) : ?string
{

foreach(array_keys(self::MESES) as $mes){
    if (str_contains($texto, $mes)){
        return $mes;
    }
}

return null;

}

private function clasificarLinea(string $linea, array $palabrasNoLaborable, array $palabrasEfemeride): string
{

$lineaMinusculas = mb_strtolower($linea);

foreach ($palabrasNoLaborable as $palabra){
    if(str_contains($lineaMinusculas, mb_strtolower($palabra))){
        return CategoriaDia::NoLaborable->value;
    }
}

foreach ($palabrasEfemeride as $palabra) {
            if (str_contains($lineaMinusculas, mb_strtolower($palabra))) {
                return 'efemeride';
            }
        }

        return CategoriaDia::Dudoso->value;

}

// reconoce varios formatos : osea un solo dia , "N" y "M", o un rango "N-M" / "N y M".
// tambien detecta el caso especial de "todo el mes"

private function extraerDias(string $linea): array
{
    // el caso especial : ejemplo "todo el mes de agosto : escuelas abiertas"
    if (preg_match('/^todo\s+el\s+mes/iu',$linea)){
        return['dias'=> [], 'es_mes_completo' => true];
    }

    // 16 y 17 asuesto de carnaval <- ejemplo
    if(preg_match('/^(\d{1,2})\s+y\s+(\d{1,2})\b/iu', $linea , $coincidencias)){
        return [
            'dias' => [(int) $coincidencias[1], (int) $coincidencias[2]],
            'es_mes_completo' => false,
        ];
    }

    // rango : "23-27" o "23 al 27"
    if(preg_match('/^(\d{1,2})\s*(?:-|al)\s*(\d{1,2})\b/iu', $linea, $coincidencias)){
        $inicio = (int) $coincidencias[1];
        $fin = (int) $coincidencias[2];

        if ($inicio <= $fin && ($fin - $inicio) < 31){
                return ['dias' => range($inicio, $fin), 'es_mes_completo' => false];
        }

    }

    // un solo numero al inicio de la linea
    if (preg_match('/^(\d{1,2})\b/u', $linea, $coincidencias)){
        return ['dias' => [(int) $coincidencias[1]], 'es_mes_completo' => false];
    }

    // La línea no empieza con ningún número reconocible
        return ['dias' => [], 'es_mes_completo' => false];

}

// aqui combina el año escolar (usando las fechas reales de este caso anio_escolars), el mes de la pagina y el dia de la linea

private function construirFecha(AnioEscolar $anioEscolar, string $mesTexto, int $dia): ?Carbon
{

$numeroMes = self::MESES[$mesTexto] ?? null;

if ($numeroMes === null){
    return null;
}

$inicio = Carbon::parse($anioEscolar->inicio_anio_escolar);
$cierre = Carbon::parse($anioEscolar->cierre_anio_escolar);

// si el mes es igual o posterior al mes de inicio del año escolar
// pertenece al año calendario de inicio ; si no, al de cierre
// Esto generaliza el caso septiembre-agosto sin fijarlo en el codigo.

$anioUsar = $numeroMes >= $inicio->month ? $inicio->year : $cierre->year;

try{
    return Carbon::create($anioUsar, $numeroMes, $dia)->startOfDay();
}catch (\Throwable $e) {
        // fecha invalida (ejemplo "31 de febrero" se descarta xd)
        return null;
    }

}

// guardarCandidato
// persiste el resultado como candidato sin confirmar , evitando duplicados.

private function guardarCandidato(
    CalendarioAcademico $calendario,
    string $texto,
    ? Carbon $fecha,
    string $categoria,
    string $confianza,
    ? string $mesPagina,
    ? string $diaExtraidoTexto,
    bool $esMesCompleto,
): ?CalendarioDia{
    // Sin fecha valida y sin ser "mes completo" no hay nada que guardar
    if($fecha === null && ! $esMesCompleto){
        return null;
    }

    $fechaTexto = $fecha?->toDateString();

    $yaExiste = CalendarioDia::query()
    ->where('calendario_id', $calendario->id)
    ->where('texto_extraido', $texto)
    ->when(
        $fechaTexto === null,
        fn ($query) => $query->whereNull('fecha'),
        fn ($query) => $query->where('fecha', $fechaTexto),
    )
    ->exist();

    if($yaExiste){
        return null;
    }

    return CalendarioDia::create([
        'calendario_id' => $calendario->id,
            'texto_extraido' => $texto,
            'mes_pagina' => $mesPagina,
            'dia_extraido' => $diaExtraidoTexto,
            'fecha' => $fechaTexto,
            'categoria' => $categoria,
            'confianza' => $confianza,
            'es_mes_completo' => $esMesCompleto,
            'confirmado' => false,
            'aplica_personal' => true,
            'aplica_estudiantes' => true,
    ]);

}

// confirmarCalendario
// Convierte los candidatos aprobados por la subdirectora en el
// calendario definitivo. Este es el único paso que depende de una
//

    public function confirmar(CalendarioAcademico $calendario, array $idsAprobados, array $ajustesManuales = []): CalendarioAcademico
    {
        return DB::transaction(function () use ($calendario, $idsAprobados, $ajustesManuales) {
            CalendarioDia::query()
                ->where('calendario_id', $calendario->id)
                ->whereIn('id', $idsAprobados)
                ->update(['confirmado' => true]);

            // Los candidatos que la subdirectora NO marcó quedan con
            // confirmado = false, como historial de qué se descartó.

            foreach ($ajustesManuales as $ajuste) {
                CalendarioDia::create([
                    'calendario_id' => $calendario->id,
                    'texto_extraido' => $ajuste['descripcion'],
                    'fecha' => $ajuste['fecha'],
                    'categoria' => $ajuste['categoria'],
                    'confianza' => ConfianzaDia::Manual->value,
                    'confirmado' => true,
                    'aplica_personal' => $ajuste['aplica_personal'] ?? true,
                    'aplica_estudiantes' => $ajuste['aplica_estudiantes'] ?? true,
                ]);
            }

            $calendario->update([
                'estado' => EstadoCalendario::Confirmado,
                'fecha_confirmacion' => now(),
            ]);

            return $calendario->fresh('dias');
        });
    }
}



