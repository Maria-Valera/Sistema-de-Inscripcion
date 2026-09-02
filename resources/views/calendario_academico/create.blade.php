{{-- @extends('adminlte::page')

@section('title', 'Cargar calendario académico')

@section('content_header')
    <h1>Cargar calendario académico</h1>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.calendario_academico.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="anio_escolar_id">Año escolar</label>
                    <select name="anio_escolar_id" id="anio_escolar_id" class="form-control" required>
                        <option value="">Selecciona un año escolar...</option>
                        @foreach ($aniosEscolaresDisponibles as $anio)
                            <option value="{{ $anio->id }}">
                                {{ \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('d/m/Y') }}
                                —
                                {{ \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Solo se muestran años escolares que todavía no tienen un calendario cargado.
                    </small>
                </div>

                <div class="form-group">
                    <label for="archivo_pdf">Archivo PDF del calendario</label>
                    <div class="custom-file">
                        <input type="file" name="archivo_pdf" id="archivo_pdf" class="custom-file-input" accept="application/pdf" required>
                        <label class="custom-file-label" for="archivo_pdf">Selecciona el PDF...</label>
                    </div>
                    <small class="form-text text-muted">Tamaño máximo: 20 MB.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Procesar PDF
                </button>
                <a href="{{ route('admin.calendario_academico.index') }}" class="btn btn-default">Cancelar</a>
            </form>

        </div>
    </div>
@endsection

@section('js')
    <script>
        // Muestra el nombre del archivo seleccionado en el input estilizado de Bootstrap.
        document.getElementById('archivo_pdf').addEventListener('change', function (e) {
            const nombre = e.target.files[0]?.name ?? 'Selecciona el PDF...';
            e.target.nextElementSibling.innerText = nombre;
        });
    </script>
@endsection --}}


@extends('adminlte::page')

@section('title', 'Cargar calendario académico')

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
                    <i class="fas fa-upload"></i>
                </div>
                <div>
                    <h1 class="title-main">Cargar calendario académico</h1>
                    <p class="title-subtitle">Sube un archivo PDF para extraer los días del calendario</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        {{-- Mostrar errores de validación con estilo moderno --}}
        @if ($errors->any())
            <div class="alerts-container">
                <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Errores en el formulario</h4>
                        <ul class="mb-0" style="padding-left: 1.2rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close btn-close" data-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Datos del calendario</h3>
                        <p class="mb-0 text-muted">Completa los campos para procesar el PDF</p>
                    </div>
                </div>
            </div>

            {{-- Aumentamos el padding interior y el espaciado entre campos --}}
            <div class="card-body p-4 p-lg-5">
                <form action="{{ route('admin.calendario_academico.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Año escolar --}}
                    <div class="form-group mb-4">
                        <label for="anio_escolar_id" class="form-label fw-semibold">Año escolar</label>
                        <select name="anio_escolar_id" id="anio_escolar_id" class="form-control form-control-lg" required>
                            <option value="">Selecciona un año escolar...</option>
                            @foreach ($aniosEscolaresDisponibles as $anio)
                                <option value="{{ $anio->id }}">
                                    {{ \Carbon\Carbon::parse($anio->inicio_anio_escolar)->format('d/m/Y') }}
                                    —
                                    {{ \Carbon\Carbon::parse($anio->cierre_anio_escolar)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted mt-1">
                            Solo se muestran años escolares que todavía no tienen un calendario cargado.
                        </small>
                    </div>

                    {{-- Archivo PDF --}}
                    <div class="form-group mb-4">
                        <label for="archivo_pdf" class="form-label fw-semibold">Archivo PDF del calendario</label>
                        <div class="custom-file">
                            <input type="file" name="archivo_pdf" id="archivo_pdf"
                                   class="custom-file-input form-control-lg"
                                   accept="application/pdf" required>
                            <label class="custom-file-label" for="archivo_pdf">Selecciona el PDF...</label>
                        </div>
                        <small class="form-text text-muted mt-1">Tamaño máximo: 20 MB.</small>
                    </div>

                    {{-- Botones con más separación --}}
                    <div class="d-flex flex-wrap gap-3 mt-4 pt-2">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-play me-1"></i> Procesar PDF
                        </button>
                        <a href="{{ route('admin.calendario_academico.index') }}" class="btn btn-secondary btn-lg px-4">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Muestra el nombre del archivo seleccionado en el input estilizado.
        document.getElementById('archivo_pdf').addEventListener('change', function (e) {
            const nombre = e.target.files[0]?.name ?? 'Selecciona el PDF...';
            e.target.nextElementSibling.innerText = nombre;
        });
    </script>
@stop
