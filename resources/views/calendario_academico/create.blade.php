
@extends('adminlte::page')

@section('title', 'Nuevo calendario académico')

@section('css')

<link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .option-card {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .option-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .option-card .card-body { padding: 2rem; }
        .option-icon {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.9rem;
        }
        .option-icon.is-pdf { background: var(--danger-light); color: var(--danger-pdf); }
        .option-icon.is-manual { background: var(--primary-light); color: var(--primary); }
        .option-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
        }
        .option-card .card-text {
            font-size: 0.9rem;
            color: var(--gray-500);
            font-style: normal;
            margin-bottom: 1.5rem;
        }
        .option-card .btn-outline-create {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .option-card .btn-outline-create:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--primary-dark);
        }
        .option-card .btn-pdf,
        .option-card .btn-create { justify-content: center; }
        .page-actions { margin-top: 0.5rem; }
    </style>
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
            <button class= "btn-create"  onclick="window.location='{{ route('admin.calendario_academico.index') }}'"  class="btn-cancel-modern">

            <i class="fas fa-arrow-left"></i> Volver al listado

        </button>

        </div>

    </div>

@endsection

@section('content')

    @unless ($hayAniosDisponibles)

        <div class="alert-modern alert-warning mb-4" role="alert">

            <div class="alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <div class="alert-content">
                <p>
                    No hay años escolares disponibles — todos ya tienen un calendario asociado,

                    o todavía no has creado ninguno en

                    <a href="{{ route('admin.anio_escolar.index') }}">Calendario Escolar</a>.
                </p>
            </div>

        </div>

    @endunless

    <div class="row">

        <div class="col-md-6 mb-4">

            <div class="card card-modern option-card h-100 text-center">

                <div class="card-body d-flex flex-column">

                    <div class="option-icon is-pdf">
                        <i class="fas fa-file-pdf"></i>
                    </div>

                    <h5 class="card-title">Subir un PDF</h5>

                    <p class="card-text flex-grow-1">

                        El sistema extrae automáticamente los días candidatos del calendario

                        oficial (Ministerio) y los deja listos para tu revisión.

                    </p>

                    <a href="{{ route('admin.calendario_academico.create_pdf') }}" class="btn-pdf justify-content-center">

                        <i class="fas fa-file-upload"></i> Subir PDF

                    </a>

                </div>

            </div>

        </div>

        <div class="col-md-6 mb-4">

            <div class="card card-modern option-card h-100 text-center">

                <div class="card-body d-flex flex-column">

                    <div class="option-icon is-manual">
                        <i class="fas fa-calendar-plus"></i>
                    </div>

                    <h5 class="card-title">Crear manualmente</h5>

                    <p class="card-text flex-grow-1">

                        Empieza con un calendario vacío y registra cada evento tú mismo,

                        directo sobre el calendario visual.

                    </p>

                    <a href="{{ route('admin.calendario_academico.create_manual') }}" class="btn-outline-create">

                        <i class="fas fa-pen"></i> Crear manual

                    </a>

                </div>

            </div>

        </div>

    </div>



@endsection
