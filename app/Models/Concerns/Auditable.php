<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            $atributos = $model->getAttributes();
            $atributos = $model->filtrarAtributosSensibles($atributos);
            $model->registrarAuditoria('crear', null, $atributos);
        });

        static::updated(function (Model $model) {
            $anteriores = $model->getOriginal();
            $nuevos = $model->getDirty();

            // Filtrar atributos sensibles
            $anteriores = $model->filtrarAtributosSensibles($anteriores);
            $nuevos = $model->filtrarAtributosSensibles($nuevos);

            // Solo registrar si hubo cambios reales después de filtrar
            if (!empty($nuevos)) {
                $model->registrarAuditoria('actualizar', $anteriores, $nuevos);
            }
        });

        static::deleted(function (Model $model) {
            $anteriores = $model->getOriginal();
            $anteriores = $model->filtrarAtributosSensibles($anteriores);

            $model->registrarAuditoria('eliminar', $anteriores, null);
        });
    }

    /**
     * Filtra atributos sensibles (password, remember_token, hidden)
     *
     * @param array $atributos
     * @return array
     */
    protected function filtrarAtributosSensibles(array $atributos): array
    {
        $atributosExcluidos = ['password', 'remember_token'];

        // Agregar atributos hidden del modelo
        if (property_exists($this, 'hidden')) {
            $atributosExcluidos = array_merge($atributosExcluidos, $this->hidden);
        }

        return array_diff_key($atributos, array_flip($atributosExcluidos));
    }

    /**
     * Registra la auditoría
     *
     * @param string $accion
     * @param array|null $anteriores
     * @param array|null $nuevos
     * @return void
     */
    protected function registrarAuditoria(string $accion, ?array $anteriores, ?array $nuevos): void
    {
        Auditoria::registrar(
            $accion,
            $this,
            $anteriores,
            $nuevos,
            $this->obtenerDescripcion($accion)
        );
    }

    /**
     * Obtiene una descripción automática para la acción
     *
     * @param string $accion
     * @return string
     */
    protected function obtenerDescripcion(string $accion): string
    {
        $nombreModelo = class_basename($this);
        $identificador = $this->getKey();

        return match($accion) {
            'crear' => "Se creó {$nombreModelo} con ID {$identificador}",
            'actualizar' => "Se actualizó {$nombreModelo} con ID {$identificador}",
            'eliminar' => "Se eliminó {$nombreModelo} con ID {$identificador}",
            default => "Acción '{$accion}' en {$nombreModelo} con ID {$identificador}",
        };
    }
}
