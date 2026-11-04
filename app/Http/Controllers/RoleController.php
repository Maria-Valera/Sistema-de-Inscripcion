<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Verifica si hay un Calendario Escolar activo
     */
    private function verificarAnioEscolar()
    {
        return \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->orderBy('name', 'asc')
            ->paginate(10);

        // Verificar si hay Calendario Escolar activo
        $anioEscolarActivo = $this->verificarAnioEscolar();

        // Cargar módulos configurados del sistema
        $modulos = config('modulos', []);

        return view('admin.roles.index', compact('roles', 'anioEscolarActivo', 'modulos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles,name|max:255',
            'permisos_seleccionados' => 'nullable|array',
            'permisos_seleccionados.*' => 'string',
        ]);

        try {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            $permisos = $request->input('permisos_seleccionados', []);
            if (!empty($permisos)) {
                foreach ($permisos as $permiso) {
                    Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
                }
                $role->syncPermissions($permisos);
            }

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol creado correctamente con sus permisos.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Error al crear el rol: ' . $e->getMessage());
        }    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:roles,name,' . $id,
            'permisos_seleccionados' => 'nullable|array',
            'permisos_seleccionados.*' => 'string',
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->name = $validated['name'];
            $role->save();

            $permisos = $request->input('permisos_seleccionados', []);
            if (!empty($permisos)) {
                foreach ($permisos as $permiso) {
                    Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
                }
            }
            $role->syncPermissions($permisos);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Error al actualizar el rol: ' . $e->getMessage());
        }    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);

            // Prevenir eliminar el rol Admin por seguridad del sistema
            if ($role->name === 'Admin') {
                return redirect()
                    ->route('admin.roles.index')
                    ->with('error', 'El rol principal Admin no puede ser eliminado.');
            }

            $role->delete();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }    
    }
}
