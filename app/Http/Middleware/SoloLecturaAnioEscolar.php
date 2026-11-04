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
        // Solo aplica a métodos de escritura
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if ($this->esRutaExenta($request)) {
                return $next($request);
            }

            if (! AnioEscolarSeleccionado::esActual()) {
                abort(403, 'No puedes modificar datos de un año anterior.');
            }
        }

        return $next($request);
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

