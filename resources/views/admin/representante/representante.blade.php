@extends('adminlte::page')

@section('title', 'Gestión de Representantes')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('content_header')
    <div class="content-header-modern hero-header-horario p-3 p-md-4">
        <div class="header-content d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="header-title d-flex align-items-center gap-3">
                <div class="icon-wrapper">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>
                    <h1 class="title-main mb-1">Gestión de Representantes</h1>
                    <p class="title-subtitle mb-0">Administración de los representantes del sistema</p>
                </div>
            </div>

            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <a class="btn-hero-white w-100 w-md-auto justify-content-center mt-3 mt-md-0"
                    href="{{ route('representante.formulario') }}"
                    title="Registrar nuevo representante">
                    <i class="fas fa-plus"></i>
                    <span>Registrar</span>
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="main-container">
        @include('partials.alerta-modo-consulta')

        @php
            $activeChips = [];
            if (request()->filled('buscar')) {
                $activeChips[] = ['name' => 'buscar', 'label' => 'Búsqueda: ' . request('buscar')];
            }
            if (request()->filled('es_legal')) {
                $labelLegal = request('es_legal') === '1' ? 'Tipo: Representante Legal' : 'Tipo: Progenitor';
                $activeChips[] = ['name' => 'es_legal', 'label' => $labelLegal];
            }
            if (request()->filled('grado_id')) {
                $gradoSel = $grados->firstWhere('id', request('grado_id'));
                if ($gradoSel) {
                    $activeChips[] = ['name' => 'grado_id', 'label' => 'Nivel: ' . $gradoSel->numero_grado . '° Nivel'];
                }
            }
            if (request()->filled('seccion_id')) {
                $activeChips[] = ['name' => 'seccion_id', 'label' => 'Sección: ' . request('seccion_id')];
            }
        @endphp

        @if (!empty($activeChips))
            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                <span class="text-muted fw-semibold" style="font-size: 0.82rem;">Filtros activos:</span>
                @foreach ($activeChips as $chip)
                    <span class="filter-chip-saas">
                        <span>{{ $chip['label'] }}</span>
                        <a href="{{ route('representante.index', array_diff_key(request()->query(), [$chip['name'] => ''])) }}"
                            class="filter-chip-remove text-decoration-none"
                            aria-label="Quitar {{ $chip['label'] }}">
                            &times;
                        </a>
                    </span>
                @endforeach
                <a href="{{ route('representante.index') }}" class="btn-clear-chips ms-1 text-decoration-none">
                    Limpiar todo
                </a>
            </div>
        @endif

        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Listado de Representantes</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">{{ $representantes->total() }} registros encontrados</p>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-center gap-2 w-100 w-md-auto">

                    <form action="{{ route('representante.index') }}" method="GET" class="m-0 w-100 w-md-auto">
                        <div class="search-wrapper position-relative w-100 w-md-auto">
                            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                            <input type="text" title="Buscar por cédula, nombre o apellido" name="buscar" id="buscador" class="form-control search-input-modern pl-5 w-100 w-md-auto"
                                placeholder="Buscar representante..." value="{{ request('buscar') }}" autocomplete="off"
                                style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                        </div>
                    </form>

                    <x-ui.filter-bar target="modalFiltros" label="Filtrar" :active-count="count($activeChips)"
                        class="w-100 w-sm-auto text-nowrap" data-bs-toggle="offcanvas" data-bs-target="#modalFiltros" />

                    <a href="{{ route('representante.reporte_pdf', request()->query()) }}"
                       class="btn btn-pdf-global w-100 w-sm-auto text-nowrap"
                       target="_blank" title="Exportar reporte general en PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF General</span>
                    </a>

                </div>
            </div>


            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table-modern table-striped text-nowrap w-100" id="tablaRepresentantes">
                        <thead>
                            <tr style="text-align: center">
                                <th style="text-align: center; width: 140px;" class="text-nowrap">Cédula</th>
                                <th style="text-align: left;" class="text-nowrap">Nombre</th>
                                <th style="text-align: left;" class="text-nowrap">Apellido</th>
                                <th style="text-align: center; width: 180px;" class="text-nowrap">Tipo</th>
                                <th style="text-align: center; width: 100px;" class="text-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-representantes" style="text-align: center">
                            @forelse($representantes as $rep)
                                <tr style="text-align: center">
                                    <td class="fw-bold text-dark text-nowrap" style="font-size: 0.92rem;">
                                        {{ $rep->persona->tipoDocumento->nombre ?? 'V' }}-{{ $rep->persona->numero_documento ?? 'N/A' }}
                                    </td>
                                    <td class="text-start fw-semibold text-dark text-nowrap">{{ $rep->persona->primer_nombre ?? 'N/A' }}</td>
                                    <td class="text-start fw-semibold text-dark text-nowrap">{{ $rep->persona->primer_apellido ?? 'N/A' }}</td>
                                    <td class="text-center text-nowrap">
                                        @if ($rep->legal)
                                            <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem; border: 1px solid #DBEAFE;">
                                                <i class="fas fa-user-shield me-1"></i> Representante Legal
                                            </span>
                                        @else
                                            <span class="badge" style="background-color: #F1F5F9; color: #475569; font-weight: 600; padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.82rem; border: 1px solid #E2E8F0;">
                                                <i class="fas fa-user-friends me-1"></i> Progenitor
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="action-buttons">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-sm action-btn rounded-circle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                    title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end shadow-sm">
                                                    {{-- Ver mas --}}
                                                    <li>
                                                        <button
                                                            class="dropdown-item dropdown-item-modern text-primary d-flex align-items-center view-details-btn"
                                                            data-bs-toggle="modal" data-bs-target="#modalVerDetalleRegistro"
                                                            data-persona='@json($rep->persona)'
                                                            data-representante='@json($rep->toArray())'
                                                            data-legal='@json($rep->legal)'
                                                            data-banco='@json($rep->legal ? $rep->legal->banco : null)' title="Ver detalle">
                                                            <i class="fas fa-eye me-2 text-primary"></i>
                                                            <span class="text-primary fw-medium">Ver detalles</span>
                                                        </button>
                                                    </li>

                                                    @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                        {{-- Editar --}}
                                                        <li>
                                                            <a href="{{ route('representante.editar', $rep->id) }}"
                                                                class="dropdown-item dropdown-item-modern text-warning d-flex align-items-center"
                                                                title="Editar">
                                                                <i class="fas fa-pen me-2 text-warning"></i>
                                                                <span class="text-warning fw-medium">Editar</span>
                                                            </a>
                                                        </li>

                                                        {{-- Inactivar / Eliminar --}}
                                                        <li>
                                                            <button class="dropdown-item dropdown-item-modern item-delete text-danger d-flex align-items-center"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#confirmarEliminarRepresentante{{ $rep->id }}"
                                                                title="Eliminar">
                                                                <i class="fas fa-trash me-2 text-danger"></i>
                                                                <span class="text-danger fw-medium">Eliminar</span>
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
                                                <div class="modal-content modal-modern shadow-lg" style="border-radius: 16px; border: none; overflow: hidden;">
                                                    <div class="modal-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: #FFFFFF;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                 style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                                                <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title m-0 fw-bold" id="modalLabelEliminarRep{{ $rep->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                                                    Confirmar Eliminación
                                                                </h5>
                                                                <p class="text-muted m-0" style="font-size: 0.82rem;">Acción sobre registro de representante</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <form action="{{ route('representante.destroy', $rep->id) }}" method="POST" class="w-100 m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                                            <p class="fw-semibold mb-2" style="color: #1E293B; font-size: 0.95rem;">
                                                                ¿Deseas eliminar este representante?
                                                            </p>
                                                            <div class="p-3 d-flex align-items-center gap-3"
                                                                 style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                                                     style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700;">
                                                                    <i class="fas fa-user-friends"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold" style="color: #1E293B; font-size: 0.92rem;">
                                                                        {{ $rep->persona->primer_nombre ?? '' }} {{ $rep->persona->primer_apellido ?? '' }}
                                                                    </div>
                                                                    <div class="text-muted" style="font-size: 0.82rem;">
                                                                        Cédula: {{ $rep->persona->tipoDocumento->nombre ?? 'V' }}-{{ $rep->persona->numero_documento ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mt-3 p-2 rounded-2 d-flex align-items-start gap-2" style="background-color: #FFFBEB; border: 1px solid #FEF3C7;">
                                                                <i class="fas fa-info-circle text-warning mt-1" style="font-size: 0.85rem;"></i>
                                                                <span class="text-muted" style="font-size: 0.82rem; line-height: 1.4;">
                                                                    Esta acción es un borrado suave: el registro no se eliminará físicamente, pero dejará de aparecer en los listados activos.
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer px-4 py-3 border-top d-flex justify-content-end gap-2" style="background-color: #F8FAFC;">
                                                            <button type="button" class="btn btn-secondary-modern" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                                                                Cancelar
                                                            </button>
                                                            <button type="submit" class="btn" style="background-color: #EF4444; color: #FFF; border-radius: 8px; font-weight: 600; padding: 8px 16px; border: none;">
                                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-user-friends fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay representantes registrados</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Agrega un nuevo representante con el botón superior.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer-modern bg-white px-4 py-3 border-top">
                <div class="d-flex justify-content-end">
                    <x-pagination :paginator="$representantes" />
                </div>
            </div>
        </div>

        {{-- Modal de detalles del representante --}}
        @include('admin.representante.modales.showModal')

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
                    tipoBadge.className = 'badge-pill-modern';
                    tipoBadge.style = 'background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE;';
                } else {
                    // Es progenitor
                    tipoBadge.textContent = 'Progenitor';
                    tipoBadge.className = 'badge-pill-modern';
                    tipoBadge.style = 'background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;';
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

                // Correo electrónico
                if (document.getElementById('modal-correo')) {
                    const correoItem = document.getElementById('correo-detail-item');
                    const correoVal = (legal && legal.correo_representante) ? legal.correo_representante : (persona.email || 'No registrado');
                    document.getElementById('modal-correo').textContent = correoVal;
                    if (correoItem) correoItem.style.display = '';
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
                    conviveElement.className = 'badge-pill-modern ' + (conviveValue ? 'badge-pill-yes' : 'badge-pill-no');
                } else {
                    conviveElement.textContent = 'No especificado';
                    conviveElement.className = 'badge-pill-modern badge-pill-no';
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
                        let texto = 'No afiliado';
                        carnetEl.className = 'badge-pill-modern';
                        
                        if (tieneCarnet === 1) {
                            texto = 'Madre';
                            carnetEl.style = 'background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE;';
                        } else if (tieneCarnet === 2) {
                            texto = 'Padre';
                            carnetEl.style = 'background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE;';
                        } else if (tieneCarnet === 3) {
                            texto = 'Otro';
                            carnetEl.style = 'background-color: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;';
                        } else {
                            carnetEl.style = 'background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;';
                        }
                        
                        carnetEl.textContent = texto;
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
                            perteneceOrgEl.className = 'badge-pill-modern badge-pill-yes';
                            if (campoOrg) campoOrg.style.display = '';
                            document.getElementById('modal-org-pertenece').textContent = legal
                                .cual_organizacion_representante || 'No especificada';
                        } else {
                            perteneceOrgEl.textContent = 'No';
                            perteneceOrgEl.className = 'badge-pill-modern badge-pill-no';
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

                    // Asegurar que el campo de correo muestre el correo de persona o 'No registrado'
                    const correoItem = document.getElementById('correo-detail-item');
                    if (correoItem) {
                        correoItem.style.display = '';
                        document.getElementById('modal-correo').textContent = persona.email || 'No registrado';
                    }
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
