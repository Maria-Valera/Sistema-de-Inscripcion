<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'usuario_email',
        'accion',
        'auditable_type',
        'auditable_id',
        'valores_anteriores',
        'valores_nuevos',
        'descripcion',
        'ip',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new LogicException('No se permite actualizar registros en la tabla de auditorias. La tabla es de solo lectura.');
        });

        static::deleting(function ($model) {
            throw new LogicException('No se permite eliminar registros en la tabla de auditorias. La tabla es de solo lectura.');
        });
    }

    /**
     * Registra una acción en la auditoría
     *
     * @param string $accion Acción realizada (ej: 'crear', 'actualizar', 'eliminar')
     * @param Model|null $modelo Modelo afectado (opcional)
     * @param array|null $anteriores Valores anteriores (opcional)
     * @param array|null $nuevos Valores nuevos (opcional)
     * @param string|null $descripcion Descripción adicional (opcional)
     * @return Auditoria|null
     */
    public static function registrar(
        string $accion,
        ?Model $modelo = null,
        ?array $anteriores = null,
        ?array $nuevos = null,
        ?string $descripcion = null
    ): ?Auditoria {
        try {
            $usuario = Auth::user();
            $request = Request::capture();

            $auditoria = new self([
                'user_id' => $usuario?->id,
                'usuario_email' => $usuario?->email,
                'accion' => $accion,
                'auditable_type' => $modelo ? get_class($modelo) : null,
                'auditable_id' => $modelo?->id,
                'valores_anteriores' => $anteriores,
                'valores_nuevos' => $nuevos,
                'descripcion' => $descripcion,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);

            $auditoria->save();

            return $auditoria;
        } catch (\Exception $e) {
            // En consola no hay request HTTP, evitamos fallar
            return null;
        }
    }

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación polimórfica con el modelo auditable
     */
    public function auditable()
    {
        return $this->morphTo();
    }
}
