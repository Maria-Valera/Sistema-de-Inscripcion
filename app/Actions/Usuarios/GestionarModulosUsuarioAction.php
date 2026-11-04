<?php

namespace App\Actions\Usuarios;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class GestionarModulosUsuarioAction
{
    /**
     * Asigna permisos específicos como permisos directos a un usuario.
     *
     * @param User $user
     * @param array|string $permisos Array de nombres de permisos (o nombre de módulo por retrocompatibilidad)
     * @return void
     * @throws AuthorizationException
     */
    public function asignar(User $user, array|string $permisos): void
    {
        $this->autorizar();

        if (is_string($permisos)) {
            $permisos = config("modulos.{$permisos}.permisos", [$permisos]);
        }

        $permisos = array_filter(array_unique((array) $permisos));

        if (empty($permisos)) {
            return;
        }

        // Asegurar que los permisos existan en la base de datos
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $directosAntes = $this->obtenerPermisosDirectos($user);

        $user->givePermissionTo($permisos);

        $directosDespues = $this->obtenerPermisosDirectos($user);

        Auditoria::registrar(
            'modulo_asignado',
            $user,
            ['permisos_directos' => $directosAntes],
            ['permisos_directos' => $directosDespues],
            "Permisos asignados al usuario {$user->email}: " . implode(', ', $permisos)
        );
    }

    /**
     * Retira permisos directos a un usuario.
     * Devuelve true si el usuario aún conserva acceso a alguno de esos permisos (ej. vía roles),
     * o false si perdió totalmente dichos permisos.
     *
     * @param User $user
     * @param array|string $permisos Array de nombres de permisos (o nombre de módulo por retrocompatibilidad)
     * @return bool
     * @throws AuthorizationException
     */
    public function retirar(User $user, array|string $permisos): bool
    {
        $this->autorizar();

        if (is_string($permisos)) {
            $permisos = config("modulos.{$permisos}.permisos", [$permisos]);
        }

        $permisos = array_filter(array_unique((array) $permisos));

        if (empty($permisos)) {
            return false;
        }

        $directosAntes = $this->obtenerPermisosDirectos($user);

        // Retirar solo los permisos que el usuario tenga asignados directamente
        $permisosARetirar = array_values(array_intersect($directosAntes, $permisos));

        if (!empty($permisosARetirar)) {
            $user->revokePermissionTo($permisosARetirar);
        }

        $directosDespues = $this->obtenerPermisosDirectos($user);

        Auditoria::registrar(
            'modulo_retirado',
            $user,
            ['permisos_directos' => $directosAntes],
            ['permisos_directos' => $directosDespues],
            "Permisos retirados al usuario {$user->email}: " . implode(', ', $permisosARetirar)
        );

        // Comprobar si el usuario aún conserva acceso a alguno de los permisos evaluados vía roles
        return $this->usuarioConservaPermisos($user, $permisos);
    }

    /**
     * Valida que el usuario autenticado tenga el permiso 'usuarios.gestionar_modulos'.
     *
     * @throws AuthorizationException
     */
    protected function autorizar(): void
    {
        $auth = Auth::user();

        if (!$auth || !$auth->can('usuarios.gestionar_modulos')) {
            throw new AuthorizationException('No tienes permiso para gestionar módulos de usuarios.');
        }
    }

    /**
     * Obtiene el listado de nombres de permisos directos ordenados.
     *
     * @param User $user
     * @return array
     */
    protected function obtenerPermisosDirectos(User $user): array
    {
        $user->unsetRelation('permissions');

        return $user->getDirectPermissions()
            ->pluck('name')
            ->values()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Verifica si el usuario conserva acceso a cualquiera de los permisos indicados (vía roles o permisos remanentes).
     *
     * @param User $user
     * @param array $permisos
     * @return bool
     */
    protected function usuarioConservaPermisos(User $user, array $permisos): bool
    {
        if (empty($permisos)) {
            return false;
        }

        $user->unsetRelation('permissions');
        $user->unsetRelation('roles');

        $todosPermisos = $user->getAllPermissions()
            ->pluck('name')
            ->flip();

        foreach ($permisos as $permiso) {
            if (isset($todosPermisos[$permiso])) {
                return true;
            }
        }

        return false;
    }
}
