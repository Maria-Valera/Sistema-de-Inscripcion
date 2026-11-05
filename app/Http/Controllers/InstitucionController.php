<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstitucionRequest;
use App\Models\Institucion;
use App\Models\Localidad;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:institucion.ver')->only(['index', 'show']);
        $this->middleware('permission:institucion.gestionar')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Muestra la lista de instituciones (datos del plantel).
     */
    public function index(): View
    {
        $instituciones = Institucion::with(['localidad.municipio.estado', 'director'])
            ->orderBy('activo', 'desc')
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        $localidades = Localidad::with('municipio.estado')
            ->where('status', 1)
            ->orderBy('nombre_localidad')
            ->get();

        $directores = Persona::where('status', 'Activo')
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        return view('admin.maestros.stephania.institucion.index', compact('instituciones', 'localidades', 'directores'));
    }

    /**
     * Muestra el formulario de creación (si se accede directamente).
     */
    public function create(): View
    {
        $localidades = Localidad::with('municipio.estado')->where('status', 1)->get();
        $directores = Persona::where('status', 'Activo')->get();

        return view('admin.maestros.stephania.institucion.index', compact('localidades', 'directores'));
    }

    /**
     * Almacena una nueva institución en la base de datos.
     */
    public function store(InstitucionRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $activo = $request->has('activo') ? $request->boolean('activo') : true;
        $datos['activo'] = $activo;
        $datos['director_encargado'] = $request->boolean('director_encargado');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos_plantel', 'public');
            $datos['logo_ruta'] = 'storage/' . $path;
        }

        DB::transaction(function () use ($datos, $activo) {
            // Regla de negocio: Si se registra una nueva activa, se desactivan las anteriores
            if ($activo) {
                Institucion::where('activo', true)->update(['activo' => false]);
            }

            Institucion::create($datos);
        });

        return redirect()->route('admin.institucion.index')
            ->with('success', 'Institución educativa registrada exitosamente.');
    }

    /**
     * Muestra los detalles de una institución (para respuesta AJAX/modal o vista).
     */
    public function show(Institucion $institucion)
    {
        $institucion->load(['localidad.municipio.estado', 'director']);

        if (request()->wantsJson()) {
            return response()->json($institucion);
        }

        return redirect()->route('admin.institucion.index');
    }

    /**
     * Retorna datos para edición vía JSON o redirige al index.
     */
    public function edit(Institucion $institucion)
    {
        $institucion->load(['localidad.municipio.estado', 'director']);

        if (request()->wantsJson()) {
            return response()->json($institucion);
        }

        return redirect()->route('admin.institucion.index');
    }

    /**
     * Actualiza los datos de la institución.
     */
    public function update(InstitucionRequest $request, Institucion $institucion): RedirectResponse
    {
        $datos = $request->validated();
        $activo = $request->boolean('activo');
        $datos['activo'] = $activo;
        $datos['director_encargado'] = $request->boolean('director_encargado');

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe en storage
            if ($institucion->logo_ruta && str_starts_with($institucion->logo_ruta, 'storage/logos_plantel/')) {
                $oldPath = str_replace('storage/', '', $institucion->logo_ruta);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('logos_plantel', 'public');
            $datos['logo_ruta'] = 'storage/' . $path;
        }

        DB::transaction(function () use ($institucion, $datos, $activo) {
            // Regla de negocio: Si se activa esta fila, se desactivan las demás
            if ($activo) {
                Institucion::where('activo', true)
                    ->where('id', '!=', $institucion->id)
                    ->update(['activo' => false]);
            }

            $institucion->update($datos);
        });

        return redirect()->route('admin.institucion.index')
            ->with('success', 'Datos del plantel actualizados exitosamente.');
    }

    /**
     * Elimina o desactiva la institución.
     */
    public function destroy(Institucion $institucion): RedirectResponse
    {
        // Regla: Desactivar en lugar de eliminar bruscamente para preservar la integridad
        $institucion->update(['activo' => false]);

        return redirect()->route('admin.institucion.index')
            ->with('success', 'La institución ha sido desactivada correctamente.');
    }
}

