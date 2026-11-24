@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Calendario académico')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
@stop

@section('content')
    @php
        $fecha = fn ($f) => \Carbon\Carbon::parse($f)->format('d/m/Y');
        $rango = substr($anio['inicio_anio_escolar'], 0, 4) . ' - ' . substr($anio['cierre_anio_escolar'], 0, 4);
    @endphp
    <div class="asistente">
        @include('asistente_anio._pasos', ['pasoActual' => 3])

        <div class="asistente-tarjeta">
            <div class="asistente-cabecera">
                <h1><i class="fas fa-calendar-alt me-2"></i> Calendario académico {{ $rango }}</h1>
                <p>Último paso: revisa todo y guarda. Recién aquí se crea el año escolar con su lapso y su calendario.</p>
            </div>

            <div class="asistente-cuerpo">
                <div class="resumen">
                    <div class="resumen-caja">
                        <h6><i class="fas fa-calendar-plus me-1"></i> Año escolar {{ $rango }}</h6>
                        <p>Del {{ $fecha($anio['inicio_anio_escolar']) }} al {{ $fecha($anio['cierre_anio_escolar']) }}</p>
                        @if (! empty($anio['extencion_anio_escolar']))
                            <p>Extensión hasta el {{ $fecha($anio['extencion_anio_escolar']) }}</p>
                        @endif
                        <p>Quedará <strong>activo</strong>.</p>
                    </div>
                    <div class="resumen-caja">
                        <h6><i class="fas fa-layer-group me-1"></i> {{ $lapso['nombre'] }}</h6>
                        <p>Del {{ $fecha($lapso['fecha_inicio']) }} al {{ $fecha($lapso['fecha_fin']) }}</p>
                        <p>Notas hasta el {{ $fecha($lapso['fecha_limite_carga']) }}</p>
                        <p>{{ $cortes }} cortes de {{ $puntos }} puntos ({{ $cortes * $puntos }} en total).</p>
                    </div>
                    <div class="resumen-caja">
                        <h6><i class="fas fa-calendar-alt me-1"></i> Calendario académico</h6>
                        <p>Se crea para este año, ya confirmado. El lapso 1 se pinta en él.</p>
                        <p>Los días no laborables y efemérides los agregas después, en el calendario.</p>
                    </div>
                </div>

                <form action="{{ route('admin.asistente.guardar') }}" method="POST">
                    @csrf
                    <div class="acciones">
                        <a href="{{ route('admin.asistente.lapso') }}" class="btn-atras">
                            <i class="fas fa-arrow-left me-1"></i> Atrás
                        </a>
                        <button type="submit" class="btn-asistente">
                            <i class="fas fa-check me-1"></i> Guardar todo y entrar al sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
