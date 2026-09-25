
@extends('adminlte::page')

@section('title', 'Nuevo calendario académico')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
@stop

@section('content_header')


     <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Nuevo calendario académico</h1>
                    <p class="title-subtitle">¿Cómo quieres cargar el calendario de este año escolar?</p>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('content')

    @unless ($hayAniosDisponibles)
        <div class="alert alert-warning">
            No hay años escolares disponibles — todos ya tienen un calendario asociado,
            o todavía no has creado ninguno en
            <a href="{{ route('admin.anio_escolar.index') }}">Calendario Escolar</a>.
        </div>
    @endunless



    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Subir un PDF</h5>
                    <p class="card-text text-muted flex-grow-1">
                        El sistema extrae automáticamente los días candidatos del calendario
                        oficial (Ministerio) y los deja listos para tu revisión.
                    </p>
                    <a href="{{ route('admin.calendario_academico.create_pdf') }}" class="btn btn-primary">
                        Subir PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-calendar-plus fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Crear manualmente</h5>
                    <p class="card-text text-muted flex-grow-1">
                        Empieza con un calendario vacío y registra cada evento tú mismo,
                        directo sobre el calendario visual.
                    </p>
                    <a href="{{ route('admin.calendario_academico.create_manual') }}" class="btn btn-outline-primary">
                        Crear manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.calendario_academico.index') }}" class="btn btn-secondary btn-md ">
                                <i class="fas fa-arrow-left"></i> Volver al listado
                            </a>

@endsection
