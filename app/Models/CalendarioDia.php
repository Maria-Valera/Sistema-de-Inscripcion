<?php

namespace App\Models;

use App\Enums\CategoriaDia;
use App\Enums\ColorEvento;
use App\Enums\ConfianzaDia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioDia extends Model
{
    protected $fillable = [
        'calendario_id',
        'texto_extraido',
        'mes_pagina',
        'fecha',
        'categoria',
        'confianza',
        'es_mes_completo',
        'confirmado',
        'aplica_personal',
        'aplica_estudiantes',
        'es_efemeride',
        'color',
    ];

    protected $casts = [
        'fecha' => 'date',
        'categoria' => CategoriaDia::class,
        'confianza' => ConfianzaDia::class,
        'es_mes_completo' => 'boolean',
        'confirmado' => 'boolean',
        'aplica_personal' => 'boolean',
        'aplica_estudiantes' => 'boolean',
        'es_efemeride' => 'boolean',
        'color' => ColorEvento::class,
    ];

    /**
     * Implementa el árbol de decisión de color acordado:
     * no laborable siempre gana sobre efeméride (lo operativamente
     * relevante es si hay clases o no, no si es históricamente importante).
     *
     *   No laborable + ambos       -> Rojo
     *   No laborable + docentes    -> Cobalto
     *   No laborable + estudiantes -> Lavanda
     *   Laborable + efeméride      -> Pavo real (fijo)
     *   Laborable + no efeméride   -> el color que eligió el usuario, o Gris
     */
    public static function determinarColor(
        bool $esNoLaborable,
        bool $aplicaPersonal,
        bool $aplicaEstudiantes,
        bool $esEfemeride,
        ?ColorEvento $colorElegido,
    ): ColorEvento {
        if ($esNoLaborable) {
            if ($aplicaPersonal && $aplicaEstudiantes) {
                return ColorEvento::Rojo;
            }

            return $aplicaPersonal ? ColorEvento::Cobalto : ColorEvento::Lavanda;
        }

        if ($esEfemeride) {
            return ColorEvento::PavoReal;
        }

        return $colorElegido ?? ColorEvento::Gris;
    }

    public function calendarioAcademico(): BelongsTo
    {
        return $this->belongsTo(CalendarioAcademico::class, 'calendario_id');
    }

    /**
     * Scope: solo días ya confirmados.
     * Uso: CalendarioDia::confirmados()->get();
     */
    public function scopeConfirmados(Builder $query): Builder
    {
        return $query->where('confirmado', true);
    }

    /**
     * Scope: solo días pendientes de revisión (los candidatos que salieron del PDF).
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('confirmado', false);
    }

    /**
     * Scope: solo los que son efectivamente no laborables (no los "dudosos").
     */
    public function scopeNoLaborables(Builder $query): Builder
    {
        return $query->where('categoria', CategoriaDia::NoLaborable);
    }

    /**
     * Scope: días que afectan al personal (docentes, administrativos, obreros).
     * Este es el que usará el módulo de inasistencias para calcular días hábiles.
     */
    public function scopeAplicaPersonal(Builder $query): Builder
    {
        return $query->where('aplica_personal', true);
    }

    /**
     * Scope: días que afectan a los estudiantes.
     */
    public function scopeAplicaEstudiantes(Builder $query): Builder
    {
        return $query->where('aplica_estudiantes', true);
    }

    /**
     * Scope: días dentro de un rango de fechas (útil para el módulo de
     * proceso administrativo, al calcular días hábiles entre dos fechas).
     */
    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /**
     * Helper estático: responde directamente "¿es esta fecha un día no
     * laborable confirmado para el personal, dentro de este año escolar?"
     * Pensado para que otros módulos (inasistencias) lo usen sin tener que
     * escribir la consulta completa cada vez.
     */
    public static function esNoLaborablePersonal(string $fecha, int $calendarioId): bool
    {
        return static::query()
            ->where('calendario_id', $calendarioId)
            ->where('fecha', $fecha)
            ->confirmados()
            ->noLaborables()
            ->aplicaPersonal()
            ->exists();
    }
}