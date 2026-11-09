@extends('adminlte::page')

@section('title', 'Cortes de ' . $lapso->nombre)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <style>
        .mensaje-error { display: block; color: #dc3545; font-size: 0.8rem; margin-top: 4px; }
        .badge-estado { padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-planificado { background: #e5e7eb; color: #374151; }
        .badge-abierto { background: #d1fae5; color: #065f46; }
        .badge-cerrado { background: #fee2e2; color: #991b1b; }
        .resumen-puntos { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.25rem; font-weight: 600; }
        .resumen-ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .resumen-pendiente { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .resumen-puntos .cifra { font-size: 1.6rem; font-weight: 800; white-space: nowrap; }
        .resumen-alerta { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .form-control-modern.campo-invalido { border-color: #dc3545; }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-cut"></i>
                </div>
                <div>
                    <h1 class="title-main">Cortes de {{ $lapso->nombre }}</h1>
                    <p class="title-subtitle">
                        Año escolar {{ $lapso->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $lapso->anioEscolar->cierre_anio_escolar->format('Y') }}
                        · del {{ $lapso->fecha_inicio->format('d/m/Y') }} al {{ $lapso->fecha_fin->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.lapsos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a lapsos
                </a>
                @can('cortes.gestionar')
                    @if ($puedeCrear && \App\Support\AnioEscolarSeleccionado::esActual() && $lapso->estado !== \App\Enums\EstadoLapso::Cerrado)
                        <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#crearCorte">
                            <i class="fas fa-plus"></i> <span>Nuevo corte</span>
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">
        @include('partials.alerta-modo-consulta')
        @include('admin.lapsos._mensajes')

        @if ($config)
            @php $texto = fn ($n) => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.'); @endphp
            @php $abiertoIncompleto = $faltan > 0 && $lapso->estado === \App\Enums\EstadoLapso::Abierto; @endphp
            <div class="resumen-puntos {{ $faltan <= 0 ? 'resumen-ok' : ($abiertoIncompleto ? 'resumen-alerta' : 'resumen-pendiente') }}">
                <span class="cifra">{{ $texto($suma) }} / {{ $texto($totalLapso) }}</span>
                <span>
                    @if ($abiertoIncompleto)
                        <i class="fas fa-exclamation-circle"></i> {{ $lapso->nombre }} está abierto pero sus cortes suman {{ $texto($suma) }} de {{ $texto($totalLapso) }}:
                        faltan {{ $texto($faltan) }} puntos. Complétalos antes de que los docentes carguen notas.
                    @elseif ($faltan <= 0)
                        <i class="fas fa-check-circle"></i> Los cortes suman los {{ $texto($totalLapso) }} puntos del lapso: ya se puede abrir.
                    @else
                        <i class="fas fa-exclamation-triangle"></i> Faltan {{ $texto($faltan) }} puntos para completar el lapso.
                    @endif
                </span>
                        </div>
            <p class="text-muted mb-3" style="margin-top: -0.5rem">
                <i class="fas fa-calculator me-1"></i>
                Nota del lapso = suma de los cortes ÷ {{ $config->num_cortes }}
                (ej.: {{ $texto($totalLapso) }} ÷ {{ $config->num_cortes }} = {{ $texto($totalLapso / max(1, $config->num_cortes)) }}).
            </p>
        @else
            <div class="alert alert-warning">
                Este año escolar no tiene configuración de evaluación. Créala primero en
                <a href="{{ route('admin.configuracion_evaluacion.index') }}">Configuración de Evaluación</a>.
            </div>
        @endif

        @if ($lapso->estado === \App\Enums\EstadoLapso::Cerrado)
            <div class="alert alert-secondary">
                <i class="fas fa-lock me-1"></i> {{ $lapso->nombre }} está cerrado: sus cortes ya no se pueden modificar.
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Cortes del lapso</h3>
                        <p class="mb-0 text-muted">
                            {{ $cortes->count() }}
                            @if ($config) de {{ $config->num_cortes }} configurados @endif
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
                                <th style="text-align: center">Vale</th>
                                <th style="text-align: center">Desde</th>
                                <th style="text-align: center">Hasta</th>
                                <th style="text-align: center">Límite de carga</th>
                                <th style="text-align: center">Estado</th>
                                <th style="text-align: center; width: 120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cortes as $corte)
                                <tr>
                                    <td style="text-align: center"><strong>{{ $corte->numero }}</strong></td>
                                    <td>{{ $corte->nombre }}</td>
                                    <td style="text-align: center">{{ rtrim(rtrim(number_format((float) $corte->peso_lapso, 2, '.', ''), '0'), '.') }} pts</td>
                                    <td style="text-align: center">{{ $corte->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                    <td style="text-align: center">{{ $corte->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                                    <td style="text-align: center">{{ $corte->fecha_limite_carga->format('d/m/Y') }}</td>
                                    <td style="text-align: center">
                                        <span class="badge-estado badge-{{ $corte->estado->value }}">{{ $corte->estado->label() }}</span>
                                    </td>
                                    <td style="text-align: center">
                                        @can('cortes.gestionar')
                                            @if (\App\Support\AnioEscolarSeleccionado::esActual() && $lapso->estado !== \App\Enums\EstadoLapso::Cerrado)
                                                <div class="action-buttons">
                                                    <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#editarCorte{{ $corte->id }}" title="Editar">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                        data-bs-target="#eliminarCorte{{ $corte->id }}" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-cut"></i></div>
                                            <h4>Este lapso no tiene cortes</h4>
                                            <p>Crea sus cortes hasta completar los puntos del lapso.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('cortes.gestionar')
                @if ($puedeCrear)
                    @include('admin.cortes.modales.createModal')
                @endif
                @foreach ($cortes as $corte)
                    @include('admin.cortes.modales.editModal')
                    @include('admin.cortes.modales.deleteModal')
                @endforeach
            @endcan
        </div>
    </div>
@stop

@section('js')
    @include('admin.lapsos._reabrir_modal')
    @include('admin.lapsos._validacion_tiempo_real')

    @if ($config)
        @php
            // Los cortes del lapso, para revisar en el navegador números repetidos y puntos disponibles.
            $datosCortes = $cortes->map(fn ($c) => [
                'id' => (string) $c->id,
                'numero' => $c->numero,
                'peso' => (float) $c->peso_lapso,
            ])->values();
        @endphp
        <script>
            // Validación en tiempo real de los cortes: las mismas reglas que CorteRequest.
            document.addEventListener('DOMContentLoaded', () => {
                const lapso = { inicio: @json($lapso->fecha_inicio->toDateString()), fin: @json($lapso->fecha_fin->toDateString()) };
                const maxCortes = {{ $config->num_cortes }};
                const totalLapso = {{ (float) $totalLapso }};
                const cortes = @json($datosCortes);

                document.querySelectorAll('form.form-corte').forEach((form) => {
                    ValidacionTiempoReal(form, (f) => {
                        const valor = (nombre) => f.querySelector(`[name="${nombre}"]`).value.trim();
                        const otros = cortes.filter((c) => c.id !== f.dataset.corteId);
                        const e = {};

                        const numero = valor('numero');
                        if (numero === '') e.numero = 'Debes indicar el número del corte.';
                        else if (!/^\d+$/.test(numero)) e.numero = 'Debe ser un número entero.';
                        else if (+numero < 1) e.numero = 'El número del corte debe ser como mínimo 1.';
                        else if (+numero > maxCortes) e.numero = `El número del corte no puede pasar del número de cortes configurado (${maxCortes}).`;
                        else if (otros.some((c) => c.numero === +numero)) e.numero = 'Ya existe un corte con ese número en este lapso.';

                        if (valor('nombre') === '') e.nombre = 'Debes indicar el nombre del corte.';

                        // Puntos: más de 0 y sin pasar de lo que dejan libre los demás cortes
                        const peso = valor('peso_lapso');
                        const disponible = totalLapso - otros.reduce((total, c) => total + c.peso, 0);
                        if (peso === '') e.peso_lapso = 'Debes indicar cuántos puntos vale el corte.';
                        else if (isNaN(peso)) e.peso_lapso = 'Los puntos deben ser un número.';
                        else if (+peso <= 0) e.peso_lapso = 'El corte debe valer más de 0 puntos.';
                        else if (+peso - disponible > 0.001) e.peso_lapso = `Los cortes de este lapso no pueden sumar más de ${numeroLegible(totalLapso)} puntos: a este corte le quedan como máximo ${numeroLegible(disponible)}.`;

                        // Fechas (opcionales) dentro del lapso
                        const inicio = valor('fecha_inicio');
                        const fin = valor('fecha_fin');
                        const limite = valor('fecha_limite_carga');
                        const rango = `(${fechaLegible(lapso.inicio)} al ${fechaLegible(lapso.fin)})`;

                        if (inicio && inicio < lapso.inicio) e.fecha_inicio = `El corte no puede empezar antes que el lapso ${rango}.`;
                        else if (inicio && inicio > lapso.fin) e.fecha_inicio = `El corte no puede empezar después de que termine el lapso ${rango}.`;

                        if (fin && fin < lapso.inicio) e.fecha_fin = `El corte no puede terminar antes de que empiece el lapso ${rango}.`;
                        else if (fin && fin > lapso.fin) e.fecha_fin = `El corte no puede terminar después del lapso ${rango}.`;
                        else if (inicio && fin && fin < inicio) e.fecha_fin = 'La fecha de fin no puede ser anterior a la fecha de inicio.';

                        if (!limite) e.fecha_limite_carga = 'Debes indicar la fecha límite de carga de notas.';
                        else if (fin && limite < fin) e.fecha_limite_carga = 'La fecha límite de carga no puede ser anterior al fin del corte.';

                        return e;
                    });
                });
            });
        </script>
    @endif
@stop
