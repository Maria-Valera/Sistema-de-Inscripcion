<?php

namespace App\Models;

use App\Enums\CategoriaDia;
use App\Enums\ConfianzaDia;
use App\Enums\ColorEvento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioDia extends Model
{
    //

    protected $fillable = [
        'calendario_id',
        'texto_extraido',
        'mes_pagina',
        'dia_extraido',
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

    /*
    aqui se implementa el arbol de decisiones de color
    no laborable siempre gana sobre si es efemeride (ya lo que se quiere es saber si hay clases si o no no si es historicamente importante)

    no laborable + ambos -> rojo
    no laborable + docentes -> cobalto
    no laborable + estudiantes -> lavanda
    laborable + efemeride -> pavo real (fijo)
    laborable + no efemeride -> el color que elija el usuario, o simplemente el gris
    */
    public static function determinarColor(
        bool $esNoLaborable,
        bool $aplicaPersonal,
        bool $aplicaEstudiantes,
        bool $esEfemeride,
        ?ColorEvento $colorElegido,
    ): ColorEvento{
        if($esNoLaborable){
            if($aplicaPersonal && $aplicaEstudiantes){
                return ColorEvento::Rojo;

            }

            return $aplicaPersonal ? ColorEvento::Cobalto : ColorEvento::Lavanda;

        }

        if($esEfemeride){
            return ColorEvento::PavoReal;
        }

        return $colorElegido ?? ColorEvento::Gris;
    }




    public function calendarioAcademico(): BelongsTo
    {
        return $this->belongsTo(CalendarioAcademico::class, 'calendario_id');
    }

    /*
    scope : solo dias ya confirmados
    su uso : calendarioDia::confirmados()->get()
    */

    public function scopeConfirmados(Builder $query): Builder
    {
        return $query->where('confirmado', true);
    }

    /*
    scope : solo dias pendientes de revision (los candidatos que salieron del pdf)
    */

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('confirmado', false);
    }

    /*
    scope : solo los que son efectivamente no laborables (no los "dudosos")
    */

    public function scopeNoLaborables(Builder $query): Builder
    {
        return $query->where('categoria', CategoriaDia::NoLaborable);
    }

    /*
    scope: dias que afectan al personal (docentes,administrativos y obreros)
    Este es el que usara el modulo de inasistencias para calcular dias habiles
    */

    public function scopeAplicaPersonal(Builder $query): Builder
    {
        return $query->where('aplica_personal', true);
    }

    /*
    scope: dias que afectan a los estudiantes
    */
    public function scopeAplicaEstudiantes(Builder $query): Builder
    {
        return $query->where('aplica_estudiantes', true);
    }

    /*
    scope: dias dentro de un rango de fechas (util para el modulo de proceso administrativo, al calcular
    dias habiles entre dos fechas)
    */

    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /*
    es un helper estatico : responde directamente "¿es esta fecha un  dia no laborable confirmado para el personal,
    dentro de este año escolar?" Pensando para que otros modulos (inasistencias) lo usen sin tener que escribir la consulta completa a cada rato
    */

    public function esNoLaborablePersonal( string $fecha, int $calendarioId): bool{
        return static::query()
        ->where('calendario_id', $calendarioId)
        ->where('fecha', $fecha)
        ->confirmados()
        ->noLaborables()
        ->exists();
    }

}
