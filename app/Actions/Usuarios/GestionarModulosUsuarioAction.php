<?php

namespace App\Actions\Usuarios;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class GestionarModulosUsuarioAction
{
    /**
     * Asigna los permisos de un módulo como permisos directos a un usuario.
     *
     * @param User $user
     * @param string $modulo
     * @return void
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     */
    public function asignar(User $user, string $modulo): void
    {
        $this->autorizar();

        $permisosModulo = config("modulos.{$modulo}.permisos");

        if (!is_array($permisosModulo)) {
            throw new InvalidArgumentException("El módulo '{$modulo}' no existe o no tiene permisos definidos en config/modulos.php.");
        }

        $directosAntes = $this->obtenerPermisosDirectos($user);

        if (!empty($permisosModulo)) {
            $user->givePermissionTo($permisosModulo);
        }

        $directosDespues = $this->obtenerPermisosDirectos($user);

        Auditoria::registrar(
            'modulo_asignado',
            $user,
            ['permisos_directos' => $directosAntes],
            ['permisos_directos' => $directosDespues],
            "Módulo '{$modulo}' asignado al usuario {$user->email}"
        );
    }

    /**
     * Retira los permisos directos de un módulo a un usuario.
     * Devuelve true si el usuario aún conserva acceso al módulo (por ejemplo, vía roles),
     * o false si perdió totalmente el acceso a dicho módulo.
     *
     * @param User $user
     * @param string $modulo
     * @return bool
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     */
    public function retirar(User $user, string $modulo): bool
    {
        $this->autorizar();

        $permisosModulo = config("modulos.{$modulo}.permisos");

        if (!is_array($permisosModulo)) {
            throw new InvalidArgumentException("El módulo '{$modulo}' no existe o no tiene permisos definidos en config/modulos.php.");
        }

        $directosAntes = $this->obtenerPermisosDirectos($user);

        // Retirar solo los permisos que el usuario tenga asignados directamente de este módulo
        $permisosARetirar = array_values(array_intersect($directosAntes, $permisosModulo));

        if (!empty($permisosARetirar)) {
            $user->revokePermissionTo($permisosARetirar);
        }

        $directosDespues = $this->obtenerPermisosDirectos($user);

        Auditoria::registrar(
            'modulo_retirado',
            $user,
            ['permisos_directos' => $directosAntes],
            ['permisos_directos' => $directosDespues],
            "Módulo '{$modulo}' retirado al usuario {$user->email}"
        );

        // Comprobar si el usuario aún conserva el módulo (por ejemplo, por roles o permisos remanentes)
        return $this->usuarioConservaModulo($user, $permisosModulo);
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
     * Verifica si el usuario conserva acceso al módulo (cualquier permiso del módulo).
     *
     * @param User $user
     * @param array $permisosModulo
     * @return bool
     */
    protected function usuarioConservaModulo(User $user, array $permisosModulo): bool
    {
        if (empty($permisosModulo)) {
            return false;
        }

        $user->unsetRelation('permissions');
        $user->unsetRelation('roles');

        $todosPermisos = $user->getAllPermissions()
            ->pluck('name')
            ->flip();

        foreach ($permisosModulo as $permiso) {
            if (isset($todosPermisos[$permiso])) {
                return true;
            }
        }

        return false;
    }
}
