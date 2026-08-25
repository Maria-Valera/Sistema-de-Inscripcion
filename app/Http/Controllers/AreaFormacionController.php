<?php

namespace App\Http\Controllers;

use App\Models\AreaFormacion;
use App\Models\GrupoEstable;
use Illuminate\Http\Request;

class AreaFormacionController extends Controller
{
    private function verificarAnioEscolar()
    {
        return \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index()
    {
        $areaFormacion = AreaFormacion::where('status', true)
            ->with('aula')
            ->orderBy('nombre_area_formacion', 'asc')
            ->paginate(5);

        $grupoEstable = GrupoEstable::where('status', true)
            ->orderBy('nombre_grupo_estable', 'asc')
            ->paginate(5);

        $aulas = \App\Models\Aula::where('status', true)
            ->orderBy('nombre_aula', 'asc')
            ->get();

        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.area_formacion.index', compact('areaFormacion', 'grupoEstable', 'aulas', 'anioEscolarActivo'));
    }

    public function storeGrupoEstable(Request $request)
    {
        $validated = $request->validate([
            'nombre_grupo_estable' => 'required|string|max:255',
            
        ]);

        $existe = GrupoEstable::where('nombre_grupo_estable', $validated['nombre_grupo_estable'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Ya existe un grupo estable activo con este nombre.');
        }

        try {
            $grupoEstable = new GrupoEstable();
            $grupoEstable->nombre_grupo_estable = $validated['nombre_grupo_estable'];
            
            $grupoEstable->status = true;
            $grupoEstable->save();

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('success', 'Grupo estable creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al crear el grupo estable: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_area_formacion' => 'required|string|max:255',
            'codigo_area' => 'required|numeric',
            'siglas' => 'required|string|max:255',
            'horas_semanales' => 'required|integer',
            'bloques_maximos_por_dia' => 'required|integer|min:1|max:8',
            'aula_id' => 'required|exists:aulas,id_aula',
        ]);

        $existe = AreaFormacion::where('nombre_area_formacion', $validated['nombre_area_formacion'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Ya existe un área de formación activa con este nombre.');
        }
 
        try {
            $areaFormacion = new AreaFormacion();
            $areaFormacion->nombre_area_formacion = $validated['nombre_area_formacion'];
            $areaFormacion->codigo_area = $validated['codigo_area'];
            $areaFormacion->siglas = $validated['siglas'];
            $areaFormacion->horas_semanales = $validated['horas_semanales'];
            $areaFormacion->bloques_maximos_por_dia = $validated['bloques_maximos_por_dia'];
            $areaFormacion->aula_id = $validated['aula_id'];
            $areaFormacion->status = true;
            $areaFormacion->save();

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('success', 'Área de formación creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al crear el área de formación: ' . $e->getMessage());
        }
    }

    public function updateGrupoEstable(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre_grupo_estable' => 'required|string|max:255',
        ]);

        $existe = GrupoEstable::where('nombre_grupo_estable', $validated['nombre_grupo_estable'])
            ->where('status', true)
            ->where('id', '!=', $id)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Ya existe otro grupo estable activo con este nombre.');
        }

        try {
            $grupoEstable = GrupoEstable::findOrFail($id);
            $grupoEstable->nombre_grupo_estable = $validated['nombre_grupo_estable'];
            $grupoEstable->save();

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('success', 'Grupo estable actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al actualizar el grupo estable: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $areaFormacion = AreaFormacion::findOrFail($id);

        $validated = $request->validate([
            'nombre_area_formacion' => 'required|string|max:255',
            'codigo_area' => 'required|numeric',
            'siglas' => 'required|string|max:255',
            'horas_semanales' => 'required|integer',
            'bloques_maximos_por_dia' => 'required|integer|min:1|max:8',
            'aula_id' => 'required|exists:aulas,id_aula',
        ]);

        $existe = AreaFormacion::where('nombre_area_formacion', $validated['nombre_area_formacion'])
            ->where('status', true)
            ->where('id', '!=', $areaFormacion->id)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Ya existe otra área de formación activa con este nombre.');
        }

        try {
            $areaFormacion->nombre_area_formacion = $validated['nombre_area_formacion'];
            $areaFormacion->codigo_area = $validated['codigo_area'];
            $areaFormacion->siglas = $validated['siglas'];
            $areaFormacion->horas_semanales = $validated['horas_semanales'];
            $areaFormacion->bloques_maximos_por_dia = $validated['bloques_maximos_por_dia'];
            $areaFormacion->aula_id = $validated['aula_id'];
            $areaFormacion->save();

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('success', 'Área de formación actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al actualizar el área de formación: ' . $e->getMessage());
        }
    }

    public function destroyGrupoEstable($id)
    {
        try {
            $grupoEstable = GrupoEstable::find($id);

            if ($grupoEstable) {
                $grupoEstable->update(['status' => false]);

                return redirect()
                    ->route('admin.area_formacion.index')
                    ->with('success', 'Grupo estable eliminado correctamente.');
            }

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Grupo estable no encontrado.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al eliminar el grupo estable: ' . $e->getMessage());
        }
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            $existe = AreaFormacion::where('nombre_area_formacion', $request->nombre)
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
                'message' => 'Error al verificar el área de formación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verificarExistenciaGrupoEstable(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            $existe = GrupoEstable::where('nombre_grupo_estable', $request->nombre)
                ->where('status', true)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en verificarExistenciaGrupoEstable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el grupo estable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $areaFormacion = AreaFormacion::with('aula')->find($id);


            if ($areaFormacion) {
                $areaFormacion->update(['status' => false]);
                

                return redirect()
                    ->route('admin.area_formacion.index')
                    ->with('success', 'Área de formación eliminada correctamente.');
            }

            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Área de formación no encontrada.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.area_formacion.index')
                ->with('error', 'Error al eliminar el área de formación: ' . $e->getMessage());
        }
    }
}
