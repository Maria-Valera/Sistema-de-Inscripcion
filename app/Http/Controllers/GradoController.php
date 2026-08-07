<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;
use App\Models\AnioEscolar;

class GradoController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index()
    {
        $grados = Grado::withCount('inscripciones')
            ->where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->paginate(10);

        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.grado.index', compact('grados', 'anioEscolarActivo'));
    }

    public function apiIndex(){
        $registros= Grado::where('status', true)
        ->get();
        
        return response()->json($registros);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_grado' => 'required|digits_between:1,4',
            'capacidad_max' => 'required|digits_between:1,3',
            'min_seccion' => 'required|digits_between:1,2',
            'max_seccion' => 'required|digits_between:1,2',
        ]);

        $existe = Grado::where('numero_grado', $validated['numero_grado'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'Ya existe un nivel academico con el mismo número.');
        }

        try {
            $grado = new Grado();
            $grado->numero_grado = $validated['numero_grado'];
            $grado->capacidad_max = $validated['capacidad_max'];
            $grado->min_seccion = $validated['min_seccion'];
            $grado->max_seccion = $validated['max_seccion'];
            $grado->status = true;
            $grado->save();

            return redirect()
                ->route('admin.grado.index')
                ->with('success', 'El nivel academico fue creado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'Ocurrió un error al crear el nivel academico: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $grado = Grado::findOrFail($id);

        $validated = $request->validate([
            'numero_grado' => 'required|digits_between:1,4' . $grado->id,
            'capacidad_max' => 'required|digits_between:1,3',
            'min_seccion' => 'required|digits_between:1,2',
            'max_seccion' => 'required|digits_between:1,2',
        ]);

        $existe = Grado::where('numero_grado', $validated['numero_grado'])
            ->where('id', '!=', $grado->id)
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'Ya existe un nivel academico con el mismo número.');
        }

        try {
            $grado->numero_grado = $validated['numero_grado'];
            $grado->capacidad_max = $validated['capacidad_max'];
            $grado->min_seccion = $validated['min_seccion'];
            $grado->max_seccion = $validated['max_seccion'];
            $grado->save();

            return redirect()
                ->route('admin.grado.index')
                ->with('success', 'El nivel academico fue actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'Ocurrió un error al actualizar el nivel academico: ' . $e->getMessage());
        }
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'numero_grado' => 'required|numeric|digits_between:1,4',
            ]);

            $existe = Grado::where('numero_grado', $request->numero_grado)
                ->where('status', true)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en verificarExistencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el grado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $grado = Grado::find($id);

        if (!$grado) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'No se encontró el nivel académico.');
        }

        $tieneEstudiantes = $grado->inscripciones()->exists();

        if ($tieneEstudiantes) {
            return redirect()
                ->route('admin.grado.index')
                ->with('error', 'No se puede inactivar este nivel académico porque tiene estudiantes inscritos.');
        }

        $grado->update(['status' => false]);

        return redirect()
            ->route('admin.grado.index')
            ->with('success', 'El nivel académico fue inactivado correctamente.');
    }
}
