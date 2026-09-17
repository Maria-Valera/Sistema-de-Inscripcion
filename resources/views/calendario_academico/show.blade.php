@extends('adminlte::page')

@section('title', 'Revisar calendario académico')

@section('css')
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
            position: relative;
        }

        .celda-vacia { background: #fafafa; }

        .celda-con-eventos { cursor: pointer; transition: background-color 0.15s ease-in-out; }
        .celda-con-eventos:hover { background-color: #f0f7ff; }

        /* Indicador de "hoy": borde de color + número resaltado */
        .celda-hoy {
            border: 2px solid #007bff;
        }
        .celda-hoy .numero-dia {
            background: #007bff;
            color: #fff;
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
        }

        .numero-dia { font-size: 0.8rem; color: #495057; margin-bottom: 2px; }

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

        /* ===== Modal de detalle del día ===== */
        #modal-dia-cuerpo .fila-evento { padding: 12px 0; }
        #modal-dia-cuerpo .fila-evento:not(:last-child) { border-bottom: 1px solid #e9ecef; }
        #modal-dia-cuerpo .fila-evento-texto { display: block; margin-bottom: 8px; word-break: break-word; font-size: 0.95rem; }
        #modal-dia-cuerpo .fila-evento-badges { margin-bottom: 10px; }
        #modal-dia-cuerpo .fila-evento-badges .badge { margin-right: 4px; margin-bottom: 4px; font-weight: 500; }
        #modal-dia-cuerpo .badge-light { background-color: #e9ecef; color: #495057; border: 1px solid #dee2e6; }
        #modal-dia-cuerpo .fila-evento-accion { display: flex; justify-content: flex-end; gap: 8px; }
        #modal-dia-cuerpo .fila-evento-accion .btn { min-width: 90px; }

        /* ===== Selector de colores ===== */
        .selector-colores {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(32px, 1fr));
            gap: 8px;
        }
        .selector-colores .swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            position: relative;
        }
        .selector-colores .swatch.seleccionado {
            border-color: #212529;
        }
        .selector-colores .swatch.seleccionado::after {
            content: "\2713";
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            text-shadow: 0 0 2px rgba(0,0,0,0.6);
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


                {{-- ===================== VISTA DE CALENDARIO (con soporte manual) ===================== --}}
<div class="tab-pane fade" id="tab-calendario" role="tabpanel">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" id="btn-mes-anterior" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-left"></i> Mes anterior
            </button>
            <h4 id="calendario-titulo" class="mb-0 text-capitalize"></h4>
            <div>
                <button type="button" id="btn-nuevo-evento" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-plus"></i> Nuevo evento
                </button>
                <button type="button" id="btn-mes-siguiente" class="btn btn-outline-secondary btn-sm">
                    Mes siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="calendario-cabecera-semana">
            <div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sá</div><div>Do</div>
        </div>
        <div id="calendario-grid" class="calendario-grid"></div>

        <div class="mt-3">
            <span class="calendario-leyenda-punto" style="background:#dc3545"></span> No laborable (ambos)
            <span class="calendario-leyenda-punto ml-3" style="background:#0047AB"></span> No laborable (docentes)
            <span class="calendario-leyenda-punto ml-3" style="background:#B57EDC"></span> No laborable (estudiantes)
            <span class="calendario-leyenda-punto ml-3" style="background:#117A8B"></span> Efeméride
            <span class="text-muted ml-3">— haz clic en un día para revisarlo o agregar un evento</span>
        </div>

    </div>
</div>
            </div>
        </div>
    </div>

    {{-- Modal de detalle del día: eventos del PDF (confirmar) + eventos manuales (editar/eliminar) --}}
<div class="modal fade" id="modal-dia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize" id="modal-dia-titulo"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal-dia-cuerpo"></div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-agregar-desde-modal">
                    <i class="fas fa-plus"></i> Agregar evento este día
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de formulario: crear / editar evento manual --}}
<div class="modal fade" id="modal-evento-form" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-evento-form-titulo">Nuevo evento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="evento-form-error"></div>

                <div class="form-group">
                    <label>Nombre del evento</label>
                    <input type="text" class="form-control" id="evento-nombre" maxlength="255">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Fecha de inicio</label>
                        <input type="date" class="form-control" id="evento-fecha-inicio">
                    </div>
                    <div class="form-group col-md-6" id="grupo-fecha-fin">
                        <label>Fecha de fin</label>
                        <input type="date" class="form-control" id="evento-fecha-fin">
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="evento-es-rango">
                        <label class="custom-control-label" for="evento-es-rango">Es un rango de varios días</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">¿Es efeméride?</label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-efemeride" id="evento-efemeride-si" value="1">
                        <label class="custom-control-label" for="evento-efemeride-si">Sí</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-efemeride" id="evento-efemeride-no" value="0" checked>
                        <label class="custom-control-label" for="evento-efemeride-no">No</label>
                    </div>
                    <small class="form-text text-muted">Ej: 24 de junio (Carabobo) es efeméride y no laborable. 17 de diciembre (muerte de Bolívar) es efeméride pero laborable.</small>
                </div>

                <div class="form-group">
                    <label class="d-block">Tipo de día</label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-tipo" id="evento-tipo-laborable" value="0" checked>
                        <label class="custom-control-label" for="evento-tipo-laborable">Laborable</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-tipo" id="evento-tipo-no-laborable" value="1">
                        <label class="custom-control-label" for="evento-tipo-no-laborable">No laborable</label>
                    </div>
                </div>

                <div class="form-group d-none" id="grupo-aplica-a">
                    <label class="d-block">Aplica a</label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-aplica-a" id="aplica-ambos" value="ambos" checked>
                        <label class="custom-control-label" for="aplica-ambos">Ambos</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-aplica-a" id="aplica-docentes" value="docentes">
                        <label class="custom-control-label" for="aplica-docentes">Solo docentes</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-aplica-a" id="aplica-estudiantes" value="estudiantes">
                        <label class="custom-control-label" for="aplica-estudiantes">Solo estudiantes</label>
                    </div>
                </div>

                <div class="form-group" id="grupo-color">
                    <label class="d-block">Color</label>
                    <div id="selector-colores" class="selector-colores"></div>
                    <input type="hidden" id="evento-color" value="gris">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-evento">Guardar</button>
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
        const HOY = (() => {
            // Se calcula en el navegador, no en el servidor, para evitar el
            // desfase cuando la zona horaria del servidor (a menudo UTC) no
            // coincide con la del usuario (ej. America/Caracas, UTC-4).
            const ahora = new Date();
            const anio = ahora.getFullYear();
            const mes = String(ahora.getMonth() + 1).padStart(2, '0');
            const dia = String(ahora.getDate()).padStart(2, '0');
            return `${anio}-${mes}-${dia}`;
        })();
        const URL_BASE = '{{ url('admin/calendario_academico/'.$calendario->id) }}';
        const CALENDARIO_CONFIRMADO = {{ $calendario->estaConfirmado() ? 'true' : 'false' }};

        // Paleta seleccionable, generada desde el enum ColorEvento (solo los 19, sin los fijos).
        const COLORES_SELECCIONABLES = [
            @foreach (\App\Enums\ColorEvento::seleccionables() as $color)
                { valor: '{{ $color->value }}', etiqueta: '{{ $color->label() }}', hex: '{{ $color->hex() }}' },
            @endforeach
        ];

        let mesActual;
        let modoFormulario = 'crear'; // 'crear' | 'editar'
        let idEventoEnEdicion = null;
        let colorSeleccionado = 'gris';

        function claveMes(fecha) { return fecha.getFullYear() * 12 + fecha.getMonth(); }
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
            if (fechaStr === HOY) div.classList.add('celda-hoy');

            const numero = document.createElement('div');
            numero.className = 'numero-dia';
            numero.textContent = numeroDia;
            div.appendChild(numero);

            eventos.forEach(evento => {
                const chip = document.createElement('div');
                chip.className = 'evento-chip';
                if (evento.colorHex) {
                    // Evento manual: usa su color propio, sin importar confirmado/categoría.
                    chip.style.backgroundColor = evento.colorHex;
                } else {
                    chip.classList.add(evento.confirmado
                        ? 'evento-confirmado'
                        : (evento.categoria === 'no_laborable' ? 'evento-no-laborable' : 'evento-dudoso'));
                }
                chip.textContent = evento.texto;
                chip.title = evento.texto;
                div.appendChild(chip);
            });

            div.classList.add('celda-con-eventos');
            div.addEventListener('click', () => abrirModalDia(fechaStr, eventos));

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

            for (let i = 0; i < diaSemanaInicio; i++) contenedor.appendChild(crearCeldaVacia());

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

        // ===== Modal de detalle del día =====

        let fechaDelModalActual = null;

        function abrirModalDia(fechaStr, eventos) {
            fechaDelModalActual = fechaStr;
            const cuerpo = document.getElementById('modal-dia-cuerpo');
            cuerpo.innerHTML = '';

            const fechaFormateada = new Date(fechaStr + 'T00:00:00')
                .toLocaleDateString('es-VE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('modal-dia-titulo').textContent = fechaFormateada;

            if (eventos.length === 0) {
                cuerpo.innerHTML = '<p class="text-muted mb-0">No hay eventos este día.</p>';
            }

            eventos.forEach(evento => {
                const fila = document.createElement('div');
                fila.className = 'fila-evento';

                const info = document.createElement('div');
                info.innerHTML = `
                    <span class="fila-evento-texto">${evento.texto}</span>
                    <div class="fila-evento-badges">
                        <span class="badge ${evento.categoria === 'no_laborable' ? 'badge-danger' : 'badge-secondary'}">${evento.categoriaLabel}</span>
                        ${evento.esManual ? '<span class="badge badge-light">Manual</span>' : `<span class="badge badge-light">${evento.confianzaLabel}</span>`}
                        ${evento.esEfemeride ? '<span class="badge badge-light">Efeméride</span>' : ''}
                    </div>
                `;

                const accion = document.createElement('div');
                accion.className = 'fila-evento-accion';

                if (evento.esManual) {
                    const btnEditar = document.createElement('button');
                    btnEditar.type = 'button';
                    btnEditar.className = 'btn btn-sm btn-outline-primary';
                    btnEditar.textContent = 'Editar';
                    btnEditar.addEventListener('click', () => abrirModalEditarEvento(evento));

                    const btnEliminar = document.createElement('button');
                    btnEliminar.type = 'button';
                    btnEliminar.className = 'btn btn-sm btn-outline-danger';
                    btnEliminar.textContent = 'Eliminar';
                    btnEliminar.addEventListener('click', () => eliminarEvento(evento.id));

                    accion.appendChild(btnEditar);
                    accion.appendChild(btnEliminar);
                } else {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn btn-sm ' + (evento.confirmado ? 'btn-outline-secondary' : 'btn-success');
                    boton.textContent = evento.confirmado ? 'Quitar confirmación' : 'Confirmar';
                    boton.disabled = CALENDARIO_CONFIRMADO;
                    boton.addEventListener('click', () => alternarConfirmacion(evento, boton));
                    accion.appendChild(boton);
                }

                fila.appendChild(info);
                fila.appendChild(accion);
                cuerpo.appendChild(fila);
            });

            $('#modal-dia').modal('show');
        }

        function alternarConfirmacion(evento, boton) {
            const nuevoValor = !evento.confirmado;
            boton.disabled = true;

            fetch(`${URL_BASE}/dias/${evento.id}/confirmar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ confirmado: nuevoValor }),
            })
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(datos => {
                    evento.confirmado = datos.confirmado;
                    boton.textContent = datos.confirmado ? 'Quitar confirmación' : 'Confirmar';
                    boton.className = 'btn btn-sm ' + (datos.confirmado ? 'btn-outline-secondary' : 'btn-success');
                    boton.disabled = false;
                    renderizarCalendario();
                })
                .catch(() => { boton.disabled = false; alert('Ocurrió un error al actualizar. Intenta de nuevo.'); });
        }

        // ===== Formulario de evento manual (crear / editar) =====

        function renderizarSelectorColores() {
            const contenedor = document.getElementById('selector-colores');
            contenedor.innerHTML = '';

            COLORES_SELECCIONABLES.forEach(color => {
                const swatch = document.createElement('div');
                swatch.className = 'swatch' + (color.valor === colorSeleccionado ? ' seleccionado' : '');
                swatch.style.backgroundColor = color.hex;
                swatch.title = color.etiqueta;
                swatch.addEventListener('click', () => {
                    colorSeleccionado = color.valor;
                    document.getElementById('evento-color').value = color.valor;
                    renderizarSelectorColores();
                });
                contenedor.appendChild(swatch);
            });
        }

        function actualizarVisibilidadFormulario() {
            const esNoLaborable = document.getElementById('evento-tipo-no-laborable').checked;
            const esEfemeride = document.getElementById('evento-efemeride-si').checked;

            document.getElementById('grupo-aplica-a').classList.toggle('d-none', !esNoLaborable);

            // El color solo tiene sentido si es laborable Y no es efeméride
            // (no laborable siempre es rojo/cobalto/lavanda; efeméride siempre pavo real).
            document.getElementById('grupo-color').classList.toggle('d-none', esNoLaborable || esEfemeride);
        }

        document.querySelectorAll('input[name="evento-tipo"], input[name="evento-efemeride"]').forEach(input => {
            input.addEventListener('change', actualizarVisibilidadFormulario);
        });

        document.getElementById('evento-es-rango').addEventListener('change', function () {
            document.getElementById('grupo-fecha-fin').classList.toggle('d-none', !this.checked);
        });

        function limpiarFormularioEvento() {
            document.getElementById('evento-form-error').classList.add('d-none');
            document.getElementById('evento-nombre').value = '';
            document.getElementById('evento-fecha-inicio').value = '';
            document.getElementById('evento-fecha-fin').value = '';
            document.getElementById('evento-es-rango').checked = false;
            document.getElementById('grupo-fecha-fin').classList.remove('d-none');
            document.getElementById('evento-efemeride-no').checked = true;
            document.getElementById('evento-tipo-laborable').checked = true;
            document.getElementById('aplica-ambos').checked = true;
            colorSeleccionado = 'gris';
            document.getElementById('evento-color').value = 'gris';
            renderizarSelectorColores();
            actualizarVisibilidadFormulario();

            // El rango solo tiene sentido al crear, no al editar un día puntual.
            document.getElementById('evento-es-rango').closest('.form-group').style.display =
                modoFormulario === 'crear' ? '' : 'none';
        }

        function abrirModalNuevoEvento(fechaPreseleccionada) {
            modoFormulario = 'crear';
            idEventoEnEdicion = null;
            limpiarFormularioEvento();
            document.getElementById('modal-evento-form-titulo').textContent = 'Nuevo evento';
            document.getElementById('evento-fecha-inicio').value = fechaPreseleccionada || HOY;
            $('#modal-dia').modal('hide');
            $('#modal-evento-form').modal('show');
        }

        function abrirModalEditarEvento(evento) {
            modoFormulario = 'editar';
            idEventoEnEdicion = evento.id;
            limpiarFormularioEvento();
            document.getElementById('modal-evento-form-titulo').textContent = 'Editar evento';
            document.getElementById('evento-nombre').value = evento.texto;
            document.getElementById('evento-fecha-inicio').value = evento.fecha;
            document.getElementById('grupo-fecha-fin').classList.add('d-none');
            document.getElementById('evento-efemeride-' + (evento.esEfemeride ? 'si' : 'no')).checked = true;
            document.getElementById('evento-tipo-' + (evento.categoria === 'no_laborable' ? 'no-laborable' : 'laborable')).checked = true;
            actualizarVisibilidadFormulario();
            $('#modal-dia').modal('hide');
            $('#modal-evento-form').modal('show');
        }

        document.getElementById('btn-nuevo-evento').addEventListener('click', () => abrirModalNuevoEvento(null));
        document.getElementById('btn-agregar-desde-modal').addEventListener('click', () => abrirModalNuevoEvento(fechaDelModalActual));

        document.getElementById('btn-guardar-evento').addEventListener('click', function () {
            const boton = this;
            const errorBox = document.getElementById('evento-form-error');
            errorBox.classList.add('d-none');

            const esRango = document.getElementById('evento-es-rango').checked && modoFormulario === 'crear';
            const payload = {
                nombre: document.getElementById('evento-nombre').value,
                fecha_inicio: document.getElementById('evento-fecha-inicio').value,
                fecha_fin: esRango ? document.getElementById('evento-fecha-fin').value : null,
                es_efemeride: document.getElementById('evento-efemeride-si').checked ? 1 : 0,
                es_no_laborable: document.getElementById('evento-tipo-no-laborable').checked ? 1 : 0,
                aplica_a: document.querySelector('input[name="evento-aplica-a"]:checked')?.value ?? null,
                color: document.getElementById('evento-color').value,
            };

            const esEdicion = modoFormulario === 'editar';
            const url = esEdicion ? `${URL_BASE}/eventos/${idEventoEnEdicion}` : `${URL_BASE}/eventos`;

            boton.disabled = true;

            fetch(url, {
                method: esEdicion ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            })
                .then(async r => {
                    const datos = await r.json();
                    if (!r.ok) throw datos;
                    return datos;
                })
                .then(datos => {
                    if (esEdicion) {
                        const indice = DIAS.findIndex(d => d.id === idEventoEnEdicion);
                        if (indice !== -1) DIAS[indice] = datos;
                    } else {
                        datos.creados.forEach(d => DIAS.push(d));
                    }
                    $('#modal-evento-form').modal('hide');
                    renderizarCalendario();
                    boton.disabled = false;
                })
                .catch(errores => {
                    boton.disabled = false;
                    const mensajes = errores.errors
                        ? Object.values(errores.errors).flat().join(' ')
                        : (errores.message || 'Ocurrió un error al guardar.');
                    errorBox.textContent = mensajes;
                    errorBox.classList.remove('d-none');
                });
        });

        function eliminarEvento(id) {
            if (!confirm('¿Eliminar este evento? Esta acción no se puede deshacer.')) return;

            fetch(`${URL_BASE}/eventos/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            })
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(() => {
                    const indice = DIAS.findIndex(d => d.id === id);
                    if (indice !== -1) DIAS.splice(indice, 1);
                    $('#modal-dia').modal('hide');
                    renderizarCalendario();
                })
                .catch(() => alert('No se pudo eliminar el evento.'));
        }

        // ===== Navegación de mes y cierre de modales =====

        document.getElementById('btn-mes-anterior').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() - 1, 1);
            renderizarCalendario();
        });

        document.getElementById('btn-mes-siguiente').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 1);
            renderizarCalendario();
        });

        document.querySelectorAll('.modal [data-dismiss="modal"]').forEach(boton => {
            boton.addEventListener('click', function () {
                $(this).closest('.modal').modal('hide');
            });
        });

        renderizarSelectorColores();
        mesActual = new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00');
        renderizarCalendario();
    </script>
@stop
