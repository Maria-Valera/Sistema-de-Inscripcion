@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Lapso 1')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
@stop

@section('content')
    @php
        $fecha = fn ($f) => \Carbon\Carbon::parse($f)->format('d/m/Y');
        $rango = substr($anio['inicio_anio_escolar'], 0, 4) . ' - ' . substr($anio['cierre_anio_escolar'], 0, 4);
        // Lo escrito antes (si vuelve con "Atrás"), lo propuesto, o lo que mandó con errores.
        $valor = fn ($campo) => old($campo, $lapso[$campo]);
    @endphp
    <div class="asistente">
        @include('asistente_anio._pasos', ['pasoActual' => 2])

        <div class="asistente-tarjeta">
            <div class="asistente-cabecera">
                <h1><i class="fas fa-layer-group me-2"></i> Lapso 1 del año escolar {{ $rango }}</h1>
                <p>Del {{ $fecha($anio['inicio_anio_escolar']) }} al {{ $fecha($anio['cierre_anio_escolar']) }}. Revisa las fechas propuestas y ajústalas si hace falta.</p>
            </div>

            <div class="asistente-cuerpo">
                <div class="nota-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Aquí se crea <strong>el lapso 1</strong>, con <strong>{{ \App\Models\Corte::CANTIDAD_POR_DEFECTO }} cortes de {{ \App\Models\Corte::PUNTOS_POR_DEFECTO }} puntos</strong>
                    que se crean solos. Los demás lapsos los agregas después, dentro del sistema, en <em>Lapsos</em>.
                    @if ($origen)
                        La configuración de evaluación se copia del año {{ $origen->inicio_anio_escolar->format('Y') }} - {{ $origen->cierre_anio_escolar->format('Y') }}.
                    @else
                        Se usa la configuración de evaluación por defecto.
                    @endif
                </div>

                <form action="{{ route('admin.asistente.lapso.store') }}" method="POST" id="form-asistente-lapso">
                    @csrf

                    <div class="row align-items-start">
                        <div class="col-md-3 form-group-modern">
                            <label class="form-label-modern" for="nombre">Nombre</label>
                            <input type="text" class="form-control-modern" id="nombre" name="nombre" maxlength="50" value="{{ $valor('nombre') }}" required>
                            @error('nombre') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 form-group-modern">
                            <label class="form-label-modern" for="fecha_inicio">Desde</label>
                            <input type="date" class="form-control-modern" id="fecha_inicio" name="fecha_inicio" value="{{ $valor('fecha_inicio') }}" required>
                            @error('fecha_inicio') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 form-group-modern">
                            <label class="form-label-modern" for="fecha_fin">Hasta</label>
                            <input type="date" class="form-control-modern" id="fecha_fin" name="fecha_fin" value="{{ $valor('fecha_fin') }}" required>
                            @error('fecha_fin') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3 form-group-modern">
                            <label class="form-label-modern" for="fecha_limite_carga">Límite para cargar notas</label>
                            <input type="date" class="form-control-modern" id="fecha_limite_carga" name="fecha_limite_carga" value="{{ $valor('fecha_limite_carga') }}" required>
                            @error('fecha_limite_carga') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="acciones">
                        <a href="{{ route('admin.asistente.anio') }}" class="btn-atras">
                            <i class="fas fa-arrow-left me-1"></i> Atrás
                        </a>
                        <button type="submit" class="btn-asistente">
                            Siguiente: calendario <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    @include('admin.lapsos._validacion_tiempo_real')
    <script>
        // Validación en tiempo real: las mismas reglas que AsistenteLapsoRequest.
        document.addEventListener('DOMContentLoaded', () => {
            const anio = { inicio: @json($anio['inicio_anio_escolar']), cierre: @json($anio['cierre_anio_escolar']) };

            ValidacionTiempoReal(document.getElementById('form-asistente-lapso'), (f) => {
                const valor = (campo) => f.querySelector(`[name="${campo}"]`).value.trim();
                const nombre = valor('nombre'), inicio = valor('fecha_inicio'), fin = valor('fecha_fin'), limite = valor('fecha_limite_carga');
                const e = {};

                if (nombre === '') e.nombre = 'Debes indicar el nombre del lapso.';

                if (!inicio) e.fecha_inicio = 'Debes indicar la fecha de inicio.';
                else if (inicio < anio.inicio) e.fecha_inicio = `No puede empezar antes del inicio del año escolar (${fechaLegible(anio.inicio)}).`;
                else if (inicio > anio.cierre) e.fecha_inicio = `No puede empezar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;

                if (!fin) e.fecha_fin = 'Debes indicar la fecha de fin.';
                else if (inicio && fin <= inicio) e.fecha_fin = 'La fecha de fin debe ser posterior a la fecha de inicio.';
                else if (fin > anio.cierre) e.fecha_fin = `No puede terminar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;

                if (!limite) e.fecha_limite_carga = 'Debes indicar la fecha límite de carga de notas.';
                else if (fin && limite < fin) e.fecha_limite_carga = 'La fecha límite de carga no puede ser anterior al fin del lapso.';

                return e;
            });
        });
    </script>
@stop
