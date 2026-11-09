@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Lapsos')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
@stop

@section('content')
    @php
        $rango = $anio->inicio_anio_escolar->format('Y') . ' - ' . $anio->cierre_anio_escolar->format('Y');
        $usarOld = old('lapsos') !== null;
    @endphp
    <div class="asistente">
        @include('asistente_anio._pasos', ['pasoActual' => 2])

        <div class="asistente-tarjeta">
            <div class="asistente-cabecera">
                <h1><i class="fas fa-layer-group me-2"></i> Lapsos del año escolar {{ $rango }}</h1>
                <p>Del {{ $anio->inicio_anio_escolar->format('d/m/Y') }} al {{ $anio->cierre_anio_escolar->format('d/m/Y') }}. Revisa las fechas propuestas y ajústalas si hace falta.</p>
            </div>

            <div class="asistente-cuerpo">
                <div class="nota-info">
                    <i class="fas fa-info-circle me-1"></i>
                    @if ($origen)
                        Se usa la misma configuración de evaluación del año {{ $origen->inicio_anio_escolar->format('Y') }} - {{ $origen->cierre_anio_escolar->format('Y') }}:
                    @else
                        Se usa la configuración de evaluación por defecto:
                    @endif
                    <strong>{{ $config->num_lapsos }} lapsos</strong>, cada uno con
                    <strong>{{ $config->num_cortes }} cortes</strong> que suman {{ $config->nota_maxima }} puntos.
                    Los cortes se crean solos; podrás ajustarlos luego en <em>Lapsos y Cortes</em>.
                </div>

                <form action="{{ route('admin.asistente.lapsos.store') }}" method="POST" id="form-asistente-lapsos">
                    @csrf

                    @foreach ($propuesta as $i => $lapso)
                        @php
                            $valor = fn ($campo) => $usarOld ? old("lapsos.{$i}.{$campo}") : $lapso[$campo];
                            $error = fn ($campo) => $errors->first("lapsos.{$i}.{$campo}");
                        @endphp
                        <div class="row align-items-start {{ $loop->last ? '' : 'mb-2' }}">
                            <div class="col-md-3 form-group-modern">
                                <label class="form-label-modern" for="lapso_{{ $i }}_nombre">Nombre</label>
                                <input type="text" class="form-control-modern" id="lapso_{{ $i }}_nombre" name="lapsos[{{ $i }}][nombre]"
                                    maxlength="50" value="{{ $valor('nombre') }}" required>
                                @if ($error('nombre')) <small class="mensaje-error">{{ $error('nombre') }}</small> @endif
                            </div>
                            <div class="col-md-3 form-group-modern">
                                <label class="form-label-modern" for="lapso_{{ $i }}_inicio">Desde</label>
                                <input type="date" class="form-control-modern" id="lapso_{{ $i }}_inicio" name="lapsos[{{ $i }}][fecha_inicio]"
                                    value="{{ $valor('fecha_inicio') }}" required>
                                @if ($error('fecha_inicio')) <small class="mensaje-error">{{ $error('fecha_inicio') }}</small> @endif
                            </div>
                            <div class="col-md-3 form-group-modern">
                                <label class="form-label-modern" for="lapso_{{ $i }}_fin">Hasta</label>
                                <input type="date" class="form-control-modern" id="lapso_{{ $i }}_fin" name="lapsos[{{ $i }}][fecha_fin]"
                                    value="{{ $valor('fecha_fin') }}" required>
                                @if ($error('fecha_fin')) <small class="mensaje-error">{{ $error('fecha_fin') }}</small> @endif
                            </div>
                            <div class="col-md-3 form-group-modern">
                                <label class="form-label-modern" for="lapso_{{ $i }}_limite">Límite para cargar notas</label>
                                <input type="date" class="form-control-modern" id="lapso_{{ $i }}_limite" name="lapsos[{{ $i }}][fecha_limite_carga]"
                                    value="{{ $valor('fecha_limite_carga') }}" required>
                                @if ($error('fecha_limite_carga')) <small class="mensaje-error">{{ $error('fecha_limite_carga') }}</small> @endif
                            </div>
                        </div>
                    @endforeach

                    @if ($errors->has('lapsos'))
                        <small class="mensaje-error">{{ $errors->first('lapsos') }}</small>
                    @endif

                    <div class="acciones">
                        <button type="submit" class="btn-asistente">
                            Guardar lapsos y seguir <i class="fas fa-arrow-right ms-1"></i>
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
        // Validación en tiempo real: las mismas reglas que AsistenteLapsosRequest.
        document.addEventListener('DOMContentLoaded', () => {
            const anio = { inicio: @json($anio->inicio_anio_escolar->toDateString()), cierre: @json($anio->cierre_anio_escolar->toDateString()) };
            const cantidad = {{ count($propuesta) }};
            const form = document.getElementById('form-asistente-lapsos');

            ValidacionTiempoReal(form, (f) => {
                const valor = (i, campo) => f.querySelector(`[name="lapsos[${i}][${campo}]"]`).value.trim();
                const e = {};

                for (let i = 0; i < cantidad; i++) {
                    const campo = (c) => `lapsos[${i}][${c}]`;
                    const nombre = valor(i, 'nombre');
                    const inicio = valor(i, 'fecha_inicio');
                    const fin = valor(i, 'fecha_fin');
                    const limite = valor(i, 'fecha_limite_carga');

                    if (nombre === '') e[campo('nombre')] = 'Debes indicar el nombre del lapso.';

                    if (!inicio) e[campo('fecha_inicio')] = 'Debes indicar la fecha de inicio.';
                    else if (inicio < anio.inicio) e[campo('fecha_inicio')] = `No puede empezar antes del inicio del año escolar (${fechaLegible(anio.inicio)}).`;
                    else if (inicio > anio.cierre) e[campo('fecha_inicio')] = `No puede empezar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;
                    else if (i > 0) {
                        const finAnterior = valor(i - 1, 'fecha_fin');
                        if (finAnterior && inicio <= finAnterior) e[campo('fecha_inicio')] = `Debe empezar después de que termine ${valor(i - 1, 'nombre') || 'el lapso anterior'} (${fechaLegible(finAnterior)}).`;
                    }

                    if (!fin) e[campo('fecha_fin')] = 'Debes indicar la fecha de fin.';
                    else if (inicio && fin <= inicio) e[campo('fecha_fin')] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
                    else if (fin > anio.cierre) e[campo('fecha_fin')] = `No puede terminar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;

                    if (!limite) e[campo('fecha_limite_carga')] = 'Debes indicar la fecha límite de carga de notas.';
                    else if (fin && limite < fin) e[campo('fecha_limite_carga')] = 'La fecha límite de carga no puede ser anterior al fin del lapso.';
                }

                return e;
            });
        });
    </script>
@stop
