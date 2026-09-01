@extends('adminlte::page')

@section('title', 'Calendarios académicos')

@section('content_header')
    <h1>Calendarios académicos</h1>
@endsection

@section('content')

    @if (session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.calendario_academico.create') }}" class="btn btn-primary">
            Cargar nuevo calendario
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Año escolar</th>
                        <th>Origen</th>
                        <th>Estado</th>
                        <th>Días detectados</th>
                        <th>Días confirmados</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calendarios as $calendario)
                        <tr>
                            <td>
                                {{ \Carbon\Carbon::parse($calendario->anioEscolar->inicio_anio_escolar)->format('Y') }}-{{ \Carbon\Carbon::parse($calendario->anioEscolar->cierre_anio_escolar)->format('Y') }}
                            </td>
                            <td>{{ $calendario->origen->label() }}</td>
                            <td>
                                @if ($calendario->estaConfirmado())
                                    <span class="badge badge-success">Confirmado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente de revisión</span>
                                @endif
                            </td>
                            <td>{{ $calendario->dias_count }}</td>
                            <td>{{ $calendario->dias_confirmados_count }}</td>
                            <td>
                                <a href="{{ route('admin.calendario_academico.show', $calendario) }}" class="btn btn-sm btn-info">
                                    Revisar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Aún no se ha cargado ningún calendario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $calendarios->links() }}
        </div>
    </div>

@endsection


