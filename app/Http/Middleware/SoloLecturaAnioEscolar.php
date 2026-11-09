<?php

namespace App\Http\Middleware;

use App\Support\AnioEscolarSeleccionado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloLecturaAnioEscolar
{
    /**
     * Handle an incoming request.
     *
     * Si el año escolar seleccionado en sesión no es el actual y la petición intenta
     * modificar datos (POST, PUT, PATCH, DELETE), bloquea la acción con HTTP 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->esRutaExenta($request)) {
            return $next($request);
        }

        // Si el año escolar no es el actual (es un año anterior / de solo consulta)
        if (! AnioEscolarSeleccionado::esActual()) {
            // 1. Bloquear formularios directos de creación y edición (GET /create, GET /edit)
            if ($request->isMethod('GET') && (
                $request->routeIs('*.create') || 
                str_ends_with($request->path(), '/create') ||
                $request->routeIs('*.edit') ||
                str_ends_with($request->path(), '/edit')
            )) {
                abort(403, 'No puedes registrar ni editar datos en un año escolar anterior (solo lectura).');
            }

            // 2. Para peticiones Livewire (POST /livewire/update):
            // Permitir navegación/búsqueda/paginación, pero bloquear cualquier acción de guardado/modificación
            if ($this->esPeticionLivewire($request)) {
                if ($this->esPeticionEscrituraLivewire($request)) {
                    abort(403, 'No puedes modificar datos de un año escolar anterior.');
                }
                return $next($request);
            }

            // 3. Bloquear cualquier petición POST, PUT, PATCH o DELETE convencional
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                abort(403, 'No puedes modificar datos de un año escolar anterior.');
            }
        }

        return $next($request);
    }

    /**
     * Determina si la petición actual proviene de Livewire.
     */
    protected function esPeticionLivewire(Request $request): bool
    {
        return $request->hasHeader('X-Livewire')
            || str_contains($request->path(), 'livewire')
            || $request->routeIs('default-livewire.update')
            || $request->routeIs('livewire.*');
    }

    /**
     * Determina si una petición Livewire intenta ejecutar una acción que modifica datos.
     */
    protected function esPeticionEscrituraLivewire(Request $request): bool
    {
        $components = $request->input('components', []);

        foreach ($components as $component) {
            $calls = $component['calls'] ?? [];
            foreach ($calls as $call) {
                $method = $call['method'] ?? '';
                // Métodos permitidos de navegación/paginación/selección en solo lectura
                if (in_array($method, ['setPage', 'previousPage', 'nextPage', 'gotoPage', 'seleccionarAlumno'])) {
                    continue;
                }
                // Cualquier otro método invocado (save, guardar, eliminar, etc.) es escritura
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si la ruta actual está exenta de la restricción de solo lectura.
     */
    protected function esRutaExenta(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return $request->routeIs('admin.anio-escolar.*')
            || $request->routeIs('logout')
            || $request->routeIs('password.change*')
            || $routeName === 'logout'
            || $routeName === 'password.change'
            || $routeName === 'password.change.post'
            || $request->is('logout')
            || $request->is('password/change*')
            || $request->is('admin/seleccionar-anio*');
    }
}

