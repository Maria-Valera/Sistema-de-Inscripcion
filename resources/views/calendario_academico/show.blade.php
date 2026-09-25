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

        /* Indicador de "hoy" */
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

        /*  Vista semanal: misma cuadrícula, pero celdas más altas y
           texto completo  */
        .semana-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }
        .semana-grid .celda-dia {
            min-height: 220px;
        }
        .semana-grid .evento-chip {
            white-space: normal;
            font-size: 0.75rem;
            padding: 3px 6px;
        }

        /*  Modal de detalle del día  */
        #modal-dia-cuerpo .fila-evento { padding: 12px 0; }
        #modal-dia-cuerpo .fila-evento:not(:last-child) { border-bottom: 1px solid #e9ecef; }
        #modal-dia-cuerpo .fila-evento-texto { display: block; margin-bottom: 8px; word-break: break-word; font-size: 0.95rem; }
        #modal-dia-cuerpo .fila-evento-badges { margin-bottom: 10px; }
        #modal-dia-cuerpo .fila-evento-badges .badge { margin-right: 4px; margin-bottom: 4px; font-weight: 500; }
        #modal-dia-cuerpo .badge-light { background-color: #e9ecef; color: #495057; border: 1px solid #dee2e6; }
        #modal-dia-cuerpo .fila-evento-accion { display: flex; justify-content: flex-end; gap: 8px; }
        #modal-dia-cuerpo .fila-evento-accion .btn { min-width: 90px; }

        /*  Selector de colores  */
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

        .error-message {
    display: none;
    align-items: center;
    gap: 6px;
}

    </style>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
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
                <li class="nav-item">
                    <a class="nav-link" id="tab-semana-link" data-toggle="tab" href="#tab-semana" role="tab">
                        <i class="fas fa-calendar-week mr-1"></i> Semana
                    </a>
                </li>

            </ul>

            <div class="tab-content">
                {{--  VISTA DE LISTA  --}}
                <div class="tab-pane fade show active" id="tab-lista" role="tabpanel">
                    <form action="{{ route('admin.calendario_academico.confirmar', $calendario) }}" method="POST" id="form-confirmar-calendario">
    @csrf
    <div id="lista-dias-contenedor">
        {{-- aqui no se rellena nada por que el js no rellena --}}
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


                {{--  VISTA DE CALENDARIO   --}}
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


{{--  VISTA SEMANAL  --}}
<div class="tab-pane fade" id="tab-semana" role="tabpanel">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button type="button" id="btn-semana-anterior" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chevron-left"></i> Semana anterior
            </button>
            <h4 id="semana-titulo" class="mb-0 text-capitalize"></h4>
            <button type="button" id="btn-semana-siguiente" class="btn btn-outline-secondary btn-sm">
                Semana siguiente <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="calendario-cabecera-semana">
            <div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sá</div><div>Do</div>
        </div>
        <div id="semana-grid" class="semana-grid"></div>

    </div>
</div>




            </div>
        </div>
    </div>



{{--  Modal: detalle del día  --}}
<div class="modal fade" id="modal-dia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-modern">

            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h5 class="modal-title-edit text-capitalize" id="modal-dia-titulo"></h5>
                <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit" id="modal-dia-cuerpo"></div>

            <div class="modal-footer-edit">
                <div class="footer-buttons w-100 d-flex justify-content-between">
                    <button type="button" class="btn-modal-cancel" id="btn-agregar-desde-modal">
                        <i class="fas fa-plus"></i> Agregar evento este día
                    </button>
                    <button type="button" class="btn-modal-cancel" data-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
</div>

{{--  Modal: crear / editar evento manual  --}}
<div class="modal fade" id="modal-evento-form" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-modern">


            <div class="modal-header-create" id="modal-evento-form-header">
                <div class="modal-icon-create" id="modal-evento-form-icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h5 class="modal-title-create" id="modal-evento-form-titulo">Nuevo evento</h5>
                <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <div class="alert-modern alert-danger d-none" id="evento-form-error">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content"><p class="mb-0"></p></div>
                </div>

                <div class="form-group-modern">
                    <label for="evento-nombre" class="form-label-modern">
                        <i class="fas fa-heading"></i> Nombre del evento
                    </label>
                    <input type="text" class="form-control-modern" id="evento-nombre" maxlength="255"
                           placeholder="Ej: Semana Santa (Lu-Do)" autocomplete="off">

                    <div class="error-message" id="error-evento-nombre-vacio">
                        <i class="fas fa-exclamation-circle"></i>
                        El nombre del evento es obligatorio.
                    </div>
                    <div class="error-message" id="error-evento-nombre-formato">
                        <i class="fas fa-exclamation-circle"></i>
                        Solo se permiten letras, números, espacios, paréntesis, comas y barras (/).
                    </div>
                    <div class="error-message" id="ok-evento-nombre" style="background: var(--soft-success-bg); color: var(--soft-success-text); display:none;">
                        <i class="fas fa-check-circle"></i>
                        Nombre válido.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <label for="evento-fecha-inicio" class="form-label-modern">
                                <i class="fas fa-calendar"></i> Fecha de inicio
                            </label>
                            <input type="date" class="form-control-modern" id="evento-fecha-inicio">

                            <div class="error-message" id="error-evento-fecha-inicio-vacia">
                                <i class="fas fa-exclamation-circle"></i>
                                Debes indicar la fecha de inicio.
                            </div>
                            <div class="error-message" id="error-evento-fecha-inicio-rango">
                                <i class="fas fa-exclamation-circle"></i>
                                Debe estar dentro del año escolar.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-modern" id="grupo-fecha-fin">
                            <label for="evento-fecha-fin" class="form-label-modern">
                                <i class="fas fa-calendar-check"></i> Fecha de fin
                            </label>
                            <input type="date" class="form-control-modern" id="evento-fecha-fin">

                            <div class="error-message" id="error-evento-fecha-fin-vacia">
                                <i class="fas fa-exclamation-circle"></i>
                                Debes indicar la fecha de fin.
                            </div>
                            <div class="error-message" id="error-evento-fecha-fin-rango">
                                <i class="fas fa-exclamation-circle"></i>
                                Debe ser igual o posterior a la fecha de inicio, y dentro del año escolar.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group-modern">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="evento-es-rango">
                        <label class="custom-control-label" for="evento-es-rango">Es un rango de varios días</label>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern d-block">
                        <i class="fas fa-landmark"></i> ¿Es efeméride?
                    </label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-efemeride" id="evento-efemeride-si" value="1">
                        <label class="custom-control-label" for="evento-efemeride-si">Sí</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-efemeride" id="evento-efemeride-no" value="0" checked>
                        <label class="custom-control-label" for="evento-efemeride-no">No</label>
                    </div>
                    <small class="form-text text-muted">
                        Ej: 24 de junio (Carabobo) es efeméride y no laborable. 17 de diciembre
                        (muerte de Bolívar) es efeméride pero laborable.
                    </small>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern d-block">
                        <i class="fas fa-briefcase"></i> Tipo de día
                    </label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-tipo" id="evento-tipo-laborable" value="0" checked>
                        <label class="custom-control-label" for="evento-tipo-laborable">Laborable</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" class="custom-control-input" name="evento-tipo" id="evento-tipo-no-laborable" value="1">
                        <label class="custom-control-label" for="evento-tipo-no-laborable">No laborable</label>
                    </div>
                </div>

                <div class="form-group-modern d-none" id="grupo-aplica-a">
                    <label class="form-label-modern d-block">
                        <i class="fas fa-users"></i> Aplica a
                    </label>
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

                <div class="form-group-modern" id="grupo-color">
                    <label class="form-label-modern d-block">
                        <i class="fas fa-palette"></i> Color
                    </label>
                    <div id="selector-colores" class="selector-colores"></div>
                    <input type="hidden" id="evento-color" value="">
                </div>
            </div>

            <div class="modal-footer-create">
                <div class="footer-buttons">
                    <button type="button" class="btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-modal-create" id="btn-guardar-evento">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{--  Modal: confirmar eliminación de evento manual  --}}
<div class="modal fade" id="modal-eliminar-evento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-modern">

            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title-delete">Confirmar eliminación</h5>
                <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-delete">
                <p>¿Deseas eliminar el evento <strong id="eliminar-evento-nombre"></strong>?</p>
                <p class="delete-warning">Esta acción no se puede deshacer.</p>
            </div>

            <div class="modal-footer-delete">
                <div class="footer-buttons">
                    <button type="button" class="btn-modal-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-modal-delete" id="btn-confirmar-eliminar-evento">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

{{--  JS   --}}

@section('js')
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const DIAS = {!! $diasParaCalendarioJson !!};
        const INICIO_ANIO_ESCOLAR = '{{ \Carbon\Carbon::parse($inicioAnioEscolar)->toDateString() }}';
        const CIERRE_ANIO_ESCOLAR = '{{ \Carbon\Carbon::parse($cierreAnioEscolar)->toDateString() }}';
        const HOY = (() => {
            const ahora = new Date();
            const anio = ahora.getFullYear();
            const mes = String(ahora.getMonth() + 1).padStart(2, '0');
            const dia = String(ahora.getDate()).padStart(2, '0');
            return `${anio}-${mes}-${dia}`;
        })();
        const URL_BASE = '{{ url('admin/calendario_academico/'.$calendario->id) }}';
        const CALENDARIO_CONFIRMADO = {{ $calendario->estaConfirmado() ? 'true' : 'false' }};

        // Paleta seleccionable, generada desde el enum ColorEvento (solo los 19, sin los colores fijos).
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

        //  Vista semanal

        let semanaActual; // Date: el lunes de la semana que se muestra

        function obtenerLunesDeSemana(fecha) {
            const copia = new Date(fecha);
            const diaSemana = copia.getDay(); // 0=domingo
            const diferencia = diaSemana === 0 ? -6 : 1 - diaSemana; // retrocede hasta el lunes
            copia.setDate(copia.getDate() + diferencia);
            return copia;
        }

        // aqui lo que se es Traducir los valores del enum a clases de badge
function badgeCategoria(categoria) {
    return categoria === 'no_laborable' ? 'badge-danger' : 'badge-secondary';
}
function badgeConfianza(confianza) {
    if (confianza === 'alta')    return 'badge-success';
    if (confianza === 'dudosa')  return 'badge-warning';
    return 'badge-info';


}


function labelCategoria(v) {

    if (v === 'no_laborable') return 'No laborable';
    if (v === 'laborable')    return 'Laborable';
    return v ?? '';
}

function labelConfianza(v) {
    if (v === 'alta')   return 'Alta';
    if (v === 'dudosa') return 'Dudosa';
    if (v === 'manual') return 'Manual';
    return v ?? '';
}

function renderizarLista() {
    const cont = document.getElementById('lista-dias-contenedor');
    if (!cont) return;

    if (DIAS.length === 0) {
        cont.innerHTML = '<p class="text-muted">No se detectaron candidatos en este PDF.</p>';
        return;
    }

    // aqui agrupamos los eventos por el mes (mes_pagina)
    const grupos = {};
    DIAS.forEach(d => {
        let mes = d.mesPagina;
        if (!mes && d.fecha) mes = parseInt(d.fecha.slice(5, 7), 10);
        if (!mes) mes = 0;
        if (!grupos[mes]) grupos[mes] = [];
        grupos[mes].push(d);
    });

    const nombresMes = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    cont.innerHTML = Object.keys(grupos)
        .sort((a, b) => a - b)
        .map(mes => {
            const dias = grupos[mes].slice().sort((a, b) =>
                (a.fecha || '').localeCompare(b.fecha || '')
            );
            const titulo = nombresMes[mes] || 'Sin mes';

            const filas = dias.map(d => `
                <tr data-dia-id="${d.id}">
                    <td>
                        <input type="checkbox"
                               name="ids_aprobados[]"
                               value="${d.id}"
                               class="chk-aprobado"
                               ${d.confirmado ? 'checked' : ''}>
                    </td>
                    <td>${d.fecha ?? ''}</td>
                    <td>${d.texto ?? ''}</td>
                    <td>
    <span class="badge ${badgeCategoria(d.categoria)}">
        ${labelCategoria(d.categoria)}
    </span>
</td>
<td>
    <span class="badge ${badgeConfianza(d.confianza)}">
        ${labelConfianza(d.confianza)}
    </span>
</td>
                    <td>
                        ${d.aplica_personal    ? '<span class="badge badge-info">Personal</span>'    : ''}
                        ${d.aplica_estudiantes ? '<span class="badge badge-info">Estudiantes</span>' : ''}
                    </td>
                </tr>
            `).join('');

            return `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>${titulo}</strong>
                        <span class="text-muted ms-2">(${dias.length})</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width:32px"></th>
                                    <th>Fecha</th>
                                    <th>Texto</th>
                                    <th>Categoría</th>
                                    <th>Confianza</th>
                                    <th>Aplica a</th>
                                </tr>
                            </thead>
                            <tbody>${filas}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }).join('');
}

        function renderizarSemana() {
            const contenedor = document.getElementById('semana-grid');
            contenedor.innerHTML = '';

            const domingo = new Date(semanaActual);
            domingo.setDate(domingo.getDate() + 6);

            const formatoCorto = { day: 'numeric', month: 'short' };
            const inicioTexto = semanaActual.toLocaleDateString('es-VE', formatoCorto);
            const finTexto = domingo.toLocaleDateString('es-VE', { day: 'numeric', month: 'short', year: 'numeric' });
            document.getElementById('semana-titulo').textContent = `${inicioTexto} — ${finTexto}`;

            for (let i = 0; i < 7; i++) {
                const fecha = new Date(semanaActual);
                fecha.setDate(fecha.getDate() + i);
                const fechaStr = formatearFecha(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
                const eventosDelDia = DIAS.filter(d => d.fecha === fechaStr);
                contenedor.appendChild(crearCeldaDia(fecha.getDate(), fechaStr, eventosDelDia));
            }

            const lunesInicioAnio = obtenerLunesDeSemana(new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00'));
            const lunesCierreAnio = obtenerLunesDeSemana(new Date(CIERRE_ANIO_ESCOLAR + 'T00:00:00'));

            document.getElementById('btn-semana-anterior').disabled = semanaActual <= lunesInicioAnio;
            document.getElementById('btn-semana-siguiente').disabled = semanaActual >= lunesCierreAnio;
        }

        document.getElementById('btn-semana-anterior').addEventListener('click', () => {
            semanaActual.setDate(semanaActual.getDate() - 7);
            renderizarSemana();
        });

        document.getElementById('btn-semana-siguiente').addEventListener('click', () => {
            semanaActual.setDate(semanaActual.getDate() + 7);
            renderizarSemana();
        });



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
                    const acciones = document.createElement('div');
                    acciones.className = 'action-buttons';

                    const btnEditar = document.createElement('button');
                    btnEditar.type = 'button';
                    btnEditar.className = 'action-btn btn-edit';
                    btnEditar.title = 'Editar';
                    btnEditar.innerHTML = '<i class="fas fa-pen"></i>';
                    btnEditar.addEventListener('click', () => abrirModalEditarEvento(evento));

                    const btnEliminar = document.createElement('button');
                    btnEliminar.type = 'button';
                    btnEliminar.className = 'action-btn btn-delete';
                    btnEliminar.title = 'Eliminar';
                    btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    btnEliminar.addEventListener('click', () => abrirModalEliminarEvento(evento));

                    acciones.appendChild(btnEditar);
                    acciones.appendChild(btnEliminar);
                    accion.appendChild(acciones);
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
                    renderizarSemana();
                    renderizarLista();
                })
                .catch(() => { boton.disabled = false; alert('Ocurrió un error al actualizar. Intenta de nuevo.'); });
        }

        //  Formulario de evento manual (crear  y editar)

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
            limpiarValidacionEvento();
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

            // El rango solo tiene sentido al crear, no al editar un día puntual, asi que no se coloca.
            document.getElementById('evento-es-rango').closest('.form-group-modern').style.display =
            document.getElementById('evento-es-rango').dispatchEvent(new Event('change'));
                // modoFormulario === 'crear' ? '' : 'none';
        }

        // validaciones en tiempo real

        const PATRON_NOMBRE = /^[\p{L}\p{N}\s(),/]+$/u;

        function mostrarError(idError, mostrar) {
            document.getElementById(idError).style.display = mostrar ? 'flex' : 'none';
        }

        function validarNombre() {
            const valor = document.getElementById('evento-nombre').value.trim();
            const vacio = valor.length === 0;
            const formatoInvalido = !vacio && !PATRON_NOMBRE.test(valor);

            mostrarError('error-evento-nombre-vacio', vacio);
            mostrarError('error-evento-nombre-formato', formatoInvalido);
            mostrarError('ok-evento-nombre', !vacio && !formatoInvalido);

            return !vacio && !formatoInvalido;
        }

        function validarFechaInicio() {
            const valor = document.getElementById('evento-fecha-inicio').value;
            const vacia = valor === '';
            const fueraDeRango = !vacia && (valor < INICIO_ANIO_ESCOLAR || valor > CIERRE_ANIO_ESCOLAR);

            mostrarError('error-evento-fecha-inicio-vacia', vacia);
            mostrarError('error-evento-fecha-inicio-rango', fueraDeRango);

            // Si la fecha de inicio cambia, la fecha de fin puede haber
            // quedado inválida respecto a la nueva fecha de inicio.
            if (document.getElementById('evento-es-rango').checked) validarFechaFin();

            return !vacia && !fueraDeRango;
        }

        function validarFechaFin() {
            const esRango = document.getElementById('evento-es-rango').checked;
            if (!esRango || modoFormulario === 'editar') {
                mostrarError('error-evento-fecha-fin-vacia', false);
                mostrarError('error-evento-fecha-fin-rango', false);
                return true;
            }

            const inicio = document.getElementById('evento-fecha-inicio').value;
            const fin = document.getElementById('evento-fecha-fin').value;
            const vacia = fin === '';
            const invalida = !vacia && (fin < inicio || fin > CIERRE_ANIO_ESCOLAR);

            mostrarError('error-evento-fecha-fin-vacia', vacia);
            mostrarError('error-evento-fecha-fin-rango', invalida);

            return !vacia && !invalida;
        }

        function validarFormularioEvento() {
            const nombreOk = validarNombre();
            const inicioOk = validarFechaInicio();
            const finOk = validarFechaFin();
            const esValido = nombreOk && inicioOk && finOk;

            document.getElementById('btn-guardar-evento').disabled = !esValido;
            return esValido;
        }

        document.getElementById('evento-nombre').addEventListener('input', validarFormularioEvento);
        document.getElementById('evento-fecha-inicio').addEventListener('input', validarFormularioEvento);
        document.getElementById('evento-fecha-fin').addEventListener('input', validarFormularioEvento);
        document.getElementById('evento-es-rango').addEventListener('change', validarFormularioEvento);



        function limpiarValidacionEvento() {
    [
        'error-evento-nombre-vacio',
        'error-evento-nombre-formato',
        'ok-evento-nombre',
        'error-evento-fecha-inicio-vacia',
        'error-evento-fecha-inicio-rango',
        'error-evento-fecha-fin-vacia',
        'error-evento-fecha-fin-rango',
    ].forEach(id => mostrarError(id, false));

    document.getElementById('btn-guardar-evento').disabled = false;
}

        function aplicarEstiloEncabezadoFormulario(modo) {
            const header = document.getElementById('modal-evento-form-header');
            const icono = document.getElementById('modal-evento-form-icon');

            header.classList.remove('modal-header-create', 'modal-header-edit');
            header.classList.add(modo === 'crear' ? 'modal-header-create' : 'modal-header-edit');

            icono.classList.remove('modal-icon-create', 'modal-icon-edit');
            icono.classList.add(modo === 'crear' ? 'modal-icon-create' : 'modal-icon-edit');
            icono.innerHTML = modo === 'crear' ? '<i class="fas fa-calendar-plus"></i>' : '<i class="fas fa-pen"></i>';
        }

        function abrirModalNuevoEvento(fechaPreseleccionada) {
            modoFormulario = 'crear';
            idEventoEnEdicion = null;
            limpiarFormularioEvento();
            aplicarEstiloEncabezadoFormulario('crear');
            document.getElementById('modal-evento-form-titulo').textContent = 'Nuevo evento';
            document.getElementById('evento-fecha-inicio').value = fechaPreseleccionada || HOY;
            $('#modal-dia').modal('hide');
            $('#modal-evento-form').modal('show');
            validarFormularioEvento();
        }



        function abrirModalEditarEvento(evento) {
    modoFormulario = 'editar';
    idEventoEnEdicion = evento.id;
    limpiarFormularioEvento();
    aplicarEstiloEncabezadoFormulario('editar');

    document.getElementById('modal-evento-form-titulo').textContent = 'Editar evento';
    document.getElementById('evento-nombre').value       = evento.texto;
    document.getElementById('evento-fecha-inicio').value = evento.fecha;
    document.getElementById('grupo-fecha-fin').classList.add('d-none');

    document.getElementById('evento-efemeride-' + (evento.esEfemeride ? 'si' : 'no')).checked = true;
    document.getElementById('evento-tipo-' +
        (evento.categoria === 'no_laborable' ? 'no-laborable' : 'laborable')
    ).checked = true;

    //  Restaurar "aplica a"
    if (evento.categoria === 'no_laborable') {
        let aplica = 'ambos';
        if (evento.aplica_personal && !evento.aplica_estudiantes)      aplica = 'docentes';
        else if (!evento.aplica_personal && evento.aplica_estudiantes) aplica = 'estudiantes';
        document.getElementById('aplica-' + aplica).checked = true;
    }

    //  Restaurar color
    // Solo tiene sentido marcar el swatch si el color es seleccionable

    const colorEvento = evento.colorValue ?? null;
    const esSeleccionable = colorEvento
        && COLORES_SELECCIONABLES.some(c => c.valor === colorEvento);

    colorSeleccionado = esSeleccionable ? colorEvento : (colorEvento || 'gris');
    document.getElementById('evento-color').value = colorSeleccionado;
    renderizarSelectorColores();

    actualizarVisibilidadFormulario();

    $('#modal-dia').modal('hide');
    validarFormularioEvento();
    $('#modal-evento-form').modal('show');


}

        document.getElementById('btn-nuevo-evento').addEventListener('click', () => abrirModalNuevoEvento(null));
        document.getElementById('btn-agregar-desde-modal').addEventListener('click', () => abrirModalNuevoEvento(fechaDelModalActual));

        document.getElementById('btn-guardar-evento').addEventListener('click', function () {
            if (!validarFormularioEvento()) return;

            const boton = this;
            const errorBox = document.getElementById('evento-form-error');
            errorBox.classList.add('d-none');

            const esRango = document.getElementById('evento-es-rango').checked && modoFormulario === 'crear';

            // el color solo aplica a días laborables que NO son efeméride
            const esNoLaborable = document.getElementById('evento-tipo-no-laborable').checked;
            const esEfemeride  = document.getElementById('evento-efemeride-si').checked;
            const colorAplica  = !esNoLaborable && !esEfemeride;



            const payload = {
                    nombre:          document.getElementById('evento-nombre').value,
                    fecha_inicio:    document.getElementById('evento-fecha-inicio').value,
                    fecha_fin:       esRango ? document.getElementById('evento-fecha-fin').value : null,
                    es_efemeride:    esEfemeride ? 1 : 0,
                    es_no_laborable: esNoLaborable ? 1 : 0,
                    aplica_a:        document.querySelector('input[name="evento-aplica-a"]:checked')?.value ?? null,
                    // se envia null cuando no aplica entonces el backend asigna el color fijo correcto
                    color:           colorAplica
                                        ? (document.getElementById('evento-color').value || null)
                                        : null,
            };

            const esEdicion = modoFormulario === 'editar'; //
            const url = esEdicion
            ? `${URL_BASE}/eventos/${idEventoEnEdicion}`
            : `${URL_BASE}/eventos`;

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
                    renderizarSemana();
                    renderizarLista();
                    boton.disabled = false;
                })
                .catch(errores => {
                    boton.disabled = false;
                    const mensajes = errores.errors
                        ? Object.values(errores.errors).flat().join(' ')
                        : (errores.message || 'Ocurrió un error al guardar.');
                    errorBox.querySelector('p').textContent = mensajes;
                    errorBox.classList.remove('d-none');
                });
        });

        let idEventoAEliminar = null;

        function abrirModalEliminarEvento(evento) {
            idEventoAEliminar = evento.id;
            document.getElementById('eliminar-evento-nombre').textContent = evento.texto;
            $('#modal-dia').modal('hide');
            $('#modal-eliminar-evento').modal('show');
        }

        document.getElementById('btn-confirmar-eliminar-evento').addEventListener('click', function () {
            const boton = this;
            boton.disabled = true;

            fetch(`${URL_BASE}/eventos/${idEventoAEliminar}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            })
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(() => {
                    const indice = DIAS.findIndex(d => d.id === idEventoAEliminar);
                    if (indice !== -1) DIAS.splice(indice, 1);
                    boton.disabled = false;
                    $('#modal-eliminar-evento').modal('hide');
                    renderizarCalendario();
                    renderizarSemana();
                    renderizarLista();
                })
                .catch(() => {
                    boton.disabled = false;
                    alert('No se pudo eliminar el evento.');
                });
        });

        //  Navegación de mes y cierre de las  modales

        document.getElementById('btn-mes-anterior').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() - 1, 1);
            renderizarCalendario();
            renderizarSemana();
        });

        document.getElementById('btn-mes-siguiente').addEventListener('click', () => {
            mesActual = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 1);
            renderizarCalendario();
            renderizarSemana();
        });

        document.querySelectorAll('.modal [data-dismiss="modal"]').forEach(boton => {
            boton.addEventListener('click', function () {
                $(this).closest('.modal').modal('hide');
            });
        });

        renderizarSelectorColores();
        mesActual = new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00');
        renderizarCalendario();
        renderizarLista();
        const hoyComoFecha = new Date(HOY + 'T00:00:00');
        const inicioAnioComoFecha = new Date(INICIO_ANIO_ESCOLAR + 'T00:00:00');
        const cierreAnioComoFecha = new Date(CIERRE_ANIO_ESCOLAR + 'T00:00:00');
        const hoyEstaDentroDelAnio = hoyComoFecha >= inicioAnioComoFecha && hoyComoFecha <= cierreAnioComoFecha;
        semanaActual = obtenerLunesDeSemana(hoyEstaDentroDelAnio ? hoyComoFecha : inicioAnioComoFecha);
        renderizarSemana();



    </script>
@stop
