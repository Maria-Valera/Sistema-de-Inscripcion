@extends('adminlte::page')

@section('title', 'Configuración de evaluación')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <h1 class="title-main">Configuración de evaluación</h1>
                    <p class="title-subtitle">Reglas de evaluación de cada año escolar</p>
                </div>
            </div>
            @can('configuracion_evaluacion.gestionar')
                @if ($aniosSinConfiguracion->isNotEmpty())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="fas fa-plus"></i> <span>Nueva configuración</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        @can('configuracion_evaluacion.gestionar')
            @if ($aniosSinConfiguracion->isNotEmpty())
                @include('admin.configuracion_evaluacion.modales.createModal')
            @endif
        @endcan

        {{-- Mensajes --}}
        @foreach (['success' => ['alert-success', 'fa-check-circle', 'Éxito'], 'error' => ['alert-danger', 'fa-exclamation-circle', 'Error']] as $clave => [$clase, $icono, $titulo])
            @if (session($clave))
                <div class="alerts-container">
                    <div class="alert-modern {{ $clase }} alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon"><i class="fas {{ $icono }}"></i></div>
                        <div class="alert-content">
                            <h4>{{ $titulo }}</h4>
                            <p>{{ session($clave) }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="alerts-container">
                <div class="alert-modern alert-danger alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Revisa los datos</h4>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $mensaje)
                                <li>{{ $mensaje }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

                <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Configuraciones por año escolar</h3>
                        <p class="mb-0 text-muted">{{ $configuraciones->total() }} registros encontrados</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="text-align: center">Año escolar</th>
                                <th style="text-align: center">Escala</th>
                                <th style="text-align: center">Aprueba con</th>
                                <th style="text-align: center">Redondeo</th>
                                <th style="text-align: center">Lapsos</th>
                                <th style="text-align: center">Cortes por lapso</th>
                                <th style="text-align: center">Máx. pendientes</th>
                                @can('configuracion_evaluacion.gestionar')
                                    <th style="text-align: center; width: 120px">Acciones</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($configuraciones as $config)
                                <tr>
                                    <td style="text-align: center">
                                        <strong>{{ $config->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $config->anioEscolar->cierre_anio_escolar->format('Y') }}</strong>
                                    </td>
                                    <td style="text-align: center">{{ $config->nota_minima }} a {{ $config->nota_maxima }}</td>
                                    <td style="text-align: center">{{ $config->nota_aprobatoria }}</td>
                                    <td style="text-align: center">{{ $config->regla_redondeo->label() }}</td>
                                    <td style="text-align: center">{{ $config->num_lapsos }}</td>
                                    <td style="text-align: center">{{ $config->num_cortes }} de {{ $config->puntos_corte }} puntos</td>
                                    <td style="text-align: center">{{ $config->max_areas_pendientes }}</td>
                                    @can('configuracion_evaluacion.gestionar')
                                        <td style="text-align: center">
                                            <div class="action-buttons">
                                                <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                    data-bs-target="#editarConfiguracion{{ $config->id }}" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                    data-bs-target="#eliminarConfiguracion{{ $config->id }}" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-sliders-h"></i></div>
                                            <h4>No hay configuraciones registradas</h4>
                                            <p>Crea la configuración de evaluación del año escolar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('configuracion_evaluacion.gestionar')
                @foreach ($configuraciones as $config)
                    @include('admin.configuracion_evaluacion.modales.editModal')
                    @include('admin.configuracion_evaluacion.modales.deleteModal')
                @endforeach
            @endcan

            <div class="card-footer-modern d-flex flex-wrap align-items-center justify-content-between">
                <div>{{ $configuraciones->links() }}</div>
            </div>
        </div>
    </div>
@stop
