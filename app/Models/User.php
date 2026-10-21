<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Concerns\Auditable;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'persona_id',
        'rol_principal',
        'debe_cambiar_password',
        'activo',
        'ultimo_acceso_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'debe_cambiar_password' => 'boolean',
            'activo' => 'boolean',
            'ultimo_acceso_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     * Integridad defensiva: Si tiene rol y rol_principal es null, auto-asignar el primer rol.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (empty($user->rol_principal) && $user->roles()->exists()) {
                $primerRol = $user->roles()->first();
                if ($primerRol) {
                    $user->rol_principal = $primerRol->name;
                }
            }
        });
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function isDocente()
    {
        return $this->persona && $this->persona->docente;
    }

    public function isRepresentante()
    {
        return $this->persona && $this->persona->representante;
    }

    public function getRepresentanteAttribute()
    {
        return $this->persona?->representante;
    }

    public function getDocenteAttribute()
    {
        return $this->persona?->docente;
    }

    /**
     * Determina si el usuario tiene rol Docente sin privilegios de Admin o Subdirectora
     */
    public function esSoloDocente(): bool
    {
        return $this->hasRole('Docente') && !$this->hasRole('Admin') && !$this->hasRole('Subdirectora');
    }

    public function getTipoUsuarioAttribute()
    {
        if ($this->isDocente()) {
            return 'docente';
        }
        if ($this->isRepresentante()) {
            return 'representante';
        }
        return 'admin';
    }

    /**
     * Obtiene el rol principal efectivo del usuario
     * Si tiene rol_principal asignado, lo retorna
     * Si no, retorna el primer rol asignado
     *
     * @return string|null
     */
    public function rolPrincipalEfectivo(): ?string
    {
        if ($this->rol_principal) {
            return $this->rol_principal;
        }

        $primerRol = $this->roles->first();
        return $primerRol ? $primerRol->name : null;
    }

    /**
     * Obtiene la ruta del dashboard según el rol principal efectivo
     *
     * @return string
     */
    public function rutaDashboard(): string
    {
        $rol = $this->rolPrincipalEfectivo();

        return match($rol) {
            'Admin' => route('home'),
            'Subdirectora' => route('dashboard.subdirectora'),
            'Docente' => route('dashboard.docente'),
            'Representante' => route('portal-representante.index'),
            default => route('sin-acceso'),
        };
    }

    /**
     * Devuelve las claves de los módulos para los cuales el usuario tiene
     * cualquier permiso, ya sea por rol o por permiso directo.
     *
     * @return array
     */
    public function modulosEfectivos(): array
    {
        $todosPermisos = $this->getAllPermissions()
            ->pluck('name')
            ->flip();

        $modulos = config('modulos', []);
        $efectivos = [];

        foreach ($modulos as $clave => $config) {
            $permisosModulo = $config['permisos'] ?? [];

            foreach ($permisosModulo as $permiso) {
                if (isset($todosPermisos[$permiso])) {
                    $efectivos[] = $clave;
                    break;
                }
            }
        }

        return $efectivos;
    }
}
