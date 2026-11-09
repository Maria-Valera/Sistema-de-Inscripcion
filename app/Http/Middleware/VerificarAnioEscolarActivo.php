<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AnioEscolar;
use App\Support\AnioEscolarSeleccionado;
use Symfony\Component\HttpFoundation\Response;

class VerificarAnioEscolarActivo
{
    /**
     * Maneja la solicitud entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Excluir rutas relacionadas con Calendario Escolar
        $rutasExcluidas = [
            'admin.anio_escolar.*',
            'logout',
            'login',
        ];

        // Si la ruta actual está excluida, permitir acceso
        foreach ($rutasExcluidas as $patron) {
            if ($request->routeIs($patron)) {
                return $next($request);
            }
        }

        // Verificar si existe un Calendario Escolar seleccionado o activo en el sistema
        $anioEscolarId = AnioEscolarSeleccionado::id();

        if (!$anioEscolarId) {
            // Si es una petición AJAX (Livewire), retornar error JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Debe registrar o seleccionar un Calendario Escolar para continuar.'
                ], 403);
            }

            // Para solicitudes normales, redirigir con mensaje
            return redirect()
                ->route('admin.anio_escolar.index')
                ->with('warning', 'Debe registrar un Calendario Escolar antes de acceder a este módulo.');
        }

        return $next($request);
    }

    public function store(Request $request)
    {
        // Verificación adicional (opcional, el middleware ya lo hace)
        $anioEscolarActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])
            ->orWhere('status', 'Extendido')
            ->exists();

        if (!$anioEscolarActivo) {
            return redirect()
                ->route('admin.anio_escolar.index')
                ->with('warning', 'Debe registrar un Calendario Escolar activo.');
        }

        // ... resto del código existente
    }
}