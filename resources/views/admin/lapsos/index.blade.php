@extends('adminlte::page')

@section('title', 'Lapsos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .mensaje-error { display: block; color: #dc3545; font-size: 0.8rem; margin-top: 4px; }
        .badge-estado { padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-planificado { background: #e5e7eb; color: #374151; }
        .badge-abierto { background: #d1fae5; color: #065f46; }
        .badge-cerrado { background: #fee2e2; color: #991b1b; }
        .suma-ok { color: #059669; font-weight: 600; }
        .suma-pendiente { color: #d97706; font-weight: 600; }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Lapsos</h1>
                    <p class="title-subtitle">
                        @if ($anio)
                            Año escolar {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                        @else
                            No hay un año escolar seleccionado
                        @endif
                    </p>
                </div>
            </div>
            @can('lapsos.gestionar')
                @if ($puedeCrear && \App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#crearLapso">
                        <i class="fas fa-plus"></i> <span>Nuevo lapso</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        @include('admin.lapsos._mensajes')

        @if ($anio && ! $config)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Este año escolar no tiene configuración de evaluación. Créala primero en
                <a href="{{ route('admin.configuracion_evaluacion.index') }}">Configuración de Evaluación</a>
                para poder cargar sus lapsos.
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Lapsos del año</h3>
                        <p class="mb-0 text-muted">
                            {{ $lapsos->count() }}
                            @if ($config) de {{ $config->num_lapsos }} configurados @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="text-align: center">N°</th>
                                <th>Nombre</th>
                                <th style="text-align: center">Desde</th>
                                <th style="text-align: center">Hasta</th>
                                <th style="text-align: center">Límite de carga</th>
                                <th style="text-align: center">Cortes</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center; width: 160px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lapsos as $lapso)
                                @php
                                    $suma = (float) ($lapso->cortes_sum_peso_lapso ?? 0);
                                    $completo = $config && abs($suma - $config->nota_maxima) < 0.001;
                                @endphp
                                <tr>
                                    <td style="text-align: center"><strong>{{ $lapso->numero }}</strong></td>
                                    <td>{{ $lapso->nombre }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_inicio->format('d/m/Y') }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_fin->format('d/m/Y') }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_limite_carga->format('d/m/Y') }}</td>
                                    <td style="text-align: center">
                                        {{ $lapso->cortes_count }} cortes ·
                                        <span class="{{ $completo ? 'suma-ok' : 'suma-pendiente' }}">
                                            {{ rtrim(rtrim(number_format($suma, 2, '.', ''), '0'), '.') }} de {{ $config?->nota_maxima ?? '?' }} pts
                                        </span>
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge-estado badge-{{ $lapso->estado->value }}">{{ $lapso->estado->label() }}</span>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="action-buttons">
                                            @can('cortes.ver')
                                                <a href="{{ route('admin.cortes.index', $lapso) }}" class="action-btn btn-view" title="Cortes del lapso">
                                                    <i class="fas fa-cut"></i>
                                                </a>
                                            @endcan
                                            @can('lapsos.gestionar')
                                                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                    <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#editarLapso{{ $lapso->id }}" title="Editar">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                        data-bs-target="#eliminarLapso{{ $lapso->id }}" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-layer-group"></i></div>
                                            <h4>No hay lapsos registrados</h4>
                                            <p>Crea los lapsos del año escolar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('lapsos.gestionar')
                @if ($puedeCrear)
                    @include('admin.lapsos.modales.createModal')
                @endif
                @foreach ($lapsos as $lapso)
                    @include('admin.lapsos.modales.editModal')
                    @include('admin.lapsos.modales.deleteModal')
                @endforeach
            @endcan
        </div>
    </div>
@stop

@section('js')
    @include('admin.lapsos._reabrir_modal')
@stop
