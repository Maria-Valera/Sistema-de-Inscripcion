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

    /**
     * Muestra el formulario para subir el PDF de un año escolar.
     */
    public function create(): View
    {
        // Solo se ofrecen años escolares que todavía no tienen un calendario asociado,
        // porque la relación es de uno a uno (ver el ERD: ANIO_ESCOLARS ||--o| CALENDARIOS_ACADEMICOS).
        $aniosEscolaresDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->get();

        return view('calendario_academico.create', compact('aniosEscolaresDisponibles'));
    }

    /**
     * Recibe el PDF, ejecuta los algoritmos 1 al 6, y redirige a la
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
     * Pantalla de revisión humana: muestra los candidatos agrupados por mes,
     * con los de confianza "alta" pre-marcados y los "dudosos" sin marcar.
     */
    // public function show(CalendarioAcademico $calendarioAcademico): View
    // {
    //     $calendarioAcademico->load('anioEscolar');

    //     $candidatos = $calendarioAcademico->dias()
    //         ->orderBy('mes_pagina')
    //         ->orderBy('fecha')
    //         ->get()
    //         ->groupBy('mes_pagina');

    //     return view('calendario_academico.show', [
    //         'calendario' => $calendarioAcademico,
    //         'candidatosPorMes' => $candidatos,
    //     ]);
    // }

    public function show(CalendarioAcademico $calendarioAcademico):View{

        $calendarioAcademico->load('anioEscolar');

        $dias = $calendarioAcademico->dias()
        ->orderBy('mes_pagina')
        ->orderBy('fecha')
        ->get();

        $candidatosPorMes = $dias->groupBy('mes_pagina');

        // solo los que tienen una fecha real se excluye en este caso por el momento "mes completo"
        // ya que no tiene un dia puntual y no puede ubicarse en una celda sola del calendario

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
     * Ejecuta el algoritmo 7 (confirmarCalendario): marca como confirmados
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

}
