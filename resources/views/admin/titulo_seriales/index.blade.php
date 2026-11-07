@extends('adminlte::page')

@section('title', 'Seriales de título')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .mensaje-error { display: block; color: #dc3545; font-size: 0.8rem; margin-top: 4px; }
        .form-control-modern.campo-invalido { border-color: #dc3545; }
        .badge-estado { padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .badge-disponible { background: #d1fae5; color: #065f46; }
        .badge-asignado { background: #dbeafe; color: #1e40af; }
        .badge-anulado { background: #fee2e2; color: #991b1b; }
        .badge-devuelto { background: #e5e7eb; color: #374151; }
        .tarjetas-estado { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .tarjeta-estado { display: block; background: #fff; border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08); border: 2px solid transparent; color: inherit; text-decoration: none; }
        .tarjeta-estado:hover { text-decoration: none; color: inherit; border-color: #c7d2fe; }
        .tarjeta-estado.activa { border-color: #4f46e5; }
        .tarjeta-estado .numero { font-size: 1.8rem; font-weight: 800; line-height: 1.1; }
        .tarjeta-estado .texto { font-size: 0.85rem; color: #6b7280; font-weight: 600; }
        .serial-texto { font-family: monospace; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; }
    </style>
@stop

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-barcode"></i>
                </div>
                <div>
                    <h1 class="title-main">Seriales de título</h1>
                    <p class="title-subtitle">
                        @if ($anio)
                            Entregados por la Zona Educativa · año escolar {{ $anio->inicio_anio_escolar->format('Y') }} - {{ $anio->cierre_anio_escolar->format('Y') }}
                        @else
                            No hay un año escolar seleccionado
                        @endif
                    </p>
                </div>
            </div>
            @can('titulo_seriales.gestionar')
                @if ($anio && \App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#crearSeriales">
                        <i class="fas fa-plus"></i> <span>Registrar seriales</span>
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

        {{-- Cuántos hay en cada estado (al hacer clic, filtra por ese estado) --}}
        <div class="tarjetas-estado">
            <a href="{{ route('admin.titulo_seriales.index') }}" class="tarjeta-estado {{ $estado ? '' : 'activa' }}">
                <div class="numero">{{ $conteo->sum() }}</div>
                <div class="texto">Todos</div>
            </a>
            @foreach (\App\Enums\EstadoSerialTitulo::cases() as $caso)
                <a href="{{ route('admin.titulo_seriales.index', ['estado' => $caso->value]) }}"
                    class="tarjeta-estado {{ $estado === $caso ? 'activa' : '' }}">
                    <div class="numero">{{ $conteo[$caso->value] ?? 0 }}</div>
                    <div class="texto"><span class="badge-estado badge-{{ $caso->value }}">{{ $caso->label() }}</span></div>
                </a>
            @endforeach
        </div>

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Seriales{{ $estado ? ' · ' . $estado->label() . 's' : '' }}</h3>
                        <p class="mb-0 text-muted">{{ $seriales->total() }} registros encontrados</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('admin.titulo_seriales.index') }}" class="d-flex gap-2">
                    @if ($estado) <input type="hidden" name="estado" value="{{ $estado->value }}"> @endif
                    <input type="text" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar serial..." style="max-width: 220px">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    @if ($buscar !== '')
                        <a href="{{ route('admin.titulo_seriales.index', array_filter(['estado' => $estado?->value])) }}" class="btn btn-outline-secondary" title="Quitar búsqueda">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="text-align: center">Serial</th>
                                <th style="text-align: center">Recibido</th>
                                <th style="text-align: center">Estado</th>
                                <th>Motivo</th>
                                <th style="text-align: center">Devuelto el</th>
                                <th style="text-align: center; width: 120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($seriales as $serial)
                                <tr>
                                    <td style="text-align: center"><span class="serial-texto">{{ $serial->serial }}</span></td>
                                    <td style="text-align: center">{{ $serial->fecha_recepcion?->format('d/m/Y') ?? '—' }}</td>
                                    <td style="text-align: center">
                                        <span class="badge-estado badge-{{ $serial->estado->value }}">{{ $serial->estado->label() }}</span>
                                    </td>
                                    <td>{{ $serial->motivo_anulacion ?? '—' }}</td>
                                    <td style="text-align: center">{{ $serial->fecha_liquidacion?->format('d/m/Y') ?? '—' }}</td>
                                    <td style="text-align: center">
                                        @can('titulo_seriales.gestionar')
                                            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                <div class="action-buttons">
                                                    <button class="action-btn btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#editarSerial{{ $serial->id }}" title="Editar">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    @if ($serial->estado === \App\Enums\EstadoSerialTitulo::Disponible)
                                                        <button class="action-btn btn-delete" data-bs-toggle="modal"
                                                            data-bs-target="#eliminarSerial{{ $serial->id }}" title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-barcode"></i></div>
                                            <h4>No hay seriales{{ $estado || $buscar !== '' ? ' con ese filtro' : ' registrados' }}</h4>
                                            <p>Registra los seriales que entregó la Zona Educativa.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('titulo_seriales.gestionar')
                @if ($anio)
                    @include('admin.titulo_seriales.modales.createModal')
                @endif
                @foreach ($seriales as $serial)
                    @include('admin.titulo_seriales.modales.editModal')
                    @if ($serial->estado === \App\Enums\EstadoSerialTitulo::Disponible)
                        @include('admin.titulo_seriales.modales.deleteModal')
                    @endif
                @endforeach
            @endcan

            <div class="card-footer-modern d-flex flex-wrap align-items-center justify-content-between">
                <div>{{ $seriales->links() }}</div>
            </div>
        </div>
    </div>
@stop

@section('js')
    @include('admin.lapsos._reabrir_modal')
    @include('admin.lapsos._validacion_tiempo_real')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formato = /^[A-Z0-9-]{3,30}$/;
            const hoy = @json(now()->toDateString());
            const registrados = new Set(@json($registrados));

            // ----- Registrar: la lista pegada, línea por línea -----
            document.querySelectorAll('form.form-serial-crear').forEach((form) => {
                const contador = form.querySelector('#crear_seriales_contador');
                const leerLista = () => form.querySelector('[name="seriales"]').value
                    .split(/\r\n|\r|\n/).map((l) => l.trim().toUpperCase()).filter(Boolean);

                form.querySelector('[name="seriales"]').addEventListener('input', () => {
                    const total = leerLista().length;
                    contador.textContent = total === 0
                        ? 'Se registrarán como disponibles en el año escolar seleccionado.'
                        : `${total} ${total === 1 ? 'serial' : 'seriales'} para registrar como disponibles.`;
                });

                ValidacionTiempoReal(form, (f) => {
                    const e = {};
                    const lista = leerLista();
                    const vistos = new Set();
                    const problemas = [];

                    lista.forEach((s) => {
                        if (!formato.test(s)) problemas.push(`"${s}" no es válido (solo letras, números y guiones, de 3 a 30 caracteres)`);
                        else if (vistos.has(s)) problemas.push(`"${s}" está repetido en la lista`);
                        else if (registrados.has(s)) problemas.push(`"${s}" ya está registrado`);
                        vistos.add(s);
                    });

                    if (lista.length === 0) e.seriales = 'Pega al menos un serial (uno por línea).';
                    else if (lista.length > 500) e.seriales = 'No se pueden registrar más de 500 seriales de una vez.';
                    else if (problemas.length) e.seriales = 'Revisa: ' + problemas.slice(0, 5).join('; ') + (problemas.length > 5 ? ` y ${problemas.length - 5} más.` : '.');

                    const fecha = f.querySelector('[name="fecha_recepcion"]').value;
                    if (fecha && fecha > hoy) e.fecha_recepcion = 'La fecha de recepción no puede ser futura.';
                    return e;
                });
            });

            // ----- Editar: mostrar u ocultar motivo y fecha de devolución según el estado -----
            document.querySelectorAll('form.form-serial-editar').forEach((form) => {
                const selector = form.querySelector('.selector-estado');
                const ajustarCampos = () => {
                    form.querySelectorAll('[data-solo-estados]').forEach((bloque) => {
                        bloque.style.display = bloque.dataset.soloEstados.split(',').includes(selector.value) ? '' : 'none';
                    });
                };
                selector.addEventListener('change', ajustarCampos);
                ajustarCampos();

                ValidacionTiempoReal(form, (f) => {
                    const valor = (nombre) => f.querySelector(`[name="${nombre}"]`).value.trim();
                    const original = f.dataset.serialOriginal;
                    const e = {};

                    const serial = valor('serial').toUpperCase();
                    if (serial === '') e.serial = 'Debes indicar el serial.';
                    else if (!formato.test(serial)) e.serial = 'El serial no es válido: usa solo letras, números y guiones (de 3 a 30 caracteres).';
                    else if (serial !== original && registrados.has(serial)) e.serial = 'Ese serial ya está registrado.';
                    else if (f.dataset.estadoOriginal === 'asignado' && serial !== original) e.serial = 'Este serial ya está asignado a un título: su número no se puede cambiar.';

                    const recepcion = valor('fecha_recepcion');
                    if (recepcion && recepcion > hoy) e.fecha_recepcion = 'La fecha de recepción no puede ser futura.';

                    const estado = valor('estado');
                    if (['anulado', 'devuelto'].includes(estado) && valor('motivo_anulacion') === '') {
                        e.motivo_anulacion = 'Para anular o devolver un serial debes indicar el motivo.';
                    }

                    const liquidacion = valor('fecha_liquidacion');
                    if (estado === 'devuelto') {
                        if (!liquidacion) e.fecha_liquidacion = 'Para marcar un serial como devuelto debes indicar la fecha de devolución.';
                        else if (liquidacion > hoy) e.fecha_liquidacion = 'La fecha de devolución no puede ser futura.';
                        else if (recepcion && liquidacion < recepcion) e.fecha_liquidacion = 'La fecha de devolución no puede ser anterior a la de recepción.';
                    }

                    return e;
                });
            });
        });
    </script>
@stop
