@extends('adminlte::page')

@section('title', 'Lapsos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .mensaje-error { display: block; color: #dc3545; font-size: 0.8rem; margin-top: 4px; }
        .badge-estado { padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-planificado { background: #e5e7eb; color: #374151; }
        .badge-abierto { background: #d1fae5; color: #065f46; }
        .badge-cerrado { background: #fee2e2; color: #991b1b; }
        .suma-ok { color: #059669; font-weight: 600; }
        .suma-pendiente { color: #d97706; font-weight: 600; }
        .suma-alerta { color: #dc2626; font-weight: 700; }
        .form-control-modern.campo-invalido { border-color: #dc3545; }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="title-main">Lapsos</h1>
                    <p class="title-subtitle">
                        @if ($anio)
                            Año escolar {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                        @else
                            No hay un año escolar seleccionado
                        @endif
                    </p>
                </div>
            </div>
            @can('lapsos.gestionar')
                @if ($puedeCrear && \App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#crearLapso">
                        <i class="fas fa-plus"></i> <span>Nuevo lapso</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        @include('admin.lapsos._mensajes')

        @if ($anio && ! $config)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Este año escolar no tiene configuración de evaluación. Créala primero en
                <a href="{{ route('admin.configuracion_evaluacion.index') }}">Configuración de Evaluación</a>
                para poder cargar sus lapsos.
            </div>
        @endif

                @php
            // Lapsos abiertos cuyos cortes no suman la nota máxima: los docentes cargarían notas sobre una base incompleta.
            $abiertosIncompletos = $config
                ? $lapsos->filter(fn ($l) => $l->estado === \App\Enums\EstadoLapso::Abierto
                    && abs((float) ($l->cortes_sum_peso_lapso ?? 0) - $config->nota_maxima) > 0.001)
                : collect();
        @endphp
        @if ($abiertosIncompletos->isNotEmpty())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i>
                <strong>Atención:</strong>
                @foreach ($abiertosIncompletos as $l)
                    {{ $l->nombre }} está abierto pero sus cortes suman
                    {{ rtrim(rtrim(number_format((float) $l->cortes_sum_peso_lapso, 2, '.', ''), '0'), '.') }} de {{ $config->nota_maxima }} puntos.
                @endforeach
                Complétalos antes de que los docentes carguen notas.
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Lapsos del año</h3>
                        <p class="mb-0 text-muted">
                            {{ $lapsos->count() }}
                            @if ($config) de {{ $config->num_lapsos }} configurados @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="text-align: center">N°</th>
                                <th>Nombre</th>
                                <th style="text-align: center">Desde</th>
                                <th style="text-align: center">Hasta</th>
                                <th style="text-align: center">Límite de carga</th>
                                <th style="text-align: center">Cortes</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center; width: 160px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lapsos as $lapso)
                                @php
                                    $suma = (float) ($lapso->cortes_sum_peso_lapso ?? 0);
                                    $completo = $config && abs($suma - $config->nota_maxima) < 0.001;
                                @endphp
                                <tr>
                                    <td style="text-align: center"><strong>{{ $lapso->numero }}</strong></td>
                                    <td>{{ $lapso->nombre }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_inicio->format('d/m/Y') }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_fin->format('d/m/Y') }}</td>
                                    <td style="text-align: center">{{ $lapso->fecha_limite_carga->format('d/m/Y') }}</td>
                                    <td style="text-align: center">
                                        {{ $lapso->cortes_count }} cortes ·
                                                                                @php $alerta = ! $completo && $lapso->estado === \App\Enums\EstadoLapso::Abierto; @endphp
                                        <span class="{{ $completo ? 'suma-ok' : ($alerta ? 'suma-alerta' : 'suma-pendiente') }}"
                                            @if ($alerta) title="El lapso está abierto y sus cortes no suman la nota máxima" @endif>
                                            @if ($alerta) <i class="fas fa-exclamation-circle"></i> @endif
                                            {{ rtrim(rtrim(number_format($suma, 2, '.', ''), '0'), '.') }} de {{ $config?->nota_maxima ?? '?' }} pts
                                        </span>
                                    </td>
                                    <td style="text-align: center">
                                        <span class="badge-estado badge-{{ $lapso->estado->value }}">{{ $lapso->estado->label() }}</span>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="action-buttons">
                                            @can('cortes.ver')
                                                <a href="{{ route('admin.cortes.index', $lapso) }}" class="action-btn btn-view" title="Cortes del lapso">
                                                    <i class="fas fa-cut"></i>
                                                </a>
                                            @endcan
                                            @can('lapsos.gestionar')
                                                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                    <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#editarLapso{{ $lapso->id }}" title="Editar">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                        data-bs-target="#eliminarLapso{{ $lapso->id }}" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-layer-group"></i></div>
                                            <h4>No hay lapsos registrados</h4>
                                            <p>Crea los lapsos del año escolar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('lapsos.gestionar')
                @if ($puedeCrear)
                    @include('admin.lapsos.modales.createModal')
                @endif
                @foreach ($lapsos as $lapso)
                    @include('admin.lapsos.modales.editModal')
                    @include('admin.lapsos.modales.deleteModal')
                @endforeach
            @endcan
        </div>
    </div>
@stop

@section('js')
    @include('admin.lapsos._reabrir_modal')
    @include('admin.lapsos._validacion_tiempo_real')

    @if ($anio && $config)
        @php
            // Los demás lapsos del año, para revisar en el navegador que no se crucen ni repitan número.
            $datosLapsos = $lapsos->map(fn ($l) => [
                'id' => (string) $l->id,
                'numero' => $l->numero,
                'nombre' => $l->nombre,
                'inicio' => $l->fecha_inicio->toDateString(),
                'fin' => $l->fecha_fin->toDateString(),
            ])->values();
        @endphp
        <script>
            // Validación en tiempo real de los lapsos: las mismas reglas que LapsoRequest.
            document.addEventListener('DOMContentLoaded', () => {
                const anio = { inicio: @json($anio->inicio_anio_escolar->toDateString()), cierre: @json($anio->cierre_anio_escolar->toDateString()) };
                const maxLapsos = {{ $config->num_lapsos }};
                const notaMaxima = {{ (float) $config->nota_maxima }};
                const lapsos = @json($datosLapsos);

                document.querySelectorAll('form.form-lapso').forEach((form) => {
                    ValidacionTiempoReal(form, (f) => {
                        const valor = (nombre) => f.querySelector(`[name="${nombre}"]`).value.trim();
                        const id = f.dataset.lapsoId;
                        const otros = lapsos.filter((l) => l.id !== id);
                        const e = {};

                        const numero = valor('numero');
                        if (numero === '') e.numero = 'Debes indicar el número del lapso.';
                        else if (!/^\d+$/.test(numero)) e.numero = 'Debe ser un número entero.';
                        else if (+numero < 1) e.numero = 'El número del lapso debe ser como mínimo 1.';
                        else if (+numero > maxLapsos) e.numero = `El número del lapso no puede pasar del número de lapsos configurado (${maxLapsos}).`;
                        else if (otros.some((l) => l.numero === +numero)) e.numero = 'Ya existe un lapso con ese número en este año escolar.';

                        if (valor('nombre') === '') e.nombre = 'Debes indicar el nombre del lapso.';

                        const inicio = valor('fecha_inicio');
                        const fin = valor('fecha_fin');
                        const limite = valor('fecha_limite_carga');

                        if (!inicio) e.fecha_inicio = 'Debes indicar la fecha de inicio.';
                        else if (inicio < anio.inicio) e.fecha_inicio = `El lapso no puede empezar antes del inicio del año escolar (${fechaLegible(anio.inicio)}).`;
                        else if (inicio > anio.cierre) e.fecha_inicio = `El lapso no puede empezar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;

                        if (!fin) e.fecha_fin = 'Debes indicar la fecha de fin.';
                        else if (inicio && fin <= inicio) e.fecha_fin = 'La fecha de fin debe ser posterior a la fecha de inicio.';
                        else if (fin > anio.cierre) e.fecha_fin = `El lapso no puede terminar después del cierre del año escolar (${fechaLegible(anio.cierre)}).`;

                        if (!limite) e.fecha_limite_carga = 'Debes indicar la fecha límite de carga de notas.';
                        else if (fin && limite < fin) e.fecha_limite_carga = 'La fecha límite de carga no puede ser anterior al fin del lapso.';

                        // Cruce con otro lapso del año
                        if (inicio && fin && !e.fecha_inicio && !e.fecha_fin) {
                            const cruce = otros.find((l) => l.inicio <= fin && l.fin >= inicio);
                            if (cruce) e.fecha_inicio = `Las fechas se cruzan con las de ${cruce.nombre} (${fechaLegible(cruce.inicio)} al ${fechaLegible(cruce.fin)}).`;
                        }

                        // Cortes que quedarían fuera del lapso
                        const cortesInicio = f.dataset.cortesInicio;
                        const cortesFin = f.dataset.cortesFin;
                        if (!e.fecha_fin && ((cortesInicio && inicio && cortesInicio < inicio) || (cortesFin && fin && cortesFin > fin))) {
                            e.fecha_fin = `Con esas fechas quedarían cortes fuera del lapso (sus cortes van del ${fechaLegible(cortesInicio)} al ${fechaLegible(cortesFin)}). Ajusta primero las fechas de esos cortes.`;
                        }

                        // Abrir exige que los cortes sumen la nota máxima
                        const suma = parseFloat(f.dataset.suma || '0');
                        if (valor('estado') === 'abierto' && Math.abs(suma - notaMaxima) > 0.001) {
                            e.estado = `Para abrir el lapso, sus cortes deben sumar ${numeroLegible(notaMaxima)} puntos (ahora suman ${numeroLegible(suma)}).`;
                        }

                        return e;
                    });
                });
            });
        </script>
    @endif
@stop
