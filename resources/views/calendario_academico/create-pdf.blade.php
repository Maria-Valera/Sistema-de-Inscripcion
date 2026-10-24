
@extends('adminlte::page')

@section('title', 'Cargar calendario académico por PDF')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .form-card-body { padding: 2rem; }
        .form-narrow { max-width: 640px; }
        .form-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        /* Campo de archivo (Bootstrap custom-file) alineado con form-control-modern */
        .form-narrow .custom-file,
        .form-narrow .custom-file-input,
        .form-narrow .custom-file-label {
            height: 48px;
        }
        .form-narrow .custom-file-label {
            display: flex;
            align-items: center;
            padding: 0 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 0.9rem;
            color: var(--gray-700);
            background: white;
            overflow: hidden;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        .form-narrow .custom-file-label::after {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 0 1rem;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
            border-left: 2px solid var(--gray-200);
            border-radius: 0 calc(var(--radius) - 2px) calc(var(--radius) - 2px) 0;
        }
        .form-narrow .custom-file-input:focus ~ .custom-file-label {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div>
                    <h1 class="title-main">Cargar calendario académico por PDF</h1>
                    <p class="title-subtitle">Sube el PDF oficial para extraer los días candidatos</p>
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

            <form action="{{ route('admin.calendario_academico.store') }}" method="POST" enctype="multipart/form-data">
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

                <div class="form-group form-narrow">
                    <label for="archivo_pdf" class="form-label-modern">
                        <i class="fas fa-file-upload"></i> Archivo PDF del calendario
                    </label>
                    <div class="custom-file">
                        <input type="file" name="archivo_pdf" id="archivo_pdf" class="custom-file-input" accept="application/pdf" required>
                        <label class="custom-file-label" for="archivo_pdf">Selecciona el PDF...</label>
                    </div>
                    <small class="form-text-modern">
                        <i class="fas fa-info-circle"></i>
                        Tamaño máximo: 20 MB.
                    </small>
                </div>

                <div class="form-actions gap-3">
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-file-import"></i> Procesar PDF
                    </button>
                    <a href="{{ route('admin.calendario_academico.create') }}" class="btn-cancel-modern">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

            </form>

        </div>

    </div>

@endsection

@section('js')

    <script>
        document.getElementById('archivo_pdf').addEventListener('change', function (e) {
            const nombre = e.target.files[0]?.name ?? 'Selecciona el PDF...';
            e.target.nextElementSibling.innerText = nombre;
        });
    </script>

@endsection
