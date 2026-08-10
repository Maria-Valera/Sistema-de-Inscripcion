<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnioEscolar;
use App\Models\seccion_aula;
use App\Models\Seccion;
use App\Models\Aula;

class Seccion_aulaController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index()
    {
        $seccionAulas = seccion_aula::with(['seccion.grado', 'aula'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        $anioEscolarActivo = $this->verificarAnioEscolar();
        return view('admin.asignar_seccion_aula.index', compact('seccionAulas', 'anioEscolarActivo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_grado' => 'required|exists:grados,id',
            'id_seccion' => 'required|exists:seccions,id',
            'id_aula' => 'required|exists:aulas,id_aula',
        ]);

        $existe = seccion_aula::where('id_seccion', $validated['id_seccion'])
            ->where('id_aula', $validated['id_aula'])
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Ya existe esta asignación de aula a sección.');
        }

        try {
            $seccionAula = new seccion_aula();
            $seccionAula->id_seccion = $validated['id_seccion'];
            $seccionAula->id_aula = $validated['id_aula'];
            $seccionAula->save();

            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('success', 'Asignación creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Error al crear la asignación: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $seccionAula = seccion_aula::findOrFail($id);
        
        $validated = $request->validate([
            'id_grado' => 'required|exists:grados,id',
            'id_seccion' => 'required|exists:seccions,id',
            'id_aula' => 'required|exists:aulas,id_aula',
        ]);

        $existe = seccion_aula::where('id_seccion', $validated['id_seccion'])
            ->where('id_aula', $validated['id_aula'])
            ->where('id', '!=', $seccionAula->id)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Ya existe esta asignación de aula a sección.');
        }

        try {
            $seccionAula->id_seccion = $validated['id_seccion'];
            $seccionAula->id_aula = $validated['id_aula'];
            $seccionAula->save();

            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('success', 'Asignación actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Error al actualizar la asignación: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $seccionAula = seccion_aula::find($id);
            if ($seccionAula) {
                $seccionAula->delete();
                return redirect()
                    ->route('admin.seccion_aula.index')
                    ->with('success', 'Asignación eliminada correctamente.');
            }
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Asignación no encontrada.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.seccion_aula.index')
                ->with('error', 'Error al eliminar la asignación: ' . $e->getMessage());
        }
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'id_seccion' => 'nullable|exists:seccions,id',
                'id_aula' => 'nullable|exists:aulas,id_aula',
            ]);

            $query = seccion_aula::query();

            if ($request->has('id_seccion') && $request->id_seccion) {
                $query->where('id_seccion', $request->id_seccion);
            }

            if ($request->has('id_aula') && $request->id_aula) {
                $query->where('id_aula', $request->id_aula);
            }

            $existe = $query->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe,
                'mensaje' => $existe ? 'Ya existe esta asignación.' : ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar la asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function seccionesPorGrado($gradoId)
    {
        try {
            $secciones = Seccion::where('grado_id', $gradoId)
                ->get(['id', 'nombre']);
            
            return response()->json([
                'success' => true,
                'secciones' => $secciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener secciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
