@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('title', 'Paso 2: Registrar Estudios del Docente')

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start gap-3">
            <div class="header-title d-flex flex-column flex-md-row align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Paso 2 de 2: Registre el(los) estudios del docente</h1>
                    <p class="title-subtitle mb-0">Información registrada en el sistema y asignación académica</p>
                </div>
            </div>
            <div class="header-actions d-flex align-items-center gap-2 flex-wrap justify-content-center">
                <a href="{{ route('admin.docente.disponibilidad', $docentes->id) }}" class="btn-hero-white" title="Disponibilidad">
                    <i class="fas fa-calendar-times"></i>
                    <span>Disponibilidad</span>
                </a>
                <a href="{{ route('admin.docente.index') }}" class="btn-hero-white" title="Volver al listado">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <!-- Resumen Limpio del Docente Registrado -->
        <div class="card-modern shadow-sm mb-4">
            <div class="card-header-modern border-bottom px-4 py-3 bg-white" style="border-bottom: 1px solid #E2E8F0 !important;">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Datos del Docente Registrado</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">Resumen de la información ingresada en el Paso 1</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Cédula</span>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.95rem;">
                            {{ $docentes->persona->tipoDocumento->nombre }}-{{ $docentes->persona->numero_documento }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Nombre Completo</span>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.95rem;">
                            {{ $docentes->persona->primer_nombre }} {{ $docentes->persona->segundo_nombre }} {{ $docentes->persona->tercer_nombre }} {{ $docentes->persona->primer_apellido }} {{ $docentes->persona->segundo_apellido }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Código</span>
                        <div class="mt-1">
                            <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.85rem; border: 1px solid #DBEAFE;">
                                {{ $docentes->codigo ?? 'No asignado' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Género / Nacimiento</span>
                        <div class="fw-medium text-dark mt-1" style="font-size: 0.88rem;">
                            {{ $docentes->persona->genero->genero }} &bull; {{ $docentes->persona->fecha_nacimiento }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Contacto</span>
                        <div class="fw-medium text-dark mt-1" style="font-size: 0.88rem;">
                            <div><i class="fas fa-envelope text-muted me-1"></i> {{ $docentes->persona->email ?? '—' }}</div>
                            <div><i class="fas fa-phone text-muted me-1"></i> {{ $docentes->persona->telefono_completo ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Dependencia</span>
                        <div class="fw-medium text-dark mt-1" style="font-size: 0.88rem;">
                            {{ $docentes->dependencia ?? '—' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Horas Académicas</span>
                        <div class="fw-medium text-dark mt-1" style="font-size: 0.88rem;">
                            {{ $docentes->horas_academicas ? $docentes->horas_academicas . ' horas' : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <livewire:admin.docente.docente-estudio-realizado :docente="$docentes" />
        </div>
    </div>
@endsection

@livewireStyles
@stack('css')

@livewireScripts
