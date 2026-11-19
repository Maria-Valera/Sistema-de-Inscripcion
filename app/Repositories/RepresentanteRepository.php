<?php

namespace App\Repositories;

use App\Models\Representante;
use App\Models\RepresentanteLegal;

class RepresentanteRepository
{
    public function obtenerPorGenero(string $genero): array
    {
        return Representante::with(['persona.tipoDocumento', 'persona.genero'])
            ->whereHas('persona', fn($q) =>
                $q->where('status', true)
                  ->whereHas('genero', fn($g) => $g->where('genero', $genero))
            )
            ->get()
            ->map(fn($rep) => [
                'id' => $rep->id,
                'nombre_completo' =>
                    $rep->persona->primer_nombre . ' ' .
                    ($rep->persona->segundo_nombre ? $rep->persona->segundo_nombre . ' ' : '') .
                    $rep->persona->primer_apellido . ' ' .
                    ($rep->persona->segundo_apellido ?? ''),
                'numero_documento' => $rep->persona->numero_documento,
                'tipo_documento' => $rep->persona->tipoDocumento->nombre ?? 'N/A',
            ])
            ->toArray();
    }

    public function obtenerRepresentantesLegales(): array
    {
        return RepresentanteLegal::with(['representante.persona.tipoDocumento', 'representante.persona.genero'])
            ->whereHas('representante.persona', fn($q) => $q->where('status', true))
            ->get()
            ->map(function ($repLegal) {
                $rep = $repLegal->representante;
                return [
                    'id' => $repLegal->id,
                    'nombre_completo' =>
                        $rep->persona->primer_nombre . ' ' .
                        ($rep->persona->segundo_nombre ? $rep->persona->segundo_nombre . ' ' : '') .
                        $rep->persona->primer_apellido . ' ' .
                        ($rep->persona->segundo_apellido ?? ''),
                    'numero_documento' => $rep->persona->numero_documento,
                    'tipo_documento' => $rep->persona->tipoDocumento->nombre ?? 'N/A',
                ];
            })
            ->toArray();
    }

    public function obtenerConRelaciones($id)
    {
        return Representante::with([
            'persona.tipoDocumento',
            'persona.genero',
            'ocupacion',
            'estado',
            'municipios',
            'localidads'
        ])->find($id);
    }

    public function obtenerRepresentanteLegalConRelaciones($id)
    {
        return RepresentanteLegal::with([
            'representante.persona.tipoDocumento',
            'representante.persona.genero',
            'representante.ocupacion',
            'representante.estado',
            'representante.municipios',
            'representante.localidads',
            'banco'
        ])->find($id);
    }
}