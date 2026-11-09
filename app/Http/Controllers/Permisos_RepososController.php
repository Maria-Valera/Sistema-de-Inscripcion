<?php

namespace App\Http\Controllers;

use App\Models\Reposos_Permisos;
use Illuminate\Http\Request;
use App\Models\AnioEscolar;

class Permisos_RepososController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index()
    {
        $permisosReposos = Reposos_Permisos::orderBy('id', 'asc')
            ->paginate(10);

        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.permisos_reposos.index', compact('permisosReposos', 'anioEscolarActivo'));
    }

    public function create()
    {
        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.permisos_reposos.create', compact('anioEscolarActivo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_reposo' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'dias_reposo' => 'required|integer|min:1',
        ]);

        try {
            $permisoReposo = new Reposos_Permisos();
            $permisoReposo->nombre_reposo = $validated['nombre_reposo'];
            $permisoReposo->tipo = $validated['tipo'];
            $permisoReposo->dias_reposo = $validated['dias_reposo'];
            $permisoReposo->save();

            return redirect()
                ->route('admin.permisos_reposos.index')
                ->with('success', 'Permiso/Reposo creado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.permisos_reposos.index')
                ->with('error', 'Ocurrió un error al crear el permiso/reposo: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $permisoReposo = Reposos_Permisos::findOrFail($id);
        $validated = $request->validate([
            'nombre_reposo' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'dias_reposo' => 'required|integer|min:1',
        ]);

        try {
            $permisoReposo->nombre_reposo = $validated['nombre_reposo'];
            $permisoReposo->tipo = $validated['tipo'];
            $permisoReposo->dias_reposo = $validated['dias_reposo'];
            $permisoReposo->save();

            return redirect()
                ->route('admin.permisos_reposos.index')
                ->with('success', 'Permiso/Reposo actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.permisos_reposos.index')
                ->with('error', 'Ocurrió un error al actualizar el permiso/reposo: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $permisoReposo = Reposos_Permisos::find($id);

        if ($permisoReposo) {
            $permisoReposo->delete();

            return redirect()
                ->route('admin.permisos_reposos.index')
                ->with('success', 'Permiso/Reposo eliminado correctamente.');
        }

        return redirect()
            ->route('admin.permisos_reposos.index')
            ->with('error', 'El permiso/reposo no fue encontrado.');
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre_reposo' => 'required|string|max:255',
            ]);

            $existe = Reposos_Permisos::where('nombre_reposo', $request->nombre_reposo)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en verificarExistencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el permiso/reposo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
