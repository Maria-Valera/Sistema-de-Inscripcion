@extends('adminlte::page')

@section('title', 'Crear calendario manual')

@section('content_header')
    <h1>Crear calendario manual</h1>
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

            <p class="text-muted">
                Se creará un calendario vacío para el año escolar que elijas. Después podrás
                registrar cada evento directamente sobre el calendario visual.
            </p>

            <form action="{{ route('admin.calendario_academico.store_manual') }}" method="POST">
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

                <button type="submit" class="btn btn-primary">
                    Crear calendario manual
                </button>
                <a href="{{ route('admin.calendario_academico.create') }}" class="btn btn-default">Volver</a>
            </form>

        </div>
    </div>
@endsection
