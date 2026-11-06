@extends('adminlte::page')

@section('title', 'Gestión de Representantes')

@section('css')
    {{-- Estilos modernos reutilizados del sistema --}}
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            font-size: 0.8em;
        }

        .action-buttons .dropdown-menu {
            min-width: 10rem;
        }

        .table-modern {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .table-modern th,
        .table-modern td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .table-modern thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .table-modern tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Estilos personalizados para los badges de tipo de representante */
        .status-legal {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border: 1px solid rgba(79, 70, 229, 0.2);
        }

        .status-progenitor {
            background: rgba(59, 130, 246, 0.1);
            color: #1d4ed8;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status-legal .status-dot {
            background: #4f46e5;
        }

        .status-progenitor .status-dot {
            background: #1d4ed8;
        }

        /* Estilos mejorados para el modal - usando los mismos estilos que el sistema */
        .details-card {
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            background: white;
            transition: all 0.2s ease;
        }

        .detail-item:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
            border-color: var(--info);
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 140px;
        }

        .detail-label i {
            color: var(--info);
            font-size: 1rem;
        }

        .detail-value {
            font-weight: 500;
            color: var(--gray-800);
            text-align: right;
            flex: 1;
            margin-left: 1rem;
        }

        .detail-value.fw-bold {
            color: var(--gray-800);
            font-size: 0.95rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--info-dark);
            margin-top: 1rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
                margin: 1rem;
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem 0;
            }

            .detail-label {
                min-width: auto;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            .detail-value {
                text-align: left;
                margin-left: 0;
                font-size: 0.95rem;
            }

            .card-header-custom {
                margin: -1.5rem -1.5rem 1rem -1.5rem;
                padding: 1rem 1.5rem;
            }

            .details-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .modal-body-view {
                padding: 1rem !important;
            }

            .row.g-4>div {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .modal-header-view {
                padding: 1rem;
            }

            .modal-title-view {
                font-size: 1.1rem !important;
            }

            .detail-label i {
                width: 18px;
                font-size: 0.9rem;
            }

            .badge.fs-6 {
                font-size: 0.7rem !important;
            }
        }

        /* Animaciones y transiciones */
        .modal-content {
            animation: slideInUp 0.3s ease-out;
        }

        @keyframes slideInUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Mejoras para badges */
        .badge {
            font-weight: 500;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        .badge.bg-success {
            background-color: #10b981 !important;
        }

        .badge.bg-primary {
            background-color: #3b82f6 !important;
        }

        .badge.bg-info {
            background-color: #06b6d4 !important;
        }

        .badge.bg-secondary {
            background-color: #6b7280 !important;
        }
    </style>
@stop

@section('content_header')
    {{-- Encabezado principal de la página --}}
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>
                    <h1 class="title-main">Gestión de Representantes</h1>
                    <p class="title-subtitle">Administración de los representantes del sistema</p>
                </div>
            </div>

            {{-- Botón crear --}}
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" class="btn-create"
                    onclick="window.location.href='{{ route('representante.formulario') }}'"
                    title="Registrar Representante">
                    <i class="fas fa-plus"></i>
                    <span>Registrar</span>
                </button>
            @endif

        </div>
    </div>
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="main-container">
        @include('partials.alerta-modo-consulta')

        <div class="card-modern">
            <div class="card-header-modern d-flex align-items-center justify-content-between flex-wrap gap-3">

                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">Listado de Representantes</h3>
                        <p class="mb-0">{{ $representantes->total() }} registros encontrados</p>
                    </div>
                </div>

                <div class="header-right d-flex align-items-center gap-2 flex-wrap">

                    <form action="{{ route('representante.index') }}" method="GET" class="mb-0 search-sm">
                        <div class="search-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" title="Buscar por cedula, nombre o apellido" name="buscar" id="buscador" class="form-control-modern"
                                placeholder="Buscar..." value="{{ request('buscar') }}" autocomplete="off">
                        </div>
                    </form>

                    <button class="btn-modal-create" data-bs-toggle="modal" data-bs-target="#modalFiltros">
                        <i class="fas fa-filter"></i>
                    </button>

                    <a href="{{ route('representante.reporte_pdf', request()->query()) }}" class="btn-pdf" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF General
                    </a>

                    @php
                        $anioActivo = \App\Models\AnioEscolar::activos()->first();
                        $anioExtendido = \App\Models\AnioEscolar::where('status', 'Extendido')->first();
                        $mostrarAnio = $anioActivo ?? $anioExtendido;
                    @endphp

                    @if ($mostrarAnio)
                        <div class="d-flex align-items-center bg-light rounded px-2 py-1 border">
                            <span class="badge bg-primary me-2" style="font-size: 0.7rem;">
                                <i class="fas fa-calendar-check me-1"></i> Calendario Escolar
                            </span>
                            <span class="text-muted me-2" style="font-size: 0.8rem;">
                                <i class="fas fa-play-circle text-primary me-1"></i>
                                {{ \Carbon\Carbon::parse($mostrarAnio->inicio_anio_escolar)->format('d/m/Y') }}
                            </span>
                            <span class="text-muted" style="font-size: 0.8rem;">
                                <i class="fas fa-flag-checkered text-danger me-1"></i>
                                {{ \Carbon\Carbon::parse($mostrarAnio->cierre_anio_escolar)->format('d/m/Y') }}
                            </span>
                        </div>
                    @else
                        <div
                            class="d-flex align-items-center bg-warning bg-opacity-10 rounded px-2 py-1 border border-warning">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            <span class="fw-semibold" style="font-size: 0.8rem;">Sin año activo</span>
                        </div>
                    @endif

                </div>
            </div>


            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern overflow-hidden">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center">Cédula</th>
                                <th style="text-align: center">Nombre</th>
                                <th style="text-align: center">Apellido</th>
                                <th style="text-align: center">Tipo</th>
                                <th style="text-align: center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-representantes" style="text-align: center">
                            @forelse($representantes as $rep)
                                <tr>
                                    <td><strong>{{ $rep->persona->numero_documento ?? 'N/A' }}</strong></td>
                                    <td>{{ $rep->persona->primer_nombre ?? 'N/A' }}</td>
                                    <td>{{ $rep->persona->primer_apellido ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $tipoRepresentante = $rep->legal ? 'Representante Legal' : 'Progenitor';
                                        @endphp
                                        <span
                                            class="status-badge {{ $tipoRepresentante === 'Representante Legal' ? 'status-legal' : 'status-progenitor' }}">
                                            <span class="status-dot"></span>
                                            {{ $tipoRepresentante }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                    title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    {{-- Ver mas --}}
                                                    <li>
                                                        <button
                                                            class="dropdown-item d-flex align-items-center text-primary view-details-btn"
                                                            data-bs-toggle="modal" data-bs-target="#modalVerDetalleRegistro"
                                                            data-persona='@json($rep->persona)'
                                                            data-representante='@json($rep->toArray())'
                                                            data-legal='@json($rep->legal)'
                                                            data-banco='@json($rep->legal ? $rep->legal->banco : null)' title="Ver detalle">
                                                            <i class="fas fa-eye me-2"></i>
                                                            Ver
                                                        </button>
                                                    </li>

                                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                        {{-- Editar --}}
                                                        <li>
                                                            <a href="{{ route('representante.editar', $rep->id) }}"
                                                                class="dropdown-item d-flex align-items-center text-warning"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2"></i>
                                                                Editar
                                                            </a>
                                                        </li>

                                                        {{-- Inactivar --}}
                                                        <li>
                                                            <button class="dropdown-item d-flex align-items-center text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminarRepresentante{{ $rep->id }}"
                                                                title="Eliminar">
                                                                <i class="fas fa-trash me-2"></i>
                                                                Eliminar
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>

                                        {{-- Modal de confirmación para eliminar representante --}}
                                        <div class="modal fade" id="confirmarEliminarRepresentante{{ $rep->id }}"
                                            tabindex="-1" aria-labelledby="modalLabelEliminarRep{{ $rep->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content modal-modern">
                                                    <div class="modal-header-delete">
                                                        <div class="modal-icon-delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </div>
                                                        <h5 class="modal-title-delete"
                                                            id="modalLabelEliminarRep{{ $rep->id }}">Confirmar
                                                            Eliminación</h5>
                                                        <button type="button" class="btn-close-modal"
                                                            data-bs-dismiss="modal" aria-label="Cerrar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body-delete">
                                                        <p>¿Deseas eliminar este representante?</p>
                                                        <p class="delete-warning">
                                                            Esta acción es un borrado suave: el registro no se eliminará
                                                            físicamente,
                                                            pero dejará de aparecer en los listados.
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer-delete">
                                                        <form action="{{ route('representante.destroy', $rep->id) }}"
                                                            method="POST" class="w-100">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="footer-buttons">
                                                                <button type="button" class="btn-modal-cancel"
                                                                    data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit"
                                                                    class="btn-modal-delete">Eliminar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">No hay representantes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer clearfix">
                <div class="d-flex justify-content-end">
                    {{ $representantes->links() }}
                </div>
            </div>
        </div>

        {{-- Modal de detalles del representante --}}
        @include('admin.representante.modales.showModal')

    </div>
    </div>
    </div>
@endsection

@include('admin.representante.modales.filtroModal')

@section('js')
    <script>
        // Obtener las listas de datos desde el controlador
        const ocupaciones = @json(\App\Models\Ocupacion::all());
        const tiposDocumentos = @json(\App\Models\TipoDocumento::all());
        const generos = @json(\App\Models\Genero::all());
        const estados = @json(\App\Models\Estado::all());
        const municipios = @json(\App\Models\Municipio::all());
        const parroquias = @json(\App\Models\Localidad::all());
        const bancos = @json(\App\Models\Banco::all());
        const paises = @json($paises);

        // Inicialización cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('JavaScript cargado y DOM listo');

            // Manejar clic en botones de ver detalles
            document.addEventListener('click', function(e) {
                console.log('Click detectado en:', e.target);
                const btn = e.target.closest('.view-details-btn');
                if (btn) {
                    console.log('Botón Ver encontrado');
                    e.preventDefault();

                    // Obtener los datos de los atributos data
                    const personaData = btn.dataset.persona ? JSON.parse(btn.dataset.persona) : null;
                    const representanteData = btn.dataset.representante ? JSON.parse(btn.dataset
                        .representante) : null;
                    const legalData = btn.dataset.legal && btn.dataset.legal !== 'null' ? JSON.parse(btn
                        .dataset.legal) : null;
                    const bancoData = btn.dataset.banco && btn.dataset.banco !== 'null' ? JSON.parse(btn
                        .dataset.banco) : null;

                    // Debug detallado
                    console.log('=== DATOS RECIBIDOS EN EL CLIENTE ===');
                    console.log('personaData:', personaData);
                    console.log('representanteData:', representanteData);
                    console.log('legalData:', legalData);
                    console.log('bancoData:', bancoData);

                    if (personaData) {
                        console.log('Campos de persona:', {
                            primer_nombre: personaData.primer_nombre,
                            primer_apellido: personaData.primer_apellido,
                            numero_documento: personaData.numero_documento,
                            tipo_documento: personaData.tipo_documento,
                            genero: personaData.genero,
                            prefijo: personaData.prefijo,
                            prefijo_dos: personaData.prefijo_dos,
                            telefono: personaData.telefono,
                            telefono_dos: personaData.telefono_dos,
                            direccion: personaData.direccion
                        });
                    }

                    if (representanteData) {
                        console.log('Campos de representante:', {
                            estado: representanteData.estado,
                            municipios: representanteData.municipios,
                            localidads: representanteData.localidads,
                            ocupacion: representanteData.ocupacion,
                            ocupacion_representante: representanteData.ocupacion_representante,
                            convivenciaestudiante_representante: representanteData
                                .convivenciaestudiante_representante
                        });
                    }

                    // Llenar el modal con los datos
                    llenarModalRepresentante(personaData, representanteData, legalData, bancoData);

                    // Mostrar el modal usando Bootstrap 5
                    const modal = new bootstrap.Modal(document.getElementById('modalVerDetalleRegistro'));
                    modal.show();
                }
            });

            // Corregir problema del backdrop que no se cierra
            const modalElement = document.getElementById('modalVerDetalleRegistro');
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    // Eliminar cualquier backdrop residual
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());

                    // Restaurar el scroll del body
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';

                    // Eliminar cualquier clase modal-open del body
                    document.body.classList.remove('modal-open');
                });

                // También manejar el evento show para asegurar que se limpie antes de abrir
                modalElement.addEventListener('show.bs.modal', function() {
                    // Limpiar cualquier backdrop residual antes de mostrar
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                });
            }
        });

        // Configuración de fechas por defecto
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer rango de fechas por defecto (mes actual)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            // Formatear fechas para el input date (YYYY-MM-DD)
            const formatDate = (date) => {
                const d = new Date(date);
                let month = '' + (d.getMonth() + 1);
                let day = '' + d.getDate();
                const year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            };

            // Establecer valores por defecto
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            if (fechaInicio) fechaInicio.value = formatDate(firstDay);
            if (fechaFin) fechaFin.value = formatDate(today);

            // Validación de fechas
            const formReporte = document.getElementById('formReporte');
            if (formReporte) {
                formReporte.addEventListener('submit', function(e) {
                    const fechaInicioVal = document.getElementById('fecha_inicio').value;
                    const fechaFinVal = document.getElementById('fecha_fin').value;

                    if (fechaInicioVal && fechaFinVal && new Date(fechaInicioVal) > new Date(fechaFinVal)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'La fecha de inicio no puede ser mayor a la fecha de fin',
                            confirmButtonColor: '#3085d6',
                        });
                    }
                });
            }
        });

        // Función para llenar el modal con los datos del representante
        function llenarModalRepresentante(persona, representante, legal, banco) {
            try {
                console.log('=== llenarModalRepresentante llamado ===');
                console.log('persona:', persona);
                console.log('representante:', representante);
                console.log('legal:', legal);
                console.log('banco:', banco);

                // Debug: verificar estructura de datos
                console.log('Estructura de persona:', {
                    tiene_tipo_documento: !!persona.tipo_documento,
                    tiene_genero: !!persona.genero,
                    tiene_prefijo: !!persona.prefijo,
                    tiene_prefijo_dos: !!persona.prefijo_dos
                });

                console.log('Estructura de representante:', {
                    tiene_estado: !!representante.estado,
                    tiene_municipios: !!representante.municipios,
                    tiene_localidads: !!representante.localidads,
                    tiene_ocupacion: !!representante.ocupacion
                });

                // Asegurarse de que los datos sean objetos
                if (typeof persona === 'string') persona = JSON.parse(persona);
                if (typeof representante === 'string') representante = JSON.parse(representante);
                if (legal && typeof legal === 'string') legal = JSON.parse(legal);
                if (banco && typeof banco === 'string') banco = JSON.parse(banco);

                // Actualizar el título con el tipo de representante
                const tipoBadge = document.getElementById('tipo-representante-badge');
                const isLegal = legal && (legal.id || (typeof legal === 'object' && Object.keys(legal).length > 0));

                if (isLegal) {
                    // Es representante legal
                    tipoBadge.textContent = 'Representante Legal';
                    tipoBadge.className = 'badge bg-primary fs-6';
                } else {
                    // Es progenitor
                    tipoBadge.textContent = 'Progenitor';
                    tipoBadge.className = 'badge bg-info fs-6';
                }

                // === DATOS PERSONALES ===

                // Tipo de documento
                let tipoDocumento = '';
                if (persona.tipo_documento && persona.tipo_documento.nombre) {
                    tipoDocumento = persona.tipo_documento.nombre;
                } else if (persona.tipo_documento_id) {
                    // Buscar en el array global de tipos de documento si existe
                    if (typeof tiposDocumentos !== 'undefined' && tiposDocumentos.length > 0) {
                        const tipoDoc = tiposDocumentos.find(td => td.id == persona.tipo_documento_id);
                        tipoDocumento = tipoDoc ? tipoDoc.nombre : `ID: ${persona.tipo_documento_id}`;
                    } else {
                        tipoDocumento = `ID: ${persona.tipo_documento_id}`;
                    }
                }
                document.getElementById('modal-tipo-documento').textContent = tipoDocumento || 'No especificado';

                // Número de cédula
                document.getElementById('modal-numero_documento').textContent = persona.numero_documento ||
                    'No especificado';

                // Nombres completos
                const nombres = [persona.primer_nombre, persona.segundo_nombre, persona.tercer_nombre]
                    .filter(n => n && n.trim())
                    .join(' ');
                document.getElementById('modal-primer-nombre').textContent = persona.primer_nombre || '';
                document.getElementById('modal-segundo-nombre').textContent = persona.segundo_nombre ? ' ' + persona
                    .segundo_nombre : '';
                document.getElementById('modal-tercer-nombre').textContent = persona.tercer_nombre ? ' ' + persona
                    .tercer_nombre : '';

                // Apellidos completos
                const apellidos = [persona.primer_apellido, persona.segundo_apellido]
                    .filter(a => a && a.trim())
                    .join(' ');
                document.getElementById('modal-primer-apellido').textContent = persona.primer_apellido || '';
                document.getElementById('modal-segundo-apellido').textContent = persona.segundo_apellido ? ' ' + persona
                    .segundo_apellido : '';

                // Fecha de nacimiento
                let fechaNacimiento = persona.fecha_nacimiento ? new Date(persona.fecha_nacimiento) : null;
                if (fechaNacimiento && !isNaN(fechaNacimiento)) {
                    const dia = String(fechaNacimiento.getDate()).padStart(2, '0');
                    const mes = String(fechaNacimiento.getMonth() + 1).padStart(2, '0');
                    const anio = fechaNacimiento.getFullYear();
                    document.getElementById('modal-fecha-nacimiento').textContent = `${dia}/${mes}/${anio}`;
                } else {
                    document.getElementById('modal-fecha-nacimiento').textContent = 'No especificada';
                }

                // Género
                let genero = '';
                if (persona.genero && persona.genero.genero) {
                    genero = persona.genero.genero;
                } else if (persona.genero_id) {
                    // Buscar en el array global de géneros si existe
                    if (typeof generos !== 'undefined' && generos.length > 0) {
                        const gen = generos.find(g => g.id == persona.genero_id);
                        genero = gen ? gen.genero : `ID: ${persona.genero_id}`;
                    } else {
                        genero = `ID: ${persona.genero_id}`;
                    }
                }
                document.getElementById('modal-genero').textContent = genero || 'No especificado';

                // === CONTACTO Y UBICACIÓN ===

                // Teléfono principal
                let telefonoCompleto = '';
                if (persona.prefijo && persona.prefijo.prefijo) {
                    telefonoCompleto = persona.prefijo.prefijo + ' ' + (persona.telefono || '');
                } else if (persona.telefono) {
                    telefonoCompleto = persona.telefono;
                }
                document.getElementById('modal-telefono').textContent = telefonoCompleto || 'No especificado';

                // Teléfono secundario
                let telefonoDosCompleto = '';
                if (persona.prefijo_dos && persona.prefijo_dos.prefijo) {
                    telefonoDosCompleto = persona.prefijo_dos.prefijo + ' ' + (persona.telefono_dos || '');
                } else if (persona.telefono_dos) {
                    telefonoDosCompleto = persona.telefono_dos;
                }
                document.getElementById('modal-telefono-dos').textContent = telefonoDosCompleto || 'No especificado';

                // Correo electrónico (solo para representantes legales)
                if (document.getElementById('modal-correo')) {
                    const correoItem = document.getElementById('correo-detail-item');
                    if (legal && legal.correo_representante) {
                        document.getElementById('modal-correo').textContent = legal.correo_representante;
                        if (correoItem) correoItem.style.display = '';
                    } else {
                        document.getElementById('modal-correo').textContent = '';
                        if (correoItem) correoItem.style.display = 'none';
                    }
                }

                // Ubicación - País
                let pais = '';
                if (representante.pais && representante.pais.nameES) {
                    pais = representante.pais.nameES;
                } else if (representante.pais_id) {
                    // Buscar en el array global de países si existe
                    if (typeof paises !== 'undefined' && paises.length > 0) {
                        const pai = paises.find(p => p.id == representante.pais_id);
                        pais = pai ? pai.nameES : 'País no especificado';
                    } else {
                        pais = 'País no especificado';
                    }
                } else {
                    pais = 'País no especificado';
                }
                document.getElementById('modal-pais').textContent = pais;

                // Ubicación - Estado
                let estado = '';
                if (representante.estado && representante.estado.nombre_estado) {
                    estado = representante.estado.nombre_estado;
                } else if (representante.estado_id) {
                    // Buscar en el array global de estados si existe
                    if (typeof estados !== 'undefined' && estados.length > 0) {
                        const est = estados.find(e => e.id == representante.estado_id);
                        estado = est ? est.nombre_estado : `ID: ${representante.estado_id}`;
                    } else {
                        estado = `ID: ${representante.estado_id}`;
                    }
                }
                document.getElementById('modal-estado').textContent = estado || 'No especificado';

                // Ubicación - Municipio
                let municipio = '';
                if (representante.municipios && representante.municipios.nombre_municipio) {
                    municipio = representante.municipios.nombre_municipio;
                } else if (representante.municipio_id) {
                    // Buscar en el array global de municipios si existe
                    if (typeof municipios !== 'undefined' && municipios.length > 0) {
                        const mun = municipios.find(m => m.id == representante.municipio_id);
                        municipio = mun ? mun.nombre_municipio : `ID: ${representante.municipio_id}`;
                    } else {
                        municipio = `ID: ${representante.municipio_id}`;
                    }
                }
                document.getElementById('modal-municipio').textContent = municipio || 'No especificado';

                // Ubicación - Parroquia
                let parroquia = '';
                if (representante.localidads && representante.localidads.nombre_localidad) {
                    parroquia = representante.localidads.nombre_localidad;
                } else if (representante.parroquia_id) {
                    // Buscar en el array global de parroquias si existe
                    if (typeof parroquias !== 'undefined' && parroquias.length > 0) {
                        const parr = parroquias.find(p => p.id == representante.parroquia_id);
                        parroquia = parr ? parr.nombre_localidad : `ID: ${representante.parroquia_id}`;
                    } else {
                        parroquia = `ID: ${representante.parroquia_id}`;
                    }
                }
                document.getElementById('modal-parroquia').textContent = parroquia || 'No especificado';

                // Dirección de habitación
                document.getElementById('modal-direccion').textContent = persona.direccion || 'No especificada';

                // === INFORMACIÓN LABORAL ===

                // Ocupación
                let ocupacionNombre = '';
                if (representante.ocupacion && representante.ocupacion.nombre_ocupacion) {
                    ocupacionNombre = representante.ocupacion.nombre_ocupacion;
                } else if (typeof ocupaciones !== 'undefined' && ocupaciones.length > 0 && representante
                    .ocupacion_representante) {
                    const ocupacion = ocupaciones.find(oc => oc.id == representante.ocupacion_representante);
                    ocupacionNombre = ocupacion ? ocupacion.nombre_ocupacion :
                        `ID: ${representante.ocupacion_representante}`;
                } else if (representante.ocupacion_representante) {
                    ocupacionNombre = `ID: ${representante.ocupacion_representante}`;
                }
                document.getElementById('modal-ocupacion').textContent = ocupacionNombre || 'No especificada';

                // Convive con el estudiante
                let convive = representante.convivenciaestudiante_representante;
                const conviveElement = document.getElementById('modal-convive');
                if (typeof convive !== 'undefined' && convive !== null) {
                    const conviveValue = (convive === true || convive === 1 || convive === 'si' || convive === 'Sí');
                    conviveElement.textContent = conviveValue ? 'Sí' : 'No';
                    conviveElement.className = conviveValue ? 'badge bg-success' : 'badge bg-secondary';
                } else {
                    conviveElement.textContent = 'No especificado';
                    conviveElement.className = 'badge bg-secondary';
                }

                // === SECCIÓN DE REPRESENTANTE LEGAL ===
                const legalSection = document.getElementById('legal-info-section');
                if (legal && legalSection) {
                    legalSection.style.display = 'block';

                    // Parentesco
                    document.getElementById('modal-parentesco').textContent = legal.parentesco || 'No especificado';

                    // Carnet de la patria (afiliado o no)
                    const carnetEl = document.getElementById('modal-carnet-afiliado');
                    if (carnetEl) {
                        const tieneCarnet = legal.carnet_patria_afiliado;
                        let texto = 'No especificado';
                        let badgeClass = 'badge bg-secondary';
                        
                        if (tieneCarnet === 1) {
                            texto = 'Madre';
                            badgeClass = 'badge bg-info';
                        } else if (tieneCarnet === 2) {
                            texto = 'Padre';
                            badgeClass = 'badge bg-primary';
                        } else if (tieneCarnet === 3) {
                            texto = 'Otro';
                            badgeClass = 'badge bg-warning';
                        }
                        
                        carnetEl.textContent = texto;
                        carnetEl.className = badgeClass;
                    }

                    // Código y serial de carnet
                    if (document.getElementById('modal-codigo')) {
                        document.getElementById('modal-codigo').textContent = legal.codigo_carnet_patria_representante ||
                            'No especificado';
                    }
                    if (document.getElementById('modal-serial')) {
                        document.getElementById('modal-serial').textContent = legal.serial_carnet_patria_representante ||
                            'No especificado';
                    }

                    // Pertenece a organización
                    const perteneceOrgEl = document.getElementById('modal-pertenece-org');
                    const campoOrg = document.getElementById('campo-organizacion');
                    if (perteneceOrgEl) {
                        const pertenece = legal.pertenece_a_organizacion_representante;
                        if (pertenece === 1 || pertenece === true) {
                            perteneceOrgEl.textContent = 'Sí';
                            perteneceOrgEl.className = 'badge bg-success';
                            if (campoOrg) campoOrg.style.display = '';
                            document.getElementById('modal-org-pertenece').textContent = legal
                                .cual_organizacion_representante || 'No especificada';
                        } else {
                            perteneceOrgEl.textContent = 'No';
                            perteneceOrgEl.className = 'badge bg-secondary';
                            if (campoOrg) campoOrg.style.display = 'none';
                            document.getElementById('modal-org-pertenece').textContent = '';
                        }
                    }

                    // Tipo de cuenta bancaria
                    document.getElementById('modal-tipo-cuenta').textContent = legal.tipo_cuenta || 'No especificado';

                    // Banco
                    let bancoNombre = '';
                    if (banco && banco.nombre_banco) {
                        bancoNombre = banco.nombre_banco;
                    } else if (legal && legal.banco_id) {
                        // Buscar en el array global de bancos si existe
                        if (typeof bancos !== 'undefined' && bancos.length > 0) {
                            const ban = bancos.find(b => b.id == legal.banco_id);
                            bancoNombre = ban ? ban.nombre_banco : `ID: ${legal.banco_id}`;
                        } else {
                            bancoNombre = `ID: ${legal.banco_id}`;
                        }
                    }
                    document.getElementById('modal-banco').textContent = bancoNombre || 'No especificado';

                } else if (legalSection) {
                    // Si no es representante legal, ocultar sección
                    legalSection.style.display = 'none';

                    // Y asegurarse de que el bloque de correo no se muestre para progenitor
                    const correoItem = document.getElementById('correo-detail-item');
                    if (correoItem) correoItem.style.display = 'none';
                }

            } catch (e) {
                console.error('Error al llenar el modal de representante:', e);
            }
        }

        // Actualizar el enlace de generación de PDF con los filtros actuales
        function actualizarEnlacePDF() {
            const generarPdfBtn = document.getElementById('generarPdfBtn');
            if (generarPdfBtn) {
                // Obtener todos los parámetros de la URL actual
                const urlParams = new URLSearchParams(window.location.search);

                // Construir la URL base del reporte
                let reportUrl = generarPdfBtn.getAttribute('href').split('?')[0];
                const params = new URLSearchParams();

                // Agregar todos los parámetros de filtro actuales
                for (const [key, value] of urlParams.entries()) {
                    if (key === 'page') continue; // No incluir la paginación en el reporte
                    params.append(key, value);
                }

                // Si hay parámetros, agregarlos a la URL
                if (params.toString()) {
                    reportUrl += '?' + params.toString();
                }

                // Actualizar el atributo href del botón
                generarPdfBtn.setAttribute('href', reportUrl);

                console.log('Enlace de PDF actualizado:', reportUrl);
            }
        }

        // Manejar el envío del formulario de filtros
        const filtroForm = document.getElementById('filtroForm');
        if (filtroForm) {
            filtroForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Obtener el valor seleccionado
                const tipoRepresentante = document.getElementById('tipo_representante').value;

                // Construir la URL con los parámetros de filtro
                const url = new URL(window.location.href.split('?')[0]);

                // Solo agregar el parámetro si hay un valor seleccionado
                if (tipoRepresentante !== '') {
                    url.searchParams.set('es_legal', tipoRepresentante);
                } else {
                    url.searchParams.delete('es_legal');
                }

                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalFiltros'));
                if (modal) {
                    modal.hide();
                }

                // Actualizar el enlace del PDF antes de redirigir
                actualizarEnlacePDF();

                // Redirigir a la URL con los filtros aplicados
                window.location.href = url.toString();
            });
        }

        // Inicializar el enlace de PDF cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar el enlace de PDF al cargar la página
            actualizarEnlacePDF();

            // Actualizar el enlace de PDF cuando cambie la URL (navegación con filtros)
            window.addEventListener('popstate', actualizarEnlacePDF);

            // Actualizar el enlace cuando se muestre el modal de filtros
            const filtroModal = document.getElementById('modalFiltros');
            if (filtroModal) {
                filtroModal.addEventListener('shown.bs.modal', function() {
                    // Asegurarse de que el select muestre el valor actual del filtro
                    const urlParams = new URLSearchParams(window.location.search);
                    const tipoRepresentante = urlParams.get('es_legal');
                    const selectTipo = document.getElementById('tipo_representante');
                    if (selectTipo) {
                        selectTipo.value = tipoRepresentante !== null ? tipoRepresentante : '';
                    }
                });
            }
            window.addEventListener('popstate', function() {
                actualizarEnlacePDF();
            });
        });
    </script>
@endsection
