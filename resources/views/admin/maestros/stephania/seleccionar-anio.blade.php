@extends('adminlte::page')

@section('title', 'Seleccionar Año Escolar')

@section('content_header')
    <div class="content-header-modern mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="mr-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.6rem;">Seleccionar Año Escolar</h1>
                    <p class="text-muted mb-0">Elija el período académico con el que desea operar en el sistema</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            @foreach($anios as $anio)
                @php
                    $esActivo = in_array($anio->status, ['Activo', 'Extendido']);
                    $esSeleccionado = ($anio->id === $anioSeleccionadoId);
                    $inicioAnio = $anio->inicio_anio_escolar ? $anio->inicio_anio_escolar->format('Y') : '';
                    $cierreAnio = $anio->cierre_anio_escolar ? $anio->cierre_anio_escolar->format('Y') : '';
                @endphp
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border {{ $esSeleccionado ? 'border-primary' : ($esActivo ? 'border-success' : 'border-secondary') }}" style="{{ $esSeleccionado ? 'border-width: 2px !important;' : '' }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $esSeleccionado ? 'bg-primary text-white' : ($esActivo ? 'bg-light' : 'bg-light text-muted') }}">
                            <h5 class="card-title font-weight-bold mb-0">
                                <i class="fas fa-calendar-check mr-2"></i>Período {{ $inicioAnio }} - {{ $cierreAnio }}
                            </h5>
                            @if($esSeleccionado)
                                <span class="badge badge-light text-primary font-weight-bold">
                                    <i class="fas fa-check mr-1"></i>En uso
                                </span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3 d-flex flex-wrap align-items-center">
                                    @if($esActivo)
                                        <span class="badge badge-success px-2 py-1 mr-2">
                                            <i class="fas fa-circle mr-1" style="font-size: 0.6rem;"></i>Año Activo
                                        </span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1 mr-2 font-weight-bold">
                                            <i class="fas fa-eye mr-1"></i>Solo consulta
                                        </span>
                                        <span class="badge badge-secondary px-2 py-1">
                                            {{ $anio->status }}
                                        </span>
                                    @endif
                                </div>

                                <ul class="list-unstyled text-muted mb-4" style="font-size: 0.95rem;">
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-day text-secondary mr-2"></i>
                                        <strong>Inicio:</strong> {{ $anio->inicio_anio_escolar?->format('d/m/Y') ?? 'N/D' }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-times text-secondary mr-2"></i>
                                        <strong>Cierre:</strong> {{ $anio->cierre_anio_escolar?->format('d/m/Y') ?? 'N/D' }}
                                    </li>
                                    @if($anio->extencion_anio_escolar)
                                        <li class="mb-2 text-info">
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Extensión:</strong> {{ $anio->extencion_anio_escolar->format('d/m/Y') }}
                                        </li>
                                    @endif
                                </ul>

                                @if(!$esActivo)
                                    <div class="alert alert-warning py-2 px-3 small text-dark mb-3">
                                        <i class="fas fa-lock mr-1"></i> Las operaciones en este período son únicamente de lectura.
                                    </div>
                                @endif
                            </div>

                            <form action="{{ route('admin.anio-escolar.guardar') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="anio_escolar_id" value="{{ $anio->id }}">
                                <button type="submit" class="btn btn-block {{ $esSeleccionado ? 'btn-primary' : ($esActivo ? 'btn-outline-success' : 'btn-outline-primary') }} font-weight-bold">
                                    @if($esSeleccionado)
                                        <i class="fas fa-arrow-right mr-1"></i>Continuar en este año
                                    @else
                                        <i class="fas fa-sign-in-alt mr-1"></i>Seleccionar este año
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@stop

