@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
    <style>
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: left;
        }
        .student-name {
            font-weight: 600;
            color: #1E293B;
            font-size: 0.92rem;
        }
        .student-details {
            font-size: 0.8rem;
            color: #64748B;
        }
        .number-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 8px;
            background-color: #F8FAFC;
            color: #1E293B;
            font-weight: 700;
            font-size: 0.88rem;
            border: 1px solid #E2E8F0;
        }
    </style>
@stop

@section('title', 'Historial del Percentil')

@section('content_header')
    <div class="content-header-modern hero-header-horario hero-header-modern" style="background: linear-gradient(135deg, #4A6FC1 0%, #3B5BB3 100%) !important; color: #FFFFFF !important; border-radius: 14px !important; padding: 1.5rem !important; margin-bottom: 1.5rem !important; box-shadow: 0 10px 25px -8px rgba(59, 89, 152, 0.38) !important;">
        <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper" style="background: rgba(255, 255, 255, 0.2) !important; color: #FFFFFF !important; width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-layer-group text-white"></i>
                </div>
                <div>
                    <h1 class="title-main text-white m-0" style="color: #FFFFFF !important; font-size: 1.45rem; font-weight: 700;">Historial del Percentil</h1>
                    <p class="title-subtitle text-white m-0" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.88rem; margin-top: 0.25rem !important;">Cálculo de índices corporales y percentiles por sección</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Éxito</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif
        <div class="card-modern">
            <div class="card-header-modern d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem !important;">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon header-icon-pastel-blue" style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background-color: #EFF6FF !important; color: #4A6FC1 !important; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; box-shadow: none !important; margin: 0 !important;">
                        <i class="fas fa-list-ul" style="color: #4A6FC1 !important;"></i>
                    </div>
                    <div>
                        <h3 class="m-0" style="font-size: 1.15rem; font-weight: 700; color: #1E293B;">Listado del percentil</h3>
                        <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                            Registros calculados para la sección
                        </p>
                    </div>
                </div>
                <div class="header-right d-flex align-items-center">
                    <button @if (!$anioEscolar) disabled @endif type="button" class="btn-secondary-modern d-inline-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#viewModal" title="Resumen de secciones">
                        <i class="fas fa-layer-group text-primary"></i>
                        <span>Resumen de secciones</span>
                    </button>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table table-modern table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th style="background-color: #F1F5F9 !important; color: #475569 !important;">Estudiante</th>
                                <th class="text-center" style="background-color: #F1F5F9 !important; color: #475569 !important;">Sección</th>
                                <th class="text-center" style="background-color: #F1F5F9 !important; color: #475569 !important;">Índice Peso</th>
                                <th class="text-center" style="background-color: #F1F5F9 !important; color: #475569 !important;">Índice Estatura</th>
                                <th class="text-center" style="background-color: #F1F5F9 !important; color: #475569 !important;">Índice Edad</th>
                                <th class="text-center" style="background-color: #F1F5F9 !important; color: #475569 !important;">Índice Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @if ($entradasPercentil->isEmpty())
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon mb-3" style="width: 72px; height: 72px; border-radius: 50%; background: #F1F5F9; color: #94A3B8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto;">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h4 style="font-size: 1.15rem; font-weight: 600; color: #1E293B; margin-bottom: 0.5rem;">
                                                No hay estudiantes asignados a secciones en el Calendario Escolar activo
                                            </h4>
                                            <p style="font-size: 0.9rem; color: #64748B; margin-bottom: 1.25rem;">
                                                Asigna estudiantes a secciones y calcula los índices para ver los resultados aquí.
                                            </p>
                                            @if ($anioEscolar)
                                                <a href="{{ route('admin.seccion_aula.index') }}" class="btn-primary-modern d-inline-flex align-items-center gap-2">
                                                    <i class="fas fa-chalkboard"></i>
                                                    <span>Ir a Asignar Secciones</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach ($entradasPercentil as $index => $datos)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="student-info">
                                                <div class="student-name">
                                                    {{ $datos->inscripcion->alumno->persona->primer_nombre ?? '' }}
                                                    {{ $datos->inscripcion->alumno->persona->segundo_nombre ?? '' }}
                                                    {{ $datos->inscripcion->alumno->persona->tercer_nombre ?? '' }}
                                                    {{ $datos->inscripcion->alumno->persona->primer_apellido ?? '' }}
                                                    {{ $datos->inscripcion->alumno->persona->segundo_apellido ?? '' }}
                                                </div>
                                                <div class="student-details">
                                                    <span>
                                                        {{ $datos->inscripcion->alumno->persona->tipoDocumento->nombre }}-{{ $datos->inscripcion->alumno->persona->numero_documento }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge-pastel-blue">
                                                {{ $datos->seccion->nombre }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">{{ $datos->indice_peso }}</td>
                                        <td class="text-center align-middle">{{ $datos->indice_estatura }}</td>
                                        <td class="text-center align-middle">{{ $datos->indice_edad }}</td>
                                        <td class="text-center align-middle">
                                            <div class="number-badge">
                                                {{ $datos->indice_total }}
                                            </div>
                                        </td>
                                    </tr>
                                    @include('admin.transacciones.percentil.modales.showModal')
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <x-pagination :paginator="$entradasPercentil" />
        </div>
    </div>

@endsection
