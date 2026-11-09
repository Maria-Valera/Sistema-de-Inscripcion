@extends('adminlte::page')

@section('title', 'Seleccionar Año Escolar')

@section('classes_body', 'sidebar-collapse')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        /* Ocultar barra lateral y resetear márgenes */
        .main-sidebar, 
        .sidebar, 
        [class*="sidebar-dark-"] {
            display: none !important;
        }

        .content-wrapper, 
        body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper,
        .main-header,
        .main-footer {
            margin-left: 0 !important;
        }

        /* Ocultar el botón hamburguesa en el navbar */
        .nav-item [data-widget="pushmenu"] {
            display: none !important;
        }

        /* Contenedor centrado */
        .select-year-container {
            min-height: calc(85vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .select-year-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            animation: fadeInCard 0.35s ease-out;
        }

        @keyframes fadeInCard {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .select-year-header {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }

        .select-year-header .icon-circle {
            width: 68px;
            height: 68px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .select-year-body {
            padding: 2rem;
        }

        .form-select-styled {
            height: 52px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 1.05rem;
            font-weight: 500;
            color: #1f2937;
            background-color: #f9fafb;
            transition: all 0.2s ease;
        }

        .form-select-styled:focus {
            border-color: #4f46e5;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
            outline: none;
        }

        .btn-ingresar {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            cursor: pointer;
        }

        .btn-ingresar:hover {
            background: linear-gradient(135deg, #4338ca, #3730a3);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-ingresar:active {
            transform: translateY(0);
        }
    </style>
@stop

@section('content')
    <div class="select-year-container">
        <div class="select-year-card">
            {{-- Encabezado estilizado con degradado índigo --}}
            <div class="select-year-header">
                <div class="icon-circle">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="font-weight-bold mb-1" style="font-size: 1.5rem;">Año Escolar</h3>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">Seleccione el período académico de trabajo</p>
            </div>

            {{-- Formulario de selección --}}
            <div class="select-year-body">
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

                {{-- Aviso y botón de creación cuando no hay ningún año escolar activo --}}
                @if(!$anioActivo)
                    <div class="alert alert-warning py-3 px-3 mb-3 border-0 shadow-sm" style="background-color: #fffbeb; color: #b45309; border-left: 4px solid #f59e0b !important; border-radius: 8px;">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                            <span class="font-weight-bold">No hay ningún año escolar activo</span>
                        </div>
                        <p class="small mb-0">Actualmente no existe un período académico activo en el sistema. Puedes crear y activar un nuevo año escolar para comenzar a operar.</p>
                    </div>

                    @can('anio_escolar.gestionar')
                        <button type="button" 
                                class="btn btn-success btn-block mb-3 py-2 font-weight-bold d-flex align-items-center justify-content-center shadow-sm"
                                style="font-size: 1rem; border-radius: 10px; gap: 0.5rem; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);"
                                data-bs-toggle="modal" 
                                data-bs-target="#modalCrearAnioEscolar"
                                data-toggle="modal" 
                                data-target="#modalCrearAnioEscolar">
                            <i class="fas fa-plus-circle"></i>
                            <span>Crear Nuevo Año Escolar</span>
                        </button>
                    @endcan
                @endif

                @if($anios->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3 text-secondary" style="opacity: 0.6;"></i>
                        <h6 class="font-weight-bold mb-1">Sin años escolares disponibles</h6>
                        <p class="small text-muted mb-0">Crea el primer año escolar usando el botón superior para ingresar al sistema.</p>
                    </div>
                @else
                    <form action="{{ route('admin.anio-escolar.guardar') }}" method="POST" id="formSeleccionarAnio">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="anio_escolar_id" class="font-weight-bold text-secondary mb-2" style="font-size: 0.9rem;">
                                <i class="fas fa-layer-group text-primary mr-1"></i> Período Académico
                            </label>
                            <select name="anio_escolar_id" id="anio_escolar_id" class="form-control form-select-styled" required>
                                @foreach($anios as $anio)
                                    @php
                                        $esActivo = in_array($anio->status, ['Activo', 'Extendido']);
                                        $esSeleccionado = ($anio->id === $anioSeleccionadoId);
                                        $inicioAnio = $anio->inicio_anio_escolar ? \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('Y') : '';
                                        $cierreAnio = $anio->cierre_anio_escolar ? \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('Y') : '';
                                        $etiqueta = $esActivo ? 'Activo' : 'Solo consulta';
                                    @endphp
                                    <option value="{{ $anio->id }}" 
                                            data-activo="{{ $esActivo ? '1' : '0' }}"
                                            {{ $esSeleccionado || ($loop->first && !$anioSeleccionadoId) ? 'selected' : '' }}>
                                        Período {{ $inicioAnio }} - {{ $cierreAnio }} ({{ $etiqueta }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Banner dinámico según el período elegido --}}
                        <div id="banner-estado" class="mb-4">
                            <div id="estado-consulta" class="alert alert-warning py-2 px-3 small border-0 d-none" style="background-color: #fffbeb; color: #b45309; border-left: 4px solid #f59e0b !important; border-radius: 8px;">
                                <i class="fas fa-lock mr-1.5"></i>
                                <span>Ha seleccionado un <strong>año anterior</strong>. El sistema operará en modo de <strong>solo consulta</strong> (lectura).</span>
                            </div>
                            <div id="estado-activo" class="alert alert-success py-2 px-3 small border-0 d-none" style="background-color: #ecfdf5; color: #047857; border-left: 4px solid #10b981 !important; border-radius: 8px;">
                                <i class="fas fa-check-circle mr-1.5"></i>
                                <span>Ha seleccionado el <strong>año activo</strong>. Podrá realizar inscripciones y modificaciones normalmente.</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ingresar">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Ingresar al sistema</span>
                        </button>
                    </form>
                @endif
            </div>

            {{-- Pie de tarjeta con info de usuario y cierre de sesión --}}
            <div class="px-4 py-3 bg-light text-center border-top small text-muted d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-user-circle mr-1 text-secondary"></i> {{ auth()->user()->name ?? 'Usuario' }}
                </span>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="text-danger font-weight-bold text-decoration-none">
                    <i class="fas fa-power-off mr-1"></i> Salir
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para crear nuevo año escolar --}}
    @can('anio_escolar.gestionar')
        @include('admin.maestros.stephania.anio_escolar.modales.createModal')
    @endcan
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('anio_escolar_id');
        const estadoConsulta = document.getElementById('estado-consulta');
        const estadoActivo = document.getElementById('estado-activo');

        function actualizarBanner() {
            if (!select) return;
            const selectedOption = select.options[select.selectedIndex];
            const esActivo = selectedOption && selectedOption.getAttribute('data-activo') === '1';

            if (esActivo) {
                if (estadoActivo) estadoActivo.classList.remove('d-none');
                if (estadoConsulta) estadoConsulta.classList.add('d-none');
            } else {
                if (estadoConsulta) estadoConsulta.classList.remove('d-none');
                if (estadoActivo) estadoActivo.classList.add('d-none');
            }
        }

        if (select) {
            select.addEventListener('change', actualizarBanner);
            actualizarBanner();
        }
    });
</script>
@stop
