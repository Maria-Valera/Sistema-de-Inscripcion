<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsistenteAnioRequest;
use App\Http\Requests\AsistenteLapsoRequest;
use App\Models\AnioEscolar;
use App\Models\Corte;
use App\Support\AsistenteAnioEscolar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// Asistente de año escolar nuevo: 1. año escolar → 2. lapso 1 → 3. calendario académico.
// Los pasos 1 y 2 solo guardan un borrador en la sesión; todo se crea junto al guardar el paso 3.
class AsistenteAnioEscolarController extends Controller
{
    // ----- Paso 1: año escolar -----

    public function anio(): View
    {
        $borrador = AsistenteAnioEscolar::borrador()['anio'] ?? [];
        $anioActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();

        return view('asistente_anio.anio', compact('borrador', 'anioActivo'));
    }

    public function guardarAnio(AsistenteAnioRequest $request): RedirectResponse
    {
        AsistenteAnioEscolar::guardarAnio($request->validated());

        return redirect()->route('admin.asistente.lapso');
    }

    // ----- Paso 2: lapso 1 -----

    public function lapso(): View|RedirectResponse
    {
        $borrador = AsistenteAnioEscolar::borrador();
        if (empty($borrador['anio'])) {
            return redirect()->route('admin.asistente.anio');
        }

        $anio = $borrador['anio'];
        $lapso = $borrador['lapso'] ?? AsistenteAnioEscolar::propuestaLapso($anio['inicio_anio_escolar'], $anio['cierre_anio_escolar']);
        $origen = AsistenteAnioEscolar::configuracionAnterior()?->anioEscolar;

        return view('asistente_anio.lapso', compact('anio', 'lapso', 'origen'));
    }

    public function guardarLapso(AsistenteLapsoRequest $request): RedirectResponse
    {
        if (empty(AsistenteAnioEscolar::borrador()['anio'])) {
            return redirect()->route('admin.asistente.anio');
        }

        AsistenteAnioEscolar::guardarLapso($request->validated());

        return redirect()->route('admin.asistente.calendario');
    }

    // ----- Paso 3: calendario y guardar todo -----

    public function calendario(): View|RedirectResponse
    {
        $borrador = AsistenteAnioEscolar::borrador();
        if (empty($borrador['anio'])) {
            return redirect()->route('admin.asistente.anio');
        }
        if (empty($borrador['lapso'])) {
            return redirect()->route('admin.asistente.lapso');
        }

        $anio = $borrador['anio'];
        $lapso = $borrador['lapso'];
        $cortes = Corte::CANTIDAD_POR_DEFECTO;
        $puntos = Corte::PUNTOS_POR_DEFECTO;

        return view('asistente_anio.calendario', compact('anio', 'lapso', 'cortes', 'puntos'));
    }

    public function guardar(): RedirectResponse
    {
        $borrador = AsistenteAnioEscolar::borrador();
        if (empty($borrador['anio']) || empty($borrador['lapso'])) {
            return redirect()->route('admin.asistente.anio')
                ->with('error', 'Faltan datos del año escolar o del lapso: revisa los pasos.');
        }

        $calendario = AsistenteAnioEscolar::crearTodo();
        AsistenteAnioEscolar::olvidar();

        // El sistema queda trabajando sobre el año recién creado.
        session(['anio_escolar_id' => $calendario->anio_escolar_id]);

        return redirect()
            ->route('admin.calendario_academico.show', $calendario)
            ->with('exito', '¡Listo! Se creó el año escolar con su lapso 1 (4 cortes de 20 puntos) y su calendario. Los demás lapsos se agregan en Lapsos; los días no laborables y efemérides, aquí en el calendario.');
    }
}
