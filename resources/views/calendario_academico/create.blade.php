@extends('adminlte::page')

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
@endsection
