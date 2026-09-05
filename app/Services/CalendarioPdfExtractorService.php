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
    /**
     * Mes "activo" mientras se recorren las líneas del documento en orden.
     * Se actualiza cada vez que aparece una línea que es, ella sola, el
     * nombre de un mes — no se resetea por página, porque una misma página
     * física puede contener el final de un mes y el inicio del siguiente.
     */
    private ?string $mesActual = null;


    /*
    aqui la libreria de smalot/pdfparser puede extraer el mismo bloque de texto al inicio de dos paginas consecutivas
    (esto es una particularidad del layout del pdf como tal) - lo que se hace esto es permitir detectar y saltar ese bloque repetido


    */

    private array $lineasPaginaAnterior = [];


    /**
     * Meses válidos y su número, para reconstruir la fecha completa (algoritmo 5).
     * Deben coincidir con cómo aparecen en mayúsculas en el encabezado de cada
     * página del PDF (ej. "SEPTIEMBRE", "OCTUBRE").
     */
    private const MESES = [
        'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
        'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
        'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
    ];

    /**
     * Punto de entrada del servicio: procesa un PDF completo para un año
     * escolar y deja los candidatos guardados, listos para revisión humana.
     * Ejecuta, en orden, los algoritmos 1 al 6.
     */
    public function procesarPdf(UploadedFile $archivo, AnioEscolar $anioEscolar): CalendarioAcademico
    {
        return DB::transaction(function () use ($archivo, $anioEscolar) {
            $this->mesActual = null; // por si el servicio se reutiliza entre peticiones

            $this->lineasPaginaAnterior = [];


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

    /**
     * Procesa una sola página, línea por línea, actualizando el mes activo
     * (this->mesActual) cada vez que encuentra un encabezado de mes.
     * Esto reemplaza al algoritmo 2 original ("un mes por página"): el PDF
     * real puede tener el final de un mes y el inicio del siguiente en la
     * misma página física, así que el mes se seguimiento por línea, no por página.
     */
    // private function procesarPagina(
    //     CalendarioAcademico $calendario,
    //     AnioEscolar $anioEscolar,
    //     array $pagina,
    //     array $palabrasNoLaborable,
    //     array $palabrasEfemeride,
    // ): void {
    //     $lineas = preg_split('/\r\n|\r|\n/', $pagina['texto']) ?: [];

    //     foreach ($lineas as $lineaOriginal) {
    //         $linea = trim($lineaOriginal);

    //         if ($linea === '') {
    //             continue;
    //         }

    //         // ¿Es esta línea, ella sola, el nombre de un mes? (encabezado de
    //         // la cuadrícula, ej. "SEPTIEMBRE"). Si sí, actualiza el mes activo
    //         // y no se procesa como evento.
    //         $mesDetectado = $this->esEncabezadoDeMes($linea);
    //         if ($mesDetectado !== null) {
    //             $this->mesActual = $mesDetectado;
    //             continue;
    //         }

    //         // Sin un mes activo todavía (ej. la portada, antes del primer
    //         // encabezado), no hay contexto confiable para construir fechas.
    //         if ($this->mesActual === null) {
    //             continue;
    //         }

    //         // Filas de la cuadrícula del mini-calendario (ej. "1 2 3 4 5 6 7"),
    //         // no son eventos: solo números y espacios, sin ningún texto real.
    //         if (preg_match('/^[\d\s]+$/u', $linea)) {
    //             continue;
    //         }

    //         $categoria = $this->clasificarLinea($linea, $palabrasNoLaborable, $palabrasEfemeride);

    //         // Las efemérides se descartan aquí mismo, nunca llegan a guardarse.
    //         if ($categoria === 'efemeride') {
    //             continue;
    //         }

    //         $this->procesarLinea($calendario, $anioEscolar, $linea, $this->mesActual, $categoria);
    //     }
    // }


        private function procesarPagina(
        CalendarioAcademico $calendario,
        AnioEscolar $anioEscolar,
        array $pagina,
        array $palabrasNoLaborable,
        array $palabrasEfemeride,
    ): void {
        $lineasCrudas = preg_split('/\r\n|\r|\n/', $pagina['texto']) ?: [];

        $lineasLimpias = array_values(array_filter(array_map('trim',$lineasCrudas), fn(string $l) => $l !== ''));

        // smalot/pdfparser puede repetir el mismo bloque de lineas al inicio de dos paginas consecutivas . Entonces si el principio de esta
        // pagina coincide exactamente con el principio de la anterior , es ese bloque  duplicado - se descarta entonces antes de procesar cualquier linea
        $solapamiento = $this->contarLineasSolapadasAlInicio($lineasLimpias, $this->lineasPaginaAnterior);

        if($solapamiento > 0){
            Log::info("la pagina {$pagina['numero']} : se detectaron {$solapamiento} lineas duplicadas de la pagina anterior por lo tanto se van a omitir");
        }
        // justo aqui lo que hacemos es que se guarde ANTES de recortar, para que se compare la proxima pagina con el contenido completo
        $this->lineasPaginaAnterior = $lineasLimpias;

        $lineas = array_slice($lineasLimpias, $solapamiento);

        foreach ($lineas as $linea) {
            // $linea = trim($lineaOriginal);

            // if ($linea === '') {
            //     continue;
            // }

            // ¿Es esta línea, ella sola, el nombre de un mes? (encabezado de
            // la cuadrícula, ej. "SEPTIEMBRE"). Si sí, actualiza el mes activo
            // y no se procesa como evento.
            $mesDetectado = $this->esEncabezadoDeMes($linea);
            if ($mesDetectado !== null) {
                $this->mesActual = $mesDetectado;
                continue;
            }

            // Sin un mes activo todavía (ej. la portada, antes del primer
            // encabezado), no hay contexto confiable para construir fechas.
            if ($this->mesActual === null) {
                continue;
            }

            // Filas de la cuadrícula del mini-calendario (ej. "1 2 3 4 5 6 7"),
            // no son eventos: solo números y espacios, sin ningún texto real.
            if (preg_match('/^[\d\s]+$/u', $linea)) {
                continue;
            }

            $categoria = $this->clasificarLinea($linea, $palabrasNoLaborable, $palabrasEfemeride);

            // Las efemérides se descartan aquí mismo, nunca llegan a guardarse.
            if ($categoria === 'efemeride') {
                continue;
            }

            $this->procesarLinea($calendario, $anioEscolar, $linea, $this->mesActual, $categoria);
        }
    }


    // nueva funcion contarLineasSolapadasAlInicio
    /*cuenta cuantas lineas , desde el inicio , son identicas entre la pagina y la anterior. esta se detiene a la primera diferencia que cuente
    -solo detecta el caso de bloque repetido "pegado" al inicio de la pagina  */

    private function contarLineasSolapadasAlInicio(array $lineasActual, array $lineasAnterior) : int
    {
        $maximo = min(count($lineasActual), count($lineasAnterior));
        // aqui contamos el numero de coincidencias
        $coincidencias = 0;

        for($i = 0; $i < $maximo; $i++){
            if($lineasActual[$i] === $lineasAnterior[$i]){
                break; // se detiene al primer bloque diferente
            }

            $coincidencias++;
        }

        return $coincidencias;

    }


    /**
     * Extrae él o los días de una línea ya clasificada, construye su fecha
     * y la guarda como candidato. Cubre el caso especial de "mes completo".
     */
    private function procesarLinea(
        CalendarioAcademico $calendario,
        AnioEscolar $anioEscolar,
        string $linea,
        string $mes,
        string $categoria,
    ): void {
        $extraccion = $this->extraerDias($linea);

        // Caso especial: "Todo el mes de agosto: Escuelas Abiertas".
        // No tiene un día puntual, se guarda sin fecha pero marcado.
        if ($extraccion['es_mes_completo']) {
            $this->guardarCandidato(
                calendario: $calendario,
                texto: $linea,
                fecha: null,
                categoria: $categoria,
                confianza: ConfianzaDia::Alta->value,
                mesPagina: $mes,
                diaExtraidoTexto: null,
                esMesCompleto: true,
            );
            return;
        }

        $confianza = $categoria === CategoriaDia::NoLaborable->value
            ? ConfianzaDia::Alta->value
            : ConfianzaDia::Dudosa->value;

        foreach ($extraccion['dias'] as $dia) {
            if ($dia < 1 || $dia > 31) {
                continue; // ruido de extracción, no es un día válido
            }

            $fecha = $this->construirFecha($anioEscolar, $mes, $dia);

            $this->guardarCandidato(
                calendario: $calendario,
                texto: $linea,
                fecha: $fecha,
                categoria: $categoria,
                confianza: $confianza,
                mesPagina: $mes,
                diaExtraidoTexto: implode(' y ', $extraccion['dias']),
                esMesCompleto: false,
            );
        }
    }

    /**
     * funcion 1 extraerPaginasDeTexto
     * Convierte el PDF en una colección de páginas de texto plano.
     */
    private function extraerPaginasDeTexto(string $rutaAbsoluta): array
    {
        $parser = new Parser();
        $documento = $parser->parseFile($rutaAbsoluta);

        $paginas = [];

        foreach ($documento->getPages() as $indice => $pagina) {
            try {
                $texto = $pagina->getText();
            } catch (\Throwable $e) {
                // Página sin texto extraíble (ej. es una imagen dentro del PDF).
                // Se registra vacía en vez de detener todo el proceso.
                Log::warning("Página {$indice} sin texto extraíble", ['error' => $e->getMessage()]);
                $texto = '';
            }

            $paginas[] = ['numero' => $indice + 1, 'texto' => $texto];
        }

        return $paginas;
    }

    /**
     * esEncabezadoDeMes
     * Antes buscaba el mes en cualquier parte del texto de una página
     * completa. Ahora compara si la LÍNEA, ella sola, es exactamente el
     * nombre de un mes — así se detecta el cambio de mes exacto dentro
     * de una página, en vez de asumir un solo mes por página.
     */
    private function esEncabezadoDeMes(string $linea): ?string
    {
        $lineaNormalizada = mb_strtoupper(trim($linea));

        return array_key_exists($lineaNormalizada, self::MESES) ? $lineaNormalizada : null;
    }

    /**
     *  clasificarLinea
     * Decide si una línea es no laborable, efeméride, o dudosa,
     * usando el diccionario compartido de calendario_palabras_clave.
     */
    private function clasificarLinea(string $linea, array $palabrasNoLaborable, array $palabrasEfemeride): string
    {
        $lineaMinusculas = mb_strtolower($linea);

        foreach ($palabrasNoLaborable as $palabra) {
            if (str_contains($lineaMinusculas, mb_strtolower($palabra))) {
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

    /**
     *  extraerDias
     * Reconoce varios formatos: un solo día, "N y M", o un rango "N-M" / "N al M".
     * También detecta el caso especial de "todo el mes".
     */
    private function extraerDias(string $linea): array
    {
        // Caso especial: "Todo el mes de agosto: Escuelas Abiertas"
        if (preg_match('/^todo\s+el\s+mes/iu', $linea)) {
            return ['dias' => [], 'es_mes_completo' => true];
        }

        // "16 y 17 Asueto de Carnaval"
        if (preg_match('/^(\d{1,2})\s+y\s+(\d{1,2})\b/iu', $linea, $coincidencias)) {
            return [
                'dias' => [(int) $coincidencias[1], (int) $coincidencias[2]],
                'es_mes_completo' => false,
            ];
        }

        // Rango: "23-27" o "23 al 27"
        if (preg_match('/^(\d{1,2})\s*(?:-|al)\s*(\d{1,2})\b/iu', $linea, $coincidencias)) {
            $inicio = (int) $coincidencias[1];
            $fin = (int) $coincidencias[2];

            if ($inicio <= $fin && ($fin - $inicio) < 31) {
                return ['dias' => range($inicio, $fin), 'es_mes_completo' => false];
            }
        }

        // Un solo número al inicio de la línea
        if (preg_match('/^(\d{1,2})\b/u', $linea, $coincidencias)) {
            return ['dias' => [(int) $coincidencias[1]], 'es_mes_completo' => false];
        }

        // La línea no empieza con ningún número reconocible
        return ['dias' => [], 'es_mes_completo' => false];
    }

    /**
     * ALGORITMO 5: construirFecha
     * Combina el año escolar (usando las fechas reales de anio_escolars,
     * no un string parseado), el mes de la página y el día de la línea.
     */
    private function construirFecha(AnioEscolar $anioEscolar, string $mesTexto, int $dia): ?Carbon
    {
        $numeroMes = self::MESES[$mesTexto] ?? null;

        if ($numeroMes === null) {
            return null;
        }

        $inicio = Carbon::parse($anioEscolar->inicio_anio_escolar);
        $cierre = Carbon::parse($anioEscolar->cierre_anio_escolar);

        // Si el mes es igual o posterior al mes de inicio del año escolar,
        // pertenece al año calendario de inicio; si no, al de cierre.
        // Esto generaliza el caso septiembre-agosto sin fijarlo en el código.
        $anioUsar = $numeroMes >= $inicio->month ? $inicio->year : $cierre->year;

        // checkdate() valida ANTES de construir. Sin esto, Carbon::create()
        // no rechaza un día inválido (ej. 31 de noviembre): lo "desborda"
        // silenciosamente al 1 de diciembre, generando una fecha incorrecta
        // en vez de descartar el candidato.
        if (! checkdate($numeroMes, $dia, $anioUsar)) {
            return null;
        }

        return Carbon::create($anioUsar, $numeroMes, $dia)->startOfDay();
    }

    /**
     * funcion 6  guardarCandidato
     * Persiste el resultado como candidato sin confirmar, evitando duplicados.
     */
    private function guardarCandidato(
        CalendarioAcademico $calendario,
        string $texto,
        ?Carbon $fecha,
        string $categoria,
        string $confianza,
        ?string $mesPagina,
        ?string $diaExtraidoTexto,
        bool $esMesCompleto,
    ): ?CalendarioDia {
        // Sin fecha válida y sin ser "mes completo" no hay nada que guardar.
        if ($fecha === null && ! $esMesCompleto) {
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
            ->exists();

        if ($yaExiste) {
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

    /**
     * funcion 7 confirmarCalendario
     * Convierte los candidatos aprobados por la subdirectora en el
     * calendario definitivo. Este es el único paso que depende de una
     * decisión humana, no de lógica automática.
     */
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




