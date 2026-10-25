<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCalendario;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioDia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioAcademicoController extends Controller
{
    /**
     * Lista todos los calendarios cargados, con su año escolar y estado.
     */
    public function index(): View
    {
        $calendarios = CalendarioAcademico::with('anioEscolar')
            ->withCount('dias')
            ->latest()
            ->paginate(15);

        return view('calendario_academico.index', compact('calendarios'));
    }



    /**
     * Muestra el formulario para empezar un calendario 100% manual
     * — solo pide el año escolar, el resto se construye evento por evento.
     */
    public function createManual(): View{
        $aniosEscolaresDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->get();

        return view('calendario_academico.create-manual',compact('aniosEscolaresDisponibles'));
    }

    /**
     *  Entrega los días ya registrados para las vistas de Calendario y Semana.
     */
    public function show(CalendarioAcademico $calendarioAcademico):View{

        $calendarioAcademico->load('anioEscolar');

        $dias = $calendarioAcademico->dias()
        ->orderBy('fecha')
        ->get();

        // solo los que tienen una fecha real (todos los eventos manuales la tienen)

        $diasParaCalendario = $dias
        ->whereNotNull('fecha')
        ->map(fn (CalendarioDia $dia) => [
            'id' => $dia->id,
            'fecha' => $dia->fecha->toDateString(),
            'texto' => $dia->texto_extraido,
            'categoria' => $dia->categoria->value,
            'categoriaLabel' => $dia->categoria->label(),
            'esEfemeride' => $dia->es_efemeride,
            'colorHex' => $dia->color?->hex(),
            'colorValue'      => $dia->color?->value,
            'aplica_personal'    => $dia->aplica_personal,      // <-- nuevo
        'aplica_estudiantes' => $dia->aplica_estudiantes,   // <-- nuevo
        ])
        ->values();

        return view('calendario_academico.show',[
            'calendario' =>$calendarioAcademico,
            'diasParaCalendarioJson' => $diasParaCalendario->toJson(),
            'inicioAnioEscolar' => $calendarioAcademico->anioEscolar->inicio_anio_escolar,
            'cierreAnioEscolar' => $calendarioAcademico->anioEscolar->cierre_anio_escolar,
        ]);


    }

    /**
     * Crea un calendario_academico para un año escolar que todavía no tiene
     * ninguno. Como ya no existe revisión de candidatos de PDF, el
     * calendario nace directamente confirmado: cada evento manual se
     * confirma por su cuenta en el momento en que se registra.
     */
    public function storeManual(Request $request) : RedirectResponse {
        $validado = $request->validate([
            'anio_escolar_id' => ['required','exists:anio_escolars,id'],
        ]);

        $calendario = CalendarioAcademico::create([
            'anio_escolar_id' => $validado['anio_escolar_id'],
            'estado' => EstadoCalendario::Confirmado,
            'fecha_confirmacion' => now(),
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

        $fechas = $this->expandirRangoDeFechas($datos['fecha_inicio'],$datos['fecha_fin']);

        // Validar si ya existe un evento con el mismo texto en alguna de las fechas del rango
        foreach ($fechas as $fecha) {
            $existeDuplicado = $calendarioAcademico->dias()
                ->where('fecha', $fecha)
                ->where('texto_extraido', $datos['nombre'])
                ->exists();

            if ($existeDuplicado) {
                return response()->json([
                    'message' => "El evento \"{$datos['nombre']}\" ya está asignado para la fecha {$fecha}.",
                    'errors' => [
                        'nombre' => ["El evento \"{$datos['nombre']}\" ya está asignado para la fecha {$fecha}."]
                    ]
                ], 422);
            }
        }

        $creados = [];

        foreach($fechas as $fecha){
            $creados[] = CalendarioDia::create([
                'calendario_id' => $calendarioAcademico->id,
                'fecha' => $fecha,
                'texto_extraido' => $datos['nombre'],
                'categoria' => $datos['categoria']->value,
                'aplica_personal' => $datos['aplica_personal'],
                'aplica_estudiantes' => $datos['aplica_estudiantes'],
                'es_efemeride' => $datos['es_efemeride'],
                'color' => $datos['color']?->value,
            ]);

        }

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

        $calendarioAcademico->load('anioEscolar');
        $datos = $this->validarDatosEvento($request,$calendarioAcademico, esEdicionDeUnSoloDia : true);

        // Validar si ya existe otro evento con la misma fecha y descripción
        $existeDuplicado = $calendarioAcademico->dias()
            ->where('id', '!=', $calendarioDia->id)
            ->where('fecha', $datos['fecha_inicio'])
            ->where('texto_extraido', $datos['nombre'])
            ->exists();

        if ($existeDuplicado) {
            return response()->json([
                'message' => "El evento \"{$datos['nombre']}\" ya está asignado para la fecha {$datos['fecha_inicio']}.",
                'errors' => [
                    'nombre' => ["El evento \"{$datos['nombre']}\" ya está asignado para la fecha {$datos['fecha_inicio']}."]
                ]
            ], 422);
        }

        $calendarioDia->update([
            'texto_extraido' => $datos['nombre'],
            'fecha' => $datos['fecha_inicio'],
            'categoria' => $datos['categoria']->value,
            'aplica_personal' => $datos['aplica_personal'],
            'aplica_estudiantes' => $datos['aplica_estudiantes'],
            'es_efemeride' => $datos['es_efemeride'],
            'color' => $datos['color']?->value,
        ]);

        return response()->json($this->serializarDia($calendarioDia));

    }

    /*
    elimina un dia puntual registrado.
    */

    public function eventoDestroy(CalendarioAcademico $calendarioAcademico, CalendarioDia $calendarioDia) : JsonResponse{
        abort_if($calendarioDia->calendario_id !== $calendarioAcademico->id,404);

        $calendarioDia->delete();

        return response()->json(['eliminado' => true]);

    }



    private function validarDatosEvento(Request $request, CalendarioAcademico $calendarioAcademico, bool $esEdicionDeUnSoloDia = false): array
    {
        $anioEscolar = $calendarioAcademico->anioEscolar;

        $reglas = [
            // en el campo de nombre se aceptan Letras, números, espacios, paréntesis, comas, guiones, dos puntos, puntos y barras.
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s\(\),\/:\.\-]+$/u'],
            'fecha_inicio' => [
                'required', 'date',
                'after_or_equal:' . $anioEscolar->inicio_anio_escolar->copy()->startOfMonth()->toDateString(),
                'before_or_equal:' . $anioEscolar->cierre_anio_escolar->copy()->endOfMonth()->toDateString(),
            ],
            'es_no_laborable' => ['required', 'boolean'],
            'es_efemeride' => ['required', 'boolean'],
            'aplica_a' => ['required_if:es_no_laborable,1,true', 'nullable', 'in:estudiantes,docentes,ambos'],
            'color' => ['nullable', \Illuminate\Validation\Rule::in(
                array_map(fn ($c) => $c->value, \App\Enums\ColorEvento::seleccionables())
            )],
        ];

        // El rango de fechas solo aplica al crear; al editar, se edita un
        // único día puntual .
        if (! $esEdicionDeUnSoloDia) {
            $reglas['fecha_fin'] = ['nullable', 'date', 'after_or_equal:fecha_inicio',
                'before_or_equal:' . $anioEscolar->cierre_anio_escolar->copy()->endOfMonth()->toDateString()];
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
        'esEfemeride' => $dia->es_efemeride,
        'colorHex' => $dia->color?->hex(),
        'colorValue'       => $dia->color?->value,
        'aplica_personal'    => $dia->aplica_personal,
        'aplica_estudiantes' => $dia->aplica_estudiantes,
    ];
    }

}