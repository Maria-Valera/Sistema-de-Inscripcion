<?php

namespace App\Http\Controllers;

use App\Enums\ConfianzaDia;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Services\CalendarioPdfExtractorService;
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

        return view('calendario-academico.index', compact('calendarios'));
    }

    /**
     * Muestra el formulario para subir el PDF de un año escolar.
     */
    public function create(): View
    {
        // Solo se ofrecen años escolares que todavía no tienen un calendario asociado,
        // porque la relación es de uno a uno (ver el ERD: ANIO_ESCOLARS ||--o| CALENDARIOS_ACADEMICOS).
        $aniosEscolaresDisponibles = AnioEscolar::whereDoesntHave('calendarioAcademico')->get();

        return view('calendario-academico.create', compact('aniosEscolaresDisponibles'));
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
    public function show(CalendarioAcademico $calendarioAcademico): View
    {
        $calendarioAcademico->load('anioEscolar');

        $candidatos = $calendarioAcademico->dias()
            ->orderBy('mes_pagina')
            ->orderBy('fecha')
            ->get()
            ->groupBy('mes_pagina');

        return view('calendario-academico.show', [
            'calendario' => $calendarioAcademico,
            'candidatosPorMes' => $candidatos,
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
}
