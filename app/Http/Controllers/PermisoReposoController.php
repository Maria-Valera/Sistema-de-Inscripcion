<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermisoReposoController extends Controller
{
    public function index()
    {
        return view('admin.permisos_reposos.index', [
            'solicitudes' => $this->solicitudesDemo(),
        ]);
    }

    public function create()
    {
        return view('admin.permisos_reposos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_usuario' => 'required|in:estudiante,administrativo,obrero',
            'persona_busqueda' => 'required|string|max:255',
            'tipo_solicitud' => 'required|in:Permiso,Reposo Médico',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'required|string|max:1000',
            'justificativo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        return redirect()
            ->route('admin.permisos_reposos.index')
            ->with('success', 'Solicitud registrada correctamente. Pendiente de revisión.');
    }

    private function solicitudesDemo(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Ana Maria Valera',
                'cedula' => '24555888',
                'tipo_usuario' => 'Estudiante',
                'tipo_solicitud' => 'Reposo Médico',
                'fecha_inicio' => '01/07/2026',
                'fecha_fin' => '05/07/2026',
                'fechas' => '01/07/2026 al 05/07/2026',
                'estado' => 'Aprobado',
                'motivo' => 'Presentó cuadro de dengue clásico verificado por examen de laboratorio.',
                'archivo' => 'reposo_dengue.pdf',
            ],
            [
                'id' => 2,
                'nombre' => 'Carlos Mendoza',
                'cedula' => '18222333',
                'tipo_usuario' => 'Administrativo',
                'tipo_solicitud' => 'Permiso',
                'fecha_inicio' => '08/07/2026',
                'fecha_fin' => '09/07/2026',
                'fechas' => '08/07/2026 al 09/07/2026',
                'estado' => 'Pendiente',
                'motivo' => 'Asistencia a cita del seguro social para consulta oftalmológica.',
                'archivo' => 'cita_ivss.pdf',
            ],
            [
                'id' => 3,
                'nombre' => 'Pedro Perez',
                'cedula' => '12444555',
                'tipo_usuario' => 'Obrero',
                'tipo_solicitud' => 'Permiso',
                'fecha_inicio' => '25/06/2026',
                'fecha_fin' => '25/06/2026',
                'fechas' => '25/06/2026 al 25/06/2026',
                'estado' => 'Rechazado',
                'motivo' => 'Asuntos familiares no justificados formalmente.',
                'archivo' => null,
            ],
        ];
    }
}
