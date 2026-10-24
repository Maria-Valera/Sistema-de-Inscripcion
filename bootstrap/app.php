<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias de middleware personalizados y de Spatie
        $middleware->alias([
            'verificar.anio.escolar' => \App\Http\Middleware\VerificarAnioEscolarActivo::class,
            'forzar.cambio.password' => \App\Http\Middleware\ForzarCambioPassword::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Agregar el middleware de verificación de años vencidos
        // Se ejecutará en TODAS las rutas autenticadas
        $middleware->append(\App\Http\Middleware\VerificarAniosVencidos::class);

        // Cabeceras de seguridad HTTP en todas las peticiones
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->appendToGroup('web', \App\Http\Middleware\ForzarCambioPassword::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            // Si intentó acceder a /home y el usuario tiene sesión activa, redirigir a su dashboard por rol en lugar de 403
            if ($request->is('home') && auth()->check() && !auth()->user()->hasRole('Admin')) {
                return redirect(auth()->user()->rutaDashboard());
            }

            // Registrar auditoría de acceso denegado
            try {
                \App\Models\Auditoria::registrar(
                    'acceso_denegado',
                    null,
                    null,
                    [
                        'permiso_requerido' => $e->getRequiredPermissions(),
                        'roles_requeridos' => $e->getRequiredRoles(),
                        'ruta' => $request->path(),
                        'metodo' => $request->method(),
                    ],
                    'Intento de acceso denegado a recurso protegido por permisos/roles'
                );
            } catch (\Throwable $th) {
                // Prevenir interrupciones
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. No posee los permisos requeridos.',
                    'permisos_requeridos' => $e->getRequiredPermissions(),
                ], 403);
            }

            return response()->view('errors.403', ['exception' => $e], 403);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                // Si la excepción no fue de Spatie (UnauthorizedException ya se maneja o hereda), registrar auditoría si no fue registrada aún
                if (!($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException)) {
                    try {
                        \App\Models\Auditoria::registrar(
                            'acceso_denegado',
                            null,
                            null,
                            [
                                'mensaje' => $e->getMessage() ?: 'Acceso denegado',
                                'ruta' => $request->path(),
                                'metodo' => $request->method(),
                            ],
                            'Acceso denegado por autorización (HTTP 403)'
                        );
                    } catch (\Throwable $th) {
                        // Prevenir interrupciones
                    }
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $e->getMessage() ?: 'Acceso denegado. No posee los permisos requeridos.',
                    ], 403);
                }

                return response()->view('errors.403', ['exception' => $e], 403);
            }
        });
    })->create();