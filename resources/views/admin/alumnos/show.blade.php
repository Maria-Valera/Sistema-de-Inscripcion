@extends('adminlte::page')

@section('title', 'Ficha del Alumno')

@section('content')
    @php
        $datos = $alumno;
        $persona = $alumno->persona;
    @endphp

    <div class="container-fluid py-4">
        <div class="card shadow-sm p-4">
            <h3 class="mb-3">Ficha del Alumno</h3>
            <p><strong>Nombre:</strong> {{ $persona?->primer_nombre }} {{ $persona?->primer_apellido }}</p>
            <p><strong>Cédula:</strong> {{ $persona?->tipoDocumento?->nombre }}-{{ $persona?->numero_documento }}</p>

            @can('ver-datos-sensibles-alumno')
                <div class="sensitive-data mt-3">
                    <p><strong>Peso:</strong> {{ $alumno->peso }} kg</p>
                    <p><strong>Estatura:</strong> {{ $alumno->estatura }} cm</p>
                    <p><strong>Talla Zapato:</strong> {{ $alumno->talla_zapato }}</p>
                </div>
            @endcan
        </div>
    </div>
@stop
