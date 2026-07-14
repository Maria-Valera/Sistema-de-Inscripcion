@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/view-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@stop

@section('title', 'Ver Docentes')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <h1 class="title-main">Paso 2 de 2. Registre el(los) estudios del docente</h1>
                    <p class="title-subtitle">Información registrada en el sistema</p>
                </div>
            </div>
            <a href="{{ route('admin.docente.index') }}" class="btn-create" style="background: var(--gray-500);">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h3>Datos del Docente Registrado</h3>
                        <p>Resumen de la información ingresada</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem 2rem 1rem;">
                <h5 class="section-title">Identificación</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> Cédula:</span>
                        <span
                            class="info-value">{{ $docentes->persona->tipoDocumento->nombre }}-{{ $docentes->persona->numero_documento }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-barcode"></i> Código:</span>
                        <span class="info-value">{{ $docentes->codigo ?? 'No asignado' }}</span>
                    </div>
                </div>
                <h5 class="section-title mt-3">Datos Personales</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Primer nombre:</span>
                        <span class="info-value">{{ $docentes->persona->primer_nombre }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Segundo nombre:</span>
                        <span class="info-value">{{ $docentes->persona->segundo_nombre ?? '—' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Tercer nombre:</span>
                        <span class="info-value">{{ $docentes->persona->tercer_nombre ?? '—' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Primer apellido:</span>
                        <span class="info-value">{{ $docentes->persona->primer_apellido }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Segundo apellido:</span>
                        <span class="info-value">{{ $docentes->persona->segundo_apellido ?? '—' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Género:</span>
                        <span class="info-value">{{ $docentes->persona->genero->genero }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Fecha nacimiento:</span>
                        <span class="info-value">{{ $docentes->persona->fecha_nacimiento }}</span>
                    </div>
                </div>
                <h5 class="section-title mt-3">Contacto</h5>

                <div class="info-grid">

                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-envelope"></i> Correo:</span>
                        <span class="info-value">{{ $docentes->persona->email ?? '—' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-phone"></i> Telefono:</span>
                        <span class="info-value">{{ $docentes->persona->telefono_completo ?? ' — ' }}</span>
                    </div>

                    <div class="info-item">
                        @if ($docentes->persona->telefono_dos_completo)
                            <span class="info-label"><i class="fas fa-phone"></i> Segundo Telefono:</span>

                            <span class="info-value">{{ $docentes->persona->telefono_dos_completo }}</span>
                        @else
                            Sin registrar
                        @endif
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-building"></i> Dependencia:</span>
                        <span class="info-value">{{ $docentes->dependencia ?? '—' }}</span>
                    </div>

                    <div class="info-item info-wide">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Dirección:</span>
                        <span class="info-value">{{ $docentes->persona->direccion ?? '—' }}</span>
                    </div>

                    <div class="info-item info-wide">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Horas academicas</span>
                        <span class="info-value">{{ $docentes->horas_academicas ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="">
            <livewire:admin.docente.docente-estudio-realizado :docente="$docentes" />
        </div>
    </div>
@endsection
@livewireStyles
@stack('css')


@livewireScripts
@stack('js')
