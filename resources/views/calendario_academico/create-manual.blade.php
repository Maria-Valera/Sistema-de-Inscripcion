

@extends('adminlte::page')

@section('title', 'Crear calendario manual')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .form-card-body { padding: 2rem; }
        .form-narrow { max-width: 640px; }
        .form-intro {
            background: var(--info-lighter);
            border-left: 4px solid var(--info);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
            color: var(--gray-700);
        }
        .form-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }
        @media (max-width: 768px) {
            .form-card-body { padding: 1.25rem; }
        }
    </style>
@endsection

@section('content_header')

    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div>
                    <h1 class="title-main">Crear calendario manual</h1>
                    <p class="title-subtitle">Empieza con un calendario vacío para el año escolar</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('content')

    <div class="card card-modern">

        <div class="card-body form-card-body">

            @if ($errors->any())
                <div class="alert-modern alert-error mb-4" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <p class="form-intro">
                Se creará un calendario vacío para el año escolar que elijas. Después podrás
                registrar cada evento directamente sobre el calendario visual.
            </p>

            <form action="{{ route('admin.calendario_academico.store_manual') }}" method="POST">
                @csrf

                <div class="form-group form-narrow">
                    <label for="anio_escolar_id" class="form-label-modern">
                        <i class="fas fa-calendar-alt"></i> Año escolar
                    </label>
                    <select name="anio_escolar_id" id="anio_escolar_id" class="form-control-modern" required>
                        <option value="">Selecciona un año escolar...</option>
                        @foreach ($aniosEscolaresDisponibles as $anio)
                            <option value="{{ $anio->id }}">
                                {{ \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('d/m/Y') }}
                                —
                                {{ \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text-modern">
                        <i class="fas fa-info-circle"></i>
                        Solo se muestran años escolares que todavía no tienen un calendario cargado.
                    </small>
                </div>

                <div class="form-actions gap-3">
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-calendar-plus"></i> Crear calendario manual
                    </button>
                    <a href="{{ route('admin.calendario_academico.create') }}" class="btn-cancel-modern">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

            </form>

        </div>

    </div>

@endsection
