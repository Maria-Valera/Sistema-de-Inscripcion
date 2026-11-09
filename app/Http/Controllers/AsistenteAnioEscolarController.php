<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCalendario;
use App\Enums\EstadoLapso;
use App\Http\Requests\AsistenteLapsosRequest;
use App\Models\CalendarioAcademico;
use App\Models\Lapso;
use App\Support\AsistenteAnioEscolar;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Asistente de año escolar nuevo: paso 2 (lapsos) y paso 3 (calendario académico).
// El paso 1 (crear el año) es la pantalla de Stephania.
class AsistenteAnioEscolarController extends Controller
{
    public function lapsos(): View|RedirectResponse
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        if (! $anio || AsistenteAnioEscolar::pasoPendiente($anio) !== 'admin.asistente.lapsos') {
            return $this->siguiente();
        }

        // El asistente trabaja sobre el año que prepara, aunque al entrar se haya elegido otro en el selector.
        session(['anio_escolar_id' => $anio->id]);

        $config = AsistenteAnioEscolar::asegurarConfiguracion($anio);
        $origen = AsistenteAnioEscolar::anioDeOrigen($anio);
        $propuesta = AsistenteAnioEscolar::propuestaLapsos($anio, $config->num_lapsos);

        return view('asistente_anio.lapsos', compact('anio', 'config', 'origen', 'propuesta'));
    }

    public function guardarLapsos(AsistenteLapsosRequest $request): RedirectResponse
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        if (! $anio || AsistenteAnioEscolar::pasoPendiente($anio) !== 'admin.asistente.lapsos') {
            return $this->siguiente();
        }

        $config = AsistenteAnioEscolar::asegurarConfiguracion($anio);

        DB::transaction(function () use ($request, $anio, $config) {
            foreach (array_values($request->validated('lapsos')) as $i => $datos) {
                $lapso = Lapso::create([
                    'anio_escolar_id' => $anio->id,
                    'calendario_academico_id' => CalendarioAcademico::where('anio_escolar_id', $anio->id)->value('id'),
                    'numero' => $i + 1,
                    'nombre' => $datos['nombre'],
                    'fecha_inicio' => $datos['fecha_inicio'],
                    'fecha_fin' => $datos['fecha_fin'],
                    'fecha_limite_carga' => Carbon::parse($datos['fecha_limite_carga'])->endOfDay(),
                    'estado' => EstadoLapso::Planificado,
                ]);

                AsistenteAnioEscolar::crearCortes($lapso, $config);
            }
        });

        return redirect()
            ->route('admin.asistente.calendario')
            ->with('success', "Se crearon {$config->num_lapsos} lapsos con {$config->num_cortes} cortes cada uno.");
    }

    public function calendario(): View|RedirectResponse
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        if (! $anio || AsistenteAnioEscolar::pasoPendiente($anio) !== 'admin.asistente.calendario') {
            return $this->siguiente();
        }

        // El asistente trabaja sobre el año que prepara, aunque al entrar se haya elegido otro en el selector.
        session(['anio_escolar_id' => $anio->id]);

        $lapsos = Lapso::where('anio_escolar_id', $anio->id)->orderBy('numero')->get();

        return view('asistente_anio.calendario', compact('anio', 'lapsos'));
    }

    public function guardarCalendario(): RedirectResponse
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        if (! $anio || AsistenteAnioEscolar::pasoPendiente($anio) !== 'admin.asistente.calendario') {
            return $this->siguiente();
        }

        // Solo el año que se acaba de crear: no se recibe el año del formulario.
        $calendario = CalendarioAcademico::create([
            'anio_escolar_id' => $anio->id,
            'estado' => EstadoCalendario::Confirmado,
            'fecha_confirmacion' => now(),
        ]);

        Lapso::where('anio_escolar_id', $anio->id)->update(['calendario_academico_id' => $calendario->id]);
        AsistenteAnioEscolar::terminar($anio);

        return redirect()
            ->route('admin.calendario_academico.show', $calendario)
            ->with('exito', '¡Listo! El año escolar quedó preparado: sus lapsos ya se ven en el calendario. Ahora puedes agregar los días no laborables y efemérides.');
    }

    /** Si el paso ya está hecho (o no hay año activo), manda al que falta o al sistema. */
    private function siguiente(): RedirectResponse
    {
        $anio = AsistenteAnioEscolar::anioActivo();
        $paso = $anio ? AsistenteAnioEscolar::pasoPendiente($anio) : null;

        return $paso ? redirect()->route($paso) : redirect()->to(auth()->user()->rutaDashboard());
    }
}
