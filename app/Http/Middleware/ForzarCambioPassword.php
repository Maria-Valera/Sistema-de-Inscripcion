<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForzarCambioPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Si el usuario debe cambiar contraseña
            if ($user->debe_cambiar_password) {
                // Rutas excluidas del redirect (patrones de nombres de ruta)
                $rutasExcluidas = [
                    'login',
                    'password.request',
                    'password.email',
                    'password.reset',
                    'password.change',
                    'password.change.post',
                    'password.confirm',
                    'verification.notice',
                    'verification.verify',
                    'verification.resend',
                    'logout',
                    'default-livewire.update',
                    'livewire.upload-file',
                ];
                
                $rutaActual = optional($request->route())->getName();
                
                // Si no está en una ruta excluida, redirigir a cambio de contraseña
                if ($rutaActual && !in_array($rutaActual, $rutasExcluidas)) {
                    return redirect()->route('password.change');
                }
            }
        }

        return $next($request);
    }
}
