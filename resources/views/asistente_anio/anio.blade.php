@extends('adminlte::page')

@section('title', 'Nuevo año escolar · Año escolar')

@section('classes_body', 'sidebar-collapse')

@section('css')
    @include('asistente_anio._estilos')
@stop

@section('content')
    @php
        // Lo escrito antes (si vuelve con "Atrás") o lo que mandó con errores.
        $valor = fn ($campo) => old($campo, $borrador[$campo] ?? '');
    @endphp
    <div class="asistente">
        @include('asistente_anio._pasos', ['pasoActual' => 1])

        @if (session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}</div>
        @endif

        <div class="asistente-tarjeta">
            <div class="asistente-cabecera">
                <h1><i class="fas fa-calendar-plus me-2"></i> Nuevo año escolar</h1>
                <p>Escribe las fechas del año. Nada se guarda hasta el último paso: puedes volver atrás cuando quieras.</p>
            </div>

            <div class="asistente-cuerpo">
                @if ($anioActivo)
                    <div class="nota-info">
                        <i class="fas fa-info-circle me-1"></i>
                        El año escolar {{ $anioActivo->inicio_anio_escolar->format('Y') }} - {{ $anioActivo->cierre_anio_escolar->format('Y') }}
                        está activo: al guardar, el nuevo quedará activo y ese pasará a inactivo.
                    </div>
                @endif

                <form action="{{ route('admin.asistente.anio.store') }}" method="POST" id="form-asistente-anio">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 form-group-modern">
                            <label class="form-label-modern" for="inicio_anio_escolar">Fecha de inicio</label>
                            <input type="date" class="form-control-modern" id="inicio_anio_escolar" name="inicio_anio_escolar"
                                value="{{ $valor('inicio_anio_escolar') }}" required>
                            @error('inicio_anio_escolar') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4 form-group-modern">
                            <label class="form-label-modern" for="cierre_anio_escolar">Fecha de cierre</label>
                            <input type="date" class="form-control-modern" id="cierre_anio_escolar" name="cierre_anio_escolar"
                                value="{{ $valor('cierre_anio_escolar') }}" required>
                            @error('cierre_anio_escolar') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4 form-group-modern">
                            <label class="form-label-modern" for="extencion_anio_escolar">Fecha de extensión <span class="text-muted">(opcional)</span></label>
                            <input type="date" class="form-control-modern" id="extencion_anio_escolar" name="extencion_anio_escolar"
                                value="{{ $valor('extencion_anio_escolar') }}">
                            @error('extencion_anio_escolar') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="acciones">
                        <a href="{{ route('admin.anio-escolar.seleccionar') }}" class="btn-atras">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-asistente">
                            Siguiente: lapso 1 <i class="fas fa-arrow-right ms-1"></i>
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
        // Validación en tiempo real: las mismas reglas que AsistenteAnioRequest.
        document.addEventListener('DOMContentLoaded', () => {
            ValidacionTiempoReal(document.getElementById('form-asistente-anio'), (f) => {
                const valor = (campo) => f.querySelector(`[name="${campo}"]`).value;
                const inicio = valor('inicio_anio_escolar'), cierre = valor('cierre_anio_escolar'), extension = valor('extencion_anio_escolar');
                const e = {};

                if (!inicio) e.inicio_anio_escolar = 'La fecha de inicio del año escolar es obligatoria.';
                if (!cierre) e.cierre_anio_escolar = 'La fecha de cierre del año escolar es obligatoria.';
                else if (inicio && cierre <= inicio) e.cierre_anio_escolar = 'La fecha de cierre debe ser posterior a la fecha de inicio.';
                if (extension && cierre && extension <= cierre) e.extencion_anio_escolar = 'La fecha de extensión debe ser posterior a la fecha de cierre.';

                return e;
            });
        });
    </script>
@stop
