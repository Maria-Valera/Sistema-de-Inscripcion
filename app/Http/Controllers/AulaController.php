<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre_aula' => 'required|string|max:100',
            ]);

            $query = Aula::where('nombre_aula', $request->nombre_aula)
                         ->where('status', true);

            if ($request->filled('id_aula')) {
                $query->where('id_aula', '!=', $request->id_aula);
            }

            return response()->json([
                'success' => true,
                'existe'  => $query->exists(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el aula',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $aulas = Aula::where('status', true)->paginate(10);
        return view('aulas.index', compact('aulas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_aula' => 'required|string|max:100',
        ], [
            'nombre_aula.required' => 'El nombre del aula es obligatorio.',
            'nombre_aula.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $existe = Aula::where('nombre_aula', $request->nombre_aula)
                      ->where('status', true)
                      ->exists();

        if ($existe) {
            return redirect()->route('aulas.index')
                ->with('error', 'Ya existe un aula activa con ese nombre.');
        }

        Aula::create([
            'nombre_aula' => $request->nombre_aula,
            'status'      => true,
        ]);

        return redirect()->route('aulas.index')
            ->with('success', 'Aula creada correctamente.');
    }

    public function update(Request $request, Aula $aula)
    {
        $request->validate([
            'nombre_aula' => 'required|string|max:100',
        ], [
            'nombre_aula.required' => 'El nombre del aula es obligatorio.',
            'nombre_aula.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $existe = Aula::where('nombre_aula', $request->nombre_aula)
                      ->where('status', true)
                      ->where('id_aula', '!=', $aula->id_aula)
                      ->exists();

        if ($existe) {
            return redirect()->route('aulas.index')
                ->with('error', 'Ya existe un aula activa con ese nombre.');
        }

        $aula->update(['nombre_aula' => $request->nombre_aula]);

        return redirect()->route('aulas.index')
            ->with('success', 'Aula actualizada correctamente.');
    }

    public function destroy(Aula $aula)
    {
        $aula->update(['status' => false]);

        return redirect()->route('aulas.index')
            ->with('success', 'Aula desactivada correctamente.');
    }

    public function create()  { return redirect()->route('aulas.index'); }
    public function show(Aula $aula) { return redirect()->route('aulas.index'); }
    public function edit(Aula $aula) { return redirect()->route('aulas.index'); }
}
