<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSerialTitulo;
use App\Http\Requests\TituloSerialStoreRequest;
use App\Http\Requests\TituloSerialUpdateRequest;
use App\Models\TituloSerial;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// M6. Seriales de título que entrega la Zona Educativa para el año escolar seleccionado.
class TituloSerialController extends Controller
{
    public function index(Request $request): View
    {
        $anio = AnioEscolarSeleccionado::modelo();
        $estado = EstadoSerialTitulo::tryFrom((string) $request->query('estado'));
        $buscar = strtoupper(trim((string) $request->query('buscar')));

        $base = TituloSerial::where('anio_escolar_id', $anio?->id);

        // Cuántos hay en cada estado, para las tarjetas de arriba.
        $conteo = (clone $base)->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        $seriales = (clone $base)
            ->when($estado, fn ($q) => $q->where('estado', $estado->value))
            ->when($buscar !== '', fn ($q) => $q->where('serial', 'like', "%{$buscar}%"))
            ->orderBy('serial')
            ->paginate(15)
            ->withQueryString();

        // Todos los seriales ya registrados (de cualquier año): la validación en tiempo real revisa que no se repitan.
        $registrados = TituloSerial::pluck('serial');

        return view('admin.titulo_seriales.index', compact('anio', 'estado', 'buscar', 'conteo', 'seriales', 'registrados'));
    }

    public function store(TituloSerialStoreRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        foreach ($datos['lista'] as $serial) {
            TituloSerial::create([
                'anio_escolar_id' => $datos['anio_escolar_id'],
                'serial' => $serial,
                'fecha_recepcion' => $datos['fecha_recepcion'] ?? null,
                'estado' => EstadoSerialTitulo::Disponible,
            ]);
        }

        $total = count($datos['lista']);

        return redirect()
            ->route('admin.titulo_seriales.index')
            ->with('success', $total === 1 ? 'Se registró 1 serial.' : "Se registraron {$total} seriales.");
    }

    public function update(TituloSerialUpdateRequest $request, TituloSerial $tituloSerial): RedirectResponse
    {
        $datos = $request->validated();
        $estado = EstadoSerialTitulo::from($datos['estado']);

        // El motivo solo se guarda si se anula o devuelve; la fecha de devolución, solo si se devuelve.
        if (! in_array($estado, [EstadoSerialTitulo::Anulado, EstadoSerialTitulo::Devuelto], true)) {
            $datos['motivo_anulacion'] = null;
        }
        if ($estado !== EstadoSerialTitulo::Devuelto) {
            $datos['fecha_liquidacion'] = null;
        }

        $tituloSerial->update($datos);

        // back(): vuelve a la misma página, con el filtro y la búsqueda que tenía.
        return back()->with('success', "Serial {$tituloSerial->serial} actualizado correctamente.");
    }

    public function destroy(TituloSerial $tituloSerial): RedirectResponse
    {
        // Solo se borra un serial disponible (por ejemplo, uno mal escrito). Los demás son historial.
        if ($tituloSerial->estado !== EstadoSerialTitulo::Disponible) {
            return back()->with('error', "No se puede eliminar el serial {$tituloSerial->serial}: está {$tituloSerial->estado->label()}. Solo se eliminan seriales disponibles.");
        }

        $tituloSerial->delete();

        return back()->with('success', "Serial {$tituloSerial->serial} eliminado.");
    }
}
