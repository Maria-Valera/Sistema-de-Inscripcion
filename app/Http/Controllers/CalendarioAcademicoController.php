<?php

namespace App\Http\Controllers;

use App\Enums\ConfianzaDia;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioDia;
use App\Services\CalendarioPdfExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioAcademicoController extends Controller
{
    public function __construct(
        private readonly CalendarioPdfExtractorService $extractorService,
    ) {
    }

    /**
     * Lista todos los calendarios cargados, con su año escolar y estado.
     */
    public function index(): View
    {
        $calendarios = CalendarioAcademico::with('anioEscolar')
            ->withCount(['dias', 'diasConfirmados'])
            ->latest()
            ->paginate(15);

        return view('calendario_academico.index', compact('calendarios'));
    }



    // aqui tenemos la entrada de una pantalla de decision : o el usuario se va por el camino de extraer el calendario por pdf o hacerlo de manera manual
    // para cargar un calendario nuevo

    public function create(): View {
        $hayAniosDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->exists();

        return view('calendario_academico.create', compact('hayAniosDisponibles'));
    }


    /**
     * Muestra el formulario para subir el PDF de un año escolar.
     */
    public function createPdf(): View
    {
        // Solo se ofrecen años escolares que todavía no tienen un calendario asociado,
        // porque la relación es de uno a uno ( ANIO_ESCOLARS ||--o| CALENDARIOS_ACADEMICOS).
        $aniosEscolaresDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->get();

        return view('calendario_academico.create-pdf', compact('aniosEscolaresDisponibles'));
    }

    public function createManual(): View{
        $aniosEscolaresDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->get();

        return view('calendario_academico.create-manual',compact('aniosEscolaresDisponibles'));
    }

    /**
     * Recibe el PDF y redirige a la
     * pantalla de revisión con los candidatos generados.
     */
    public function store(Request $request): RedirectResponse
    {
        $validado = $request->validate([
            'anio_escolar_id' => ['required', 'exists:anio_escolars,id'],
            'archivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20 MB máx.
        ]);

        $anioEscolar = AnioEscolar::findOrFail($validado['anio_escolar_id']);

        $calendario = $this->extractorService->procesarPdf(
            archivo: $request->file('archivo_pdf'),
            anioEscolar: $anioEscolar,
        );

        return redirect()
            ->route('admin.calendario_academico.show', $calendario)
            ->with('exito', 'PDF procesado. Revisa los candidatos antes de confirmar el calendario.');
    }

    /**
     *  se muestran los candidatos agrupados por mes,
     * con los de confianza "alta" pre-marcados y los "dudosos" sin marcar.
     */


    public function show(CalendarioAcademico $calendarioAcademico):View{

        $calendarioAcademico->load('anioEscolar');

        $dias = $calendarioAcademico->dias()
        ->orderBy('mes_pagina')
        ->orderBy('fecha')
        ->get();

        $candidatosPorMes = $dias->groupBy('mes_pagina');

        // solo los que tienen una fecha real (ejemplo los manuales) se excluye en este caso por el momento "mes completo"
        // ya que no tiene un dia puntual y no puede ubicarse en una celda sola del calendario ( en el camino pdf tengo que arreglar bug de "sin mes" por este mismo detalle)

        $diasParaCalendario = $dias
        ->whereNotNull('fecha')
        ->map(fn (CalendarioDia $dia) => [
            'id' => $dia->id,
            'fecha' => $dia->fecha->toDateString(),
            'texto' => $dia->texto_extraido,
            'categoria' => $dia->categoria->value,
            'categoriaLabel' => $dia->categoria->label(),
            'confianza' => $dia->confianza->value,
            'confianzaLabel' => $dia->confianza->label(),
            'confirmado' => $dia->confirmado,
            'esEfemeride' => $dia->es_efemeride,
            'colorHex' => $dia->color?->hex(),
            'colorValue'      => $dia->color?->value,
            'esManual' => $dia->confianza === \App\Enums\ConfianzaDia::Manual,
            'aplica_personal'    => $dia->aplica_personal,      // <-- nuevo
        'aplica_estudiantes' => $dia->aplica_estudiantes,   // <-- nuevo
        'mesPagina'          => $dia->mes_pagina,
        ])
        ->values();

        return view('calendario_academico.show',[
            'calendario' =>$calendarioAcademico,
            'candidatosPorMes' => $candidatosPorMes,
            'diasParaCalendarioJson' => $diasParaCalendario->toJson(),
            'inicioAnioEscolar' => $calendarioAcademico->anioEscolar->inicio_anio_escolar,
            'cierreAnioEscolar' => $calendarioAcademico->anioEscolar->cierre_anio_escolar,
        ]);


    }

    /**
     * (confirmar): marca como confirmados
     * los candidatos que la subdirectora aprobó desde los checkboxes.
     */
    public function confirmar(Request $request, CalendarioAcademico $calendarioAcademico): RedirectResponse
    {
        $validado = $request->validate([
            'ids_aprobados' => ['array'],
            'ids_aprobados.*' => ['integer', 'exists:calendario_dias,id'],
        ]);

        $this->extractorService->confirmar(
            calendario: $calendarioAcademico,
            idsAprobados: $validado['ids_aprobados'] ?? [],
        );

        return redirect()
            ->route('admin.calendario_academico.index')
            ->with('exito', 'Calendario confirmado correctamente.');
    }


    //  se confirma ( o se revierte ) Un solo dia ( no mas ) desde la vista del calendario, lo hacemos por via AJAX
    // esta funcion en especifico no usa la funcion confirmar por completo,por que ese cierra todo el calendario a la vez ; esto como tal
    // es solo una confirmacion puntual nada mas, mucho mas rapida para cuando el usuario este revisando visualmente dia por dia
    public function confirmarDia(Request $request,CalendarioAcademico $calendarioAcademico, CalendarioDia $calendarioDia): JsonResponse{


        abort_if($calendarioDia->calendario_id !==  $calendarioAcademico->id,404);

        $validado = $request->validate([
            'confirmado' => ['required', 'boolean'],
        ]);

        $calendarioDia->update(['confirmado' => $validado['confirmado']]);

        return response()->json([
            'id' => $calendarioDia->id,
            'confirmado' => $calendarioDia->confirmado,
        ]);
    }

    public function storeManual(Request $request) : RedirectResponse {
        $validado = $request->validate([
            'anio_escolar_id' => ['required','exists:anio_escolars,id'],
        ]);

        $calendario = CalendarioAcademico::create([
            'anio_escolar_id' => $validado['anio_escolar_id'],
            'origen' => \App\Enums\OrigenCalendario::Manual,
            'pdf_original' => null,
            'estado' => \App\Enums\EstadoCalendario::PendienteRevision,
        ]);

        return redirect()
            ->route('admin.calendario_academico.show',$calendario)
            ->with('exito','Calendario creado de manera manual. Ahora puedes agregar los días uno por uno.');



    }

    // funcio 8 : aqui se crea un envento manual, si es un rango de dias , genera  UNA fila por cada dia del rango
    // cada fila queda como registro independiente  despues de creada
    public function eventoStore(Request $request,CalendarioAcademico $calendarioAcademico) : JsonResponse
    {
        $calendarioAcademico->load('anioEscolar');
        $datos = $this->validarDatosEvento($request,$calendarioAcademico);

        $creados = [];

        foreach($this->expandirRangoDeFechas($datos['fecha_inicio'],$datos['fecha_fin']) as $fecha){
            $creados[] = CalendarioDia::create([
                'calendario_id' => $calendarioAcademico->id,
                'fecha' => $fecha,
                'texto_extraido' => $datos['nombre'],
                'categoria' => $datos['categoria']->value,
                'confianza' => \App\Enums\ConfianzaDia::Manual->value,
                'confirmado' => true,


                'aplica_personal' => $datos['aplica_personal'],
                'aplica_estudiantes' => $datos['aplica_estudiantes'],
                'es_efemeride' => $datos['es_efemeride'],
                'color' => $datos['color']?->value,
                'mes_pagina' => (int) \Carbon\Carbon::parse($fecha)->format('n'),
            ]);

        }



        // si el calendario todavia esta pendiente de revision (ejemplo. venia de un pdf y esto es un ajuste suelto) no se toca -
        // el estado general como tal del calendario lo decide la funcion 7, no un evento individual

        return response()->json([
            'creados' => collect($creados)->map(fn(CalendarioDia $d) => $this->serializarDia($d)),
        ],201);

    }

    /*
    eventoUpdate, se edita un dia puntual, si se crea como un rango de dias, esto edita solo la fila de ese dia especifico , no la "serie" de
    dias completa - cada dia es un registro independiente desde que se creo

    */

    public function eventoUpdate(Request $request, CalendarioAcademico $calendarioAcademico, CalendarioDia $calendarioDia) : JsonResponse{

        abort_if($calendarioDia->calendario_id !== $calendarioAcademico->id,404);
        abort_if($calendarioDia->confianza !== \App\Enums\ConfianzaDia::Manual,403,'Solo se pueden editar eventos creados manualmente');

        $calendarioAcademico->load('anioEscolar');
        $datos = $this->validarDatosEvento($request,$calendarioAcademico, esEdicionDeUnSoloDia : true);

        $calendarioDia->update([
            'texto_extraido' => $datos['nombre'],
            'fecha' => $datos['fecha_inicio'],
            'categoria' => $datos['categoria']->value,
            'aplica_personal' => $datos['aplica_personal'],
            'aplica_estudiantes' => $datos['aplica_estudiantes'],
            'es_efemeride' => $datos['es_efemeride'],
            'color' => $datos['color']?->value,
            'mes_pagina' => (int) \Carbon\Carbon::parse($datos['fecha_inicio'])->format('n'),
        ]);

        return response()->json($this->serializarDia($calendarioDia));

    }

    /*
    elimina un dia puntual registrado manualmente. No se pueden registrar candidatos que vinieron del pdf desde aqui -
    esos se descartan desmarcando el checkbox en la vista de revision  , no borrandolos

     */

    public function eventoDestroy(CalendarioAcademico $calendarioAcademico, CalendarioDia $calendarioDia) : JsonResponse{
        abort_if($calendarioDia->calendario_id !== $calendarioAcademico->id,404);
        abort_if($calendarioDia->confianza !== \App\Enums\ConfianzaDia::Manual,403,'Solo se pueden eliminar eventos creados manualmente');

        $calendarioDia->delete();

        return response()->json(['eliminado' => true]);

    }



    private function validarDatosEvento(Request $request, CalendarioAcademico $calendarioAcademico, bool $esEdicionDeUnSoloDia = false): array
    {
        $anioEscolar = $calendarioAcademico->anioEscolar;

        $reglas = [
            // en el campo de nombre se aceptan Letras, números, espacios, paréntesis, comas y barras.
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s\(\),\/]+$/u'],
            'fecha_inicio' => [
                'required', 'date',
                'after_or_equal:' . $anioEscolar->inicio_anio_escolar->toDateString(),
                'before_or_equal:' . $anioEscolar->cierre_anio_escolar->toDateString(),
            ],
            'es_no_laborable' => ['required', 'boolean'],
            'es_efemeride' => ['required', 'boolean'],
            'aplica_a' => ['required_if:es_no_laborable,true', 'nullable', 'in:estudiantes,docentes,ambos'],
            'color' => ['nullable', \Illuminate\Validation\Rule::in(
                array_map(fn ($c) => $c->value, \App\Enums\ColorEvento::seleccionables())
            )],
        ];

        // El rango de fechas solo aplica al crear; al editar, se edita un
        // único día puntual .
        if (! $esEdicionDeUnSoloDia) {
            $reglas['fecha_fin'] = ['nullable', 'date', 'after_or_equal:fecha_inicio',
                'before_or_equal:' . $anioEscolar->cierre_anio_escolar->toDateString()];
        }

        $mensajes = [
            'nombre.required' => 'El nombre del evento es obligatorio.',
            'nombre.string' => 'El nombre del evento no es válido.',
            'nombre.max' => 'El nombre del evento no puede tener más de 255 caracteres.',
            'nombre.regex' => 'El nombre solo puede tener letras, números, espacios, paréntesis, comas y barras (/).',

            'fecha_inicio.required' => 'Debes indicar la fecha de inicio del evento.',
            'fecha_inicio.date' => 'La fecha de inicio no es una fecha válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior al inicio del año escolar (:date).',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio no puede ser posterior al cierre del año escolar (:date).',

            'fecha_fin.date' => 'La fecha de fin no es una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'fecha_fin.before_or_equal' => 'La fecha de fin no puede ser posterior al cierre del año escolar (:date).',

            'es_no_laborable.required' => 'Debes indicar si el día es laborable o no laborable.',
            'es_no_laborable.boolean' => 'El tipo de día no es válido.',

            'es_efemeride.required' => 'Debes indicar si el evento es una efeméride.',
            'es_efemeride.boolean' => 'El valor de efeméride no es válido.',

            'aplica_a.required_if' => 'Debes indicar a quién aplica el día no laborable: estudiantes, docentes o ambos.',
            'aplica_a.in' => 'La opción de "aplica a" seleccionada no es válida.',

            'color.in' => 'El color seleccionado no está disponible en la paleta.',
        ];

        $validado = $request->validate($reglas, $mensajes);

        $esNoLaborable = (bool) $validado['es_no_laborable'];
        $esEfemeride = (bool) $validado['es_efemeride'];
        $aplicaA = $validado['aplica_a'] ?? null;

        $aplicaPersonal = ! $esNoLaborable || in_array($aplicaA, ['docentes', 'ambos'], true);
        $aplicaEstudiantes = ! $esNoLaborable || in_array($aplicaA, ['estudiantes', 'ambos'], true);

        $colorElegido = isset($validado['color'])
            ? \App\Enums\ColorEvento::from($validado['color'])
            : null;

        return [
            'nombre' => $validado['nombre'],
            'fecha_inicio' => $validado['fecha_inicio'],
            'fecha_fin' => $validado['fecha_fin'] ?? $validado['fecha_inicio'],
            'categoria' => $esNoLaborable ? \App\Enums\CategoriaDia::NoLaborable : \App\Enums\CategoriaDia::Laborable,
            'aplica_personal' => $aplicaPersonal,
            'aplica_estudiantes' => $aplicaEstudiantes,
            'es_efemeride' => $esEfemeride,
            'color' => CalendarioDia::determinarColor($esNoLaborable, $aplicaPersonal, $aplicaEstudiantes, $esEfemeride, $colorElegido),
        ];
    }

    // genera la lista de fechas (formato y-m-d)

 private function expandirRangoDeFechas(string $inicio, string $fin): array
    {
        $fechas = [];
        $cursor = \Carbon\Carbon::parse($inicio);
        $final = \Carbon\Carbon::parse($fin);

        while ($cursor->lte($final)) {
            $fechas[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $fechas;
    }

    private function serializarDia(CalendarioDia $dia) : array {

    return[
        'id' => $dia->id,
        'fecha' => $dia->fecha->toDateString(),
        'texto' => $dia->texto_extraido,
        'categoria' => $dia->categoria->value,
        'categoriaLabel' => $dia->categoria->label(),
        'confianza' => $dia->confianza->value,
        'confianzaLabel' => $dia->confianza->label(),
        'confirmado' => $dia->confirmado,
        'esEfemeride' => $dia->es_efemeride,
        'colorHex' => $dia->color?->hex(),
        'colorValue'       => $dia->color?->value,
        'esManual' => true,
        'aplica_personal'    => $dia->aplica_personal,
        'aplica_estudiantes' => $dia->aplica_estudiantes,
        'mesPagina'          => $dia->mes_pagina,
    ];
    }

}
