<?php

namespace App\Http\Middleware;

use App\Support\AsistenteAnioEscolar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el año activo todavía no tiene lapsos o calendario, manda a quien puede crearlos al paso que falta
 * del asistente de año nuevo. Así, si cerró el navegador a mitad, al volver retoma donde quedó.
 */
class AsistenteAnioEscolarPendiente
{
    public function handle(Request $request, Closure $next): Response
    {
        $ruta = $request->route()?->getName() ?? '';

        $aplica = $request->isMethod('GET')
            && ! $request->ajax()
            && auth()->check()
            // Solo las pantallas del sistema (los paneles y todo lo de /admin)...
            && ($ruta === 'home' || str_starts_with($ruta, 'dashboard.') || str_starts_with($ruta, 'admin.'))
            // ...menos el propio asistente y las pantallas de año escolar de Stephania (seleccionar y crear).
            && ! str_starts_with($ruta, 'admin.asistente.')
            && ! str_starts_with($ruta, 'admin.anio-escolar.');

        if (! $aplica) {
            return $next($request);
        }

        // Solo quien puede crear lapsos y calendario hace el asistente; los demás entran normal.
        $usuario = $request->user();
        if (! $usuario->can('lapsos.gestionar') || ! $usuario->can('calendario_academico.gestionar')) {
            return $next($request);
        }

        $anio = AsistenteAnioEscolar::anioActivo();
        $paso = $anio ? AsistenteAnioEscolar::pasoPendiente($anio) : null;

        return $paso ? redirect()->route($paso) : $next($request);
    }
}
