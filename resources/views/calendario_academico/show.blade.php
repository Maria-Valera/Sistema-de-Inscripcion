@extends('adminlte::page')

@section('title', 'Revisar calendario académico')

@section('css')
    {{-- Estilos modernos de referencia --}}
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">

    {{-- Estilos específicos del calendario (se mantienen) --}}
    <style>
        .calendario-cabecera-semana,
        .calendario-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        .calendario-cabecera-semana > div {
            text-align: center;
            font-weight: 600;
            padding: 6px 0;
            color: #6c757d;
            font-size: 0.85rem;
        }
        .celda-dia {
            min-height: 90px;
            border: 1px solid #e9ecef;
            padding: 4px;
            overflow: hidden;
            background: #fff;
        }
        .celda-vacia {
            background: #fafafa;
        }
        .celda-con-eventos {
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }
        .celda-con-eventos:hover {
            background-color: #f0f7ff;
        }
        .numero-dia {
            font-size: 0.8rem;
            color: #495057;
            margin-bottom: 2px;
        }
        .evento-chip {
            font-size: 0.7rem;
            padding: 1px 5px;
            border-radius: 4px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #fff;
        }
        .evento-chip.evento-no-laborable { background-color: #dc3545; }
        .evento-chip.evento-dudoso { background-color: #ffc107; color: #212529; }
        .evento-chip.evento-confirmado { background-color: #28a745 !important; color: #fff !important; }
        .calendario-leyenda-punto {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
        }
        .calendario-leyenda-punto.evento-no-laborable { background-color: #dc3545; }
        .calendario-leyenda-punto.evento-dudoso { background-color: #ffc107; }
        .calendario-leyenda-punto.evento-confirmado { background-color: #28a745; }

        /* Modal de detalle */
        #modal-dia-cuerpo .fila-evento {
            padding: 12px 0;
        }
        #modal-dia-cuerpo .fila-evento:not(:last-child) {
            border-bottom: 1px solid #e9ecef;
        }
        #modal-dia-cuerpo .fila-evento-texto {
            display: block;
            margin-bottom: 8px;
            word-break: break-word;
            font-size: 0.95rem;
        }
        #modal-dia-cuerpo .fila-evento-badges {
            margin-bottom: 10px;
        }
        #modal-dia-cuerpo .fila-evento-badges .badge {
            margin-right: 4px;
            margin-bottom: 4px;
            font-weight: 500;
        }
        #modal-dia-cuerpo .badge-light {
            background-color: #e9ecef;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        #modal-dia-cuerpo .fila-evento-accion {
            display: flex;
            justify-content: flex-end;
        }
        #modal-dia-cuerpo .fila-evento-accion .btn {
            min-width: 130px;
        }

        /* Ajustes menores para el calendario dentro del card-body-modern */
        .calendario-titulo-mes {
            font-weight: 600;
        }
        .btn-outline-secondary.btn-sm {
            border-radius: 50px;
            padding: 0.25rem 1rem;
        }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <h1 class="title-main">Revisar calendario académico</h1>
                    <p class="title-subtitle">
                        {{ \Carbon\Carbon::parse($calendario->anioEscolar->inicio_anio_escolar)->format('Y') }} -
                        {{ \Carbon\Carbon::parse($calendario->anioEscolar->cierre_anio_escolar)->format('Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="main-container">

        {{-- Alerta de éxito con estilo moderno --}}
        @if (session('exito'))
            <div class="alerts-container">
                <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('exito') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <div class="card-modern">
            {{-- Cabecera de la tarjeta --}}
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Revisión de días</h3>
                        <p class="mb-0 text-muted">
                            {{ $calendario->dias()->count() }} candidatos detectados
                        </p>
                    </div>
                </div>
                <div class="header-right d-flex align-items-center gap-2 flex-wrap">
                    @if ($calendario->estaConfirmado())
                        <span class="status-badge status-active">
                            <span class="status-dot"></span> Confirmado
                            <span class="text-muted ms-1" style="font-size:0.8rem;">
                                el {{ $calendario->fecha_confirmacion?->format('d/m/Y') }}
                            </span>
                        </span>
                    @else
                        <span class="status-badge status-inactive">
                            <span class="status-dot"></span> Pendiente de revisión
                        </span>
                    @endif
                </div>
            </div>

            {{-- Pestañas --}}
            <ul class="nav nav-tabs" id="tabs-revision" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-lista-link" data-toggle="tab" href="#tab-lista" role="tab">
                        <i class="fas fa-list mr-1"></i> Lista
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-calendario-link" data-toggle="tab" href="#tab-calendario" role="tab">
                        <i class="fas fa-calendar-alt mr-1"></i> Calendario
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- ===================== VISTA DE LISTA ===================== --}}
                <div class="tab-pane fade show active" id="tab-lista" role="tabpanel">
                    <form action="{{ route('admin.calendario_academico.confirmar', $calendario) }}" method="POST">
                        @csrf
                        <div class="card-body-modern">
                            @forelse ($candidatosPorMes as $mes => $dias)
                                <h5 class="mt-3">{{ $mes ?? 'Sin mes detectado' }}</h5>
                                <div class="table-wrapper">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px"></th>
                                                <th>Fecha</th>
                                                <th>Descripción</th>
                                                <th>Categoría</th>
                                                <th>Confianza</th>
                                                <th>Aplica a</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dias as $dia)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="ids_aprobados[]" value="{{ $dia->id }}"
                                                            {{ $dia->confirmado || $dia->confianza === \App\Enums\ConfianzaDia::Alta ? 'checked' : '' }}
                                                            {{ $calendario->estaConfirmado() ? 'disabled' : '' }}>
                                                    </td>
                                                    <td>
                                                        @if ($dia->es_mes_completo)
                                                            <span class="text-muted">Mes completo</span>
                                                        @else
                                                            {{ $dia->fecha?->format('d/m/Y') ?? '—' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $dia->texto_extraido }}</td>
                                                    <td>
                                                        <span class="badge {{ $dia->categoria === \App\Enums\CategoriaDia::NoLaborable ? 'badge-danger' : 'badge-secondary' }}">
                                                            {{ $dia->categoria->label() }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge
                                                            @switch($dia->confianza)
                                                                @case(\App\Enums\ConfianzaDia::Alta) badge-success @break
                                                                @case(\App\Enums\ConfianzaDia::Dudosa) badge-warning @break
                                                                @default badge-info
                                                            @endswitch
                                                        ">
                                                            {{ $dia->confianza->label() }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($dia->aplica_personal) <span class="badge badge-info">Personal</span> @endif
                                                        @if ($dia->aplica_estudiantes) <span class="badge badge-info">Estudiantes</span> @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @empty
                                <p class="text-muted">No se detectaron candidatos en este PDF.</p>
                            @endforelse
                        </div>

                        <div class="card-footer-modern d-flex flex-wrap align-items-center justify-content-between m-4 p-2">
                            @unless ($calendario->estaConfirmado())
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check-circle btn-md "></i> Confirmar calendario
                                </button>
                            @endunless
                            <a href="{{ route('admin.calendario_academico.index') }}" class="btn btn-secondary btn-md ">
                                <i class="fas fa-arrow-left"></i> Volver al listado
                            </a>
                        </div>
                    </form>
                </div>

                {{-- ===================== VISTA DE CALENDARIO ===================== --}}
                <div class="tab-pane fade m-3" id="tab-calendario" role="tabpanel">
                    <div class="card-body-modern">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" id="btn-mes-anterior" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-chevron-left"></i> Mes anterior
                            </button>
                            <h4 id="calendario-titulo" class="mb-0 text-capitalize calendario-titulo-mes"></h4>
                            <button type="button" id="btn-mes-siguiente" class="btn btn-outline-secondary btn-sm">
                                Mes siguiente <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="calendario-cabecera-semana">
                            <div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sá</div><div>Do</div>
                        </div>
                        <div id="calendario-grid" class="calendario-grid"></div>
                        <div class=" m-4 ">
                            <span class="calendario-leyenda-punto evento-no-laborable"></span> No laborable
                            <span class="calendario-leyenda-punto evento-dudoso ml-3"></span> Dudoso
                            <span class="calendario-leyenda-punto evento-confirmado ml-3"></span> Confirmado
                            <span class="text-muted ml-3">— haz clic en un día con eventos para revisarlo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de detalle del día (con estilo moderno) --}}
    <div class="modal fade" id="modal-dia" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-modern">
                <div class="modal-header">
                    <h5 class="modal-title text-capitalize" id="modal-dia-titulo"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-dia-cuerpo"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default " data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- ===================== JS  ===================== --}}
@section('js')
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const DIAS = {!! $diasParaCalendarioJson !!};
        const INICIO_ANIO_ESCOLAR = '{{ \Carbon\Carbon::parse($inicioAnioEscolar)->toDateString() }}';
        const CIERRE_ANIO_ESCOLAR = '{{ \Carbon\Carbon::parse($cierreAnioEscolar)->toDateString() }}';
        const URL_CONFIRMAR_DIA_BASE = '{{ url('admin/calendario_academico/'.$calendario->id.'/dias') }}';
        const CALENDARIO_CONFIRMADO = {{ $calendario->estaConfirmado() ? 'true' : 'false' }};

        let mesActual;

        function claveMes(fecha) {
            return fecha.getFullYear() * 12 + fecha.getMonth();
        }

        function formatearFecha(anio, mes, dia) {
            return `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        }

        function crearCeldaVacia() {
            const div = document.createElement('div');
            div.className = 'celda-dia celda-vacia';
            return div;
        }

        function crearCeldaDia(numeroDia, fechaStr, eventos) {
            const div = document.createElement('div');
            div.className = 'celda-dia';

            const numero = document.createElement('div');
            numero.className = 'numero-dia';
            numero.textContent = numeroDia;
            div.appendChild(numero);

            eventos.forEach(evento => {
                const chip = document.createElement('div');
                chip.className = 'evento-chip ' + (evento.confirmado
                    ? 'evento-confirmado'
                    : (evento.categoria === 'no_laborable' ? 'evento-no-laborable' : 'evento-dudoso'));
                chip.textContent = evento.texto;
                chip.title = evento.texto;
                div.appendChild(chip);
            });

            if (eventos.length > 0) {
                div.classList.add('celda-con-eventos');
                div.addEventListener('click', () => abrirModalDia(fechaStr, eventos));
            }

            return div;
        }

        function renderizarCalendario() {
            const contenedor = document.getElementById('calendario-grid');
            contenedor.innerHTML = '';

            const anio = mesActual.getFullYear();
            const mes = mesActual.getMonth();

            document.getElementById('calendario-titulo').textContent =
                mesActual.toLocaleDateString('es-VE', { month: 'long', year: 'numeric' });

            const primerDiaMes = new Date(anio, mes, 1);
            const ultimoDiaMes = new Date(anio, mes + 1, 0);

            let diaSemanaInicio = primerDiaMes.getDay();
            diaSemanaInicio = diaSemanaInicio === 0 ? 6 : diaSemanaInicio - 1;

            for (let i = 0; i < diaSemanaInicio; i++) {
                contenedor.appendChild(crearCeldaVacia());
            }

            for (let dia = 1; dia <= ultimoDiaMes.getDate(); dia++) {
                const fechaStr = formatearFecha(anio, mes, dia);
                const eventosDelDia = DIAS.filter(d => d.fecha === fechaStr);
                contenedor.appendChild(crearCeldaDia(dia, fechaStr, eventosDelDia));
            }

            document.getElementById('btn-mes-anterior').disabled =
                claveMes(mesActual) <= claveMes(new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00'));
            document.getElementById('btn-mes-siguiente').disabled =
                claveMes(mesActual) >= claveMes(new Date(CIERRE_ANIO_ESCOLAR + 'T00:00:00'));
        }

        function abrirModalDia(fechaStr, eventos) {
            const cuerpo = document.getElementById('modal-dia-cuerpo');
            cuerpo.innerHTML = '';

            const fechaFormateada = new Date(fechaStr + 'T00:00:00')
                .toLocaleDateString('es-VE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('modal-dia-titulo').textContent = fechaFormateada;

            eventos.forEach(evento => {
                const fila = document.createElement('div');
                fila.className = 'fila-evento';

                const info = document.createElement('div');
                info.innerHTML = `
                    <span class="fila-evento-texto">${evento.texto}</span>
                    <div class="fila-evento-badges">
                        <span class="badge ${evento.categoria === 'no_laborable' ? 'badge-danger' : 'badge-secondary'}">${evento.categoriaLabel}</span>
                        <span class="badge badge-light">${evento.confianzaLabel}</span>
                    </div>
                `;

                const accion = document.createElement('div');
                accion.className = 'fila-evento-accion';

                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'btn btn-sm ' + (evento.confirmado ? 'btn-outline-secondary' : 'btn-success');
                boton.textContent = evento.confirmado ? 'Quitar confirmación' : 'Confirmar';
                boton.disabled = CALENDARIO_CONFIRMADO;
                boton.addEventListener('click', () => alternarConfirmacion(evento, boton));
                accion.appendChild(boton);

                fila.appendChild(info);
                fila.appendChild(accion);
                cuerpo.appendChild(fila);
            });

            $('#modal-dia').modal('show');
        }

        function alternarConfirmacion(evento, boton) {
            const nuevoValor = !evento.confirmado;
            boton.disabled = true;

            fetch(`${URL_CONFIRMAR_DIA_BASE}/${evento.id}/confirmar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ confirmado: nuevoValor }),
            })
                .then(respuesta => {
                    if (!respuesta.ok) throw new Error('No se pudo actualizar');
                    return respuesta.json();
                })
                .then(datos => {
                    evento.confirmado = datos.confirmado;
                    boton.textContent = datos.confirmado ? 'Quitar confirmación' : 'Confirmar';
                    boton.className = 'btn btn-sm ' + (datos.confirmado ? 'btn-outline-secondary' : 'btn-success');
                    boton.disabled = false;
                    renderizarCalendario();
                })
                .catch(() => {
                    boton.disabled = false;
                    alert('Ocurrió un error al actualizar. Intenta de nuevo.');
                });
        }

        document.getElementById('btn-mes-anterior').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() - 1, 1);
            renderizarCalendario();
        });

        document.getElementById('btn-mes-siguiente').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 1);
            renderizarCalendario();
        });

        // Cierre explícito del modal
        document.querySelectorAll('#modal-dia [data-dismiss="modal"]').forEach(boton => {
            boton.addEventListener('click', () => {
                $('#modal-dia').modal('hide');
            });
        });

        mesActual = new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00');
        renderizarCalendario();
    </script>
@endsection
