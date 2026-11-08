@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Calendario académico')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
    @include('calendario_academico._colores_lapsos')
    <style>
        .lista-lapsos { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem; }
        .lapso-resumen { border-radius: 12px; padding: 0.8rem 1rem; border: 1px solid #e5e7eb; }
        .lapso-resumen strong { display: block; }
    </style>
@stop

@section('content')
    @php $rango = $anio->inicio_anio_escolar->format('Y') . ' - ' . $anio->cierre_anio_escolar->format('Y'); @endphp
    <div class="asistente">
        @include('asistente_anio._pasos', ['pasoActual' => 3])

        @if (session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
        @endif

        <div class="asistente-tarjeta">
            <div class="asistente-cabecera">
                <h1><i class="fas fa-calendar-alt me-2"></i> Calendario académico {{ $rango }}</h1>
                <p>Último paso: crea el calendario del año. Sus lapsos se pintarán en él.</p>
            </div>

            <div class="asistente-cuerpo">
                <div class="lista-lapsos">
                    @foreach ($lapsos as $lapso)
                        <div class="lapso-resumen lapso-color-{{ ($lapso->numero - 1) % 4 + 1 }}">
                            <strong>{{ $lapso->nombre }}</strong>
                            {{ $lapso->fecha_inicio->format('d/m/Y') }} al {{ $lapso->fecha_fin->format('d/m/Y') }}
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('admin.asistente.calendario.store') }}" method="POST">
                    @csrf
                    <div class="form-group-modern" style="max-width: 420px">
                        <label for="anio_escolar_id" class="form-label-modern">Año escolar</label>
                        {{-- Solo el año que se acaba de crear --}}
                        <select id="anio_escolar_id" class="form-control-modern" disabled>
                            <option selected>
                                {{ $rango }} ({{ $anio->inicio_anio_escolar->format('d/m/Y') }} al {{ $anio->cierre_anio_escolar->format('d/m/Y') }})
                            </option>
                        </select>
                        <small class="text-muted d-block mt-1">Es el año que acabas de crear. Los días no laborables y efemérides los agregas después, en el calendario.</small>
                    </div>

                    <div class="acciones">
                        <button type="submit" class="btn-asistente">
                            <i class="fas fa-check me-1"></i> Crear calendario y entrar al sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
