<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnioEscolar extends Model
{
    use HasFactory;

    protected $table = 'anio_escolars';

    protected $fillable = [
        'inicio_anio_escolar',
        'cierre_anio_escolar',
        'extencion_anio_escolar',
        'status',
    ];

    protected $casts = [
        'inicio_anio_escolar' => 'date',
        'cierre_anio_escolar' => 'date',
        'extencion_anio_escolar' => 'date',
    ];

    public function historicos()
    {
        return $this->hasMany(Historico::class);
    }

    public function docentes()
    {
        return $this->hasMany(Docente::class);
    }

    public function noDisponibilidades()
    {
        return $this->hasMany(DocenteNoDisponibilidad::class);
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('status', ['Activo', 'Extendido']);
    }

    public function scopeInactivos($query)
    {
        return $query->where('status', 'Inactivo');
    }

    public static function inactivarVencidos(): int
    {
        $hoy = Carbon::today();
        $contador = 0;

        $aniosVencidos = self::activos()
            ->where('cierre_anio_escolar', '<', $hoy)
            ->get();

        foreach ($aniosVencidos as $anio) {
            $anio->marcarComoInactivo();
            $contador++;

            Log::info('Calendario Escolar auto-inactivado', [
                'id' => $anio->id,
                'inicio' => $anio->inicio_anio_escolar->format('Y-m-d'),
                'cierre' => $anio->cierre_anio_escolar->format('Y-m-d'),
                'fecha_sistema' => $hoy->format('Y-m-d')
            ]);
        }

        return $contador;
    }

    public function marcarComoExtendido(): void
    {
        $this->update(['status' => 'Extendido']);
    }


    public function marcarComoInactivo(): void
    {
        $this->update(['status' => 'Inactivo']);
    }

    public function getNombreCompletoAttribute(): string
    {
        return sprintf(
            '%s - %s',
            $this->inicio_anio_escolar->format('d/m/Y'),
            $this->cierre_anio_escolar->format('d/m/Y')
        );
    }

    public function getDuracionDiasAttribute(): int
    {
        return $this->inicio_anio_escolar->diffInDays($this->cierre_anio_escolar);
    }

    public function getDuracionMesesAttribute(): int
    {
        return $this->inicio_anio_escolar->diffInMonths($this->cierre_anio_escolar);
    }

    public static function puedeCrearConFechas($inicio, $cierre): array
    {
        $inicio = Carbon::parse($inicio);
        $cierre = Carbon::parse($cierre);

        if ($inicio >= $cierre) {
            return [
                'valido' => false,
                'error' => 'La fecha de inicio no puede ser mayor o igual a la fecha de cierre.'
            ];
        }

        $existe = self::where(function ($q) use ($inicio, $cierre) {
            $q->whereBetween('inicio_anio_escolar', [$inicio, $cierre])
                ->orWhereBetween('cierre_anio_escolar', [$inicio, $cierre]);
        })
            ->whereIn('status', ['Activo', 'Extendido'])
            ->exists();

        if ($existe) {
            return [
                'valido' => false,
                'error' => 'Ya existe un Calendario Escolar que se superpone con estas fechas.'
            ];
        }

        return [
            'valido' => true,
            'error' => null
        ];
    }

    public function puedeExtenderA($nuevoCierre): array
    {
        $nuevoCierre = Carbon::parse($nuevoCierre);

        if ($nuevoCierre <= $this->cierre_anio_escolar) {
            return [
                'valido' => false,
                'error' => 'La nueva fecha debe ser mayor a la fecha de cierre actual.'
            ];
        }

        return [
            'valido' => true,
            'error' => null
        ];
    }
}
