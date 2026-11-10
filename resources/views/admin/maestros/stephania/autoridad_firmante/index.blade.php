@extends('adminlte::page')

{{ Breadcrumbs::render('admin.autoridad-firmante.index') }}

@section('title', 'Autoridades Firmantes')

@section('content_header')
    <div class="content-header-modern">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-signature"></i>
                </div>
                <div>
                    <h1 class="title-main">Autoridades Firmantes</h1>
                    <p class="title-subtitle">Registro y vigencia de directivos, coordinadores y docentes firmantes para los documentos oficiales</p>
                </div>
            </div>

            @can('autoridad_firmante.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-create" data-bs-toggle="modal" data-bs-target="#modalCrearAutoridad">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Autoridad</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <style>
        .badge-cargo {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .cargo-director {
            background: rgba(91, 110, 225, 0.12);
            color: #4338ca;
            border: 1px solid rgba(91, 110, 225, 0.3);
        }
        .cargo-subdirector_academico {
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }
        .cargo-coordinador_control_estudios {
            background: rgba(20, 184, 166, 0.12);
            color: #0f766e;
            border: 1px solid rgba(20, 184, 166, 0.3);
        }
        .cargo-coordinador_evaluacion {
            background: rgba(168, 85, 247, 0.12);
            color: #7e22ce;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        .cargo-docente_firmante_mppe {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .badge-condicion-titular {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.78rem;
            border: 1px solid #cbd5e1;
        }
        .badge-condicion-encargado {
            background: #fffbeb;
            color: #b45309;
            font-weight: 600;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.78rem;
            border: 1px solid #fde68a;
        }
        .vigencia-box {
            display: inline-flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('content')
    <div class="main-container">

        @if (!\App\Support\AnioEscolarSeleccionado::esActual())
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="border-left: 4px solid #17a2b8;">
                <i class="fas fa-info-circle mr-2 me-2"></i>
                Estás viendo un año escolar anterior en modo <strong>Solo Consulta</strong>. No puedes crear, editar ni eliminar registros.
            </div>
        @endif

        @if (session('success') || session('error'))
            <div class="alerts-container">
                @if (session('success'))
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Éxito</h4>
                            <p>{{ session('success') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert-modern alert-error alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Error</h4>
                            <p>{{ session('error') }}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <div class="card-modern">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-signature"></i>
                    </div>
                    <div>
                        <h3>Autoridades Asignadas</h3>
                        <p>
                            {{ $autoridades->total() }} autoridades registradas para el año escolar 
                            <strong class="text-primary">{{ $anioModelo?->nombre_completo ?? 'Activo' }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th>Cargo</th>
                                <th>Persona (Funcionario)</th>
                                <th style="text-align: center;">Condición</th>
                                <th>Gaceta / Resolución</th>
                                <th style="text-align: center;">Vigencia</th>
                                <th style="width: 100px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($autoridades as $index => $aut)
                                <tr>
                                    <td style="text-align: center;">{{ $autoridades->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge-cargo cargo-{{ $aut->cargo }}">
                                            <i class="fas fa-user-check"></i>
                                            {{ $aut->cargo_nombre }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold fw-bold text-dark" style="font-size: 0.95rem;">
                                            {{ $aut->persona?->nombre_completo ?? 'Persona no encontrada' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $aut->persona?->tipoDocumento?->abreviatura ?? 'CI' }}: {{ $aut->persona?->numero_documento ?? '-' }}
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($aut->encargado)
                                            <span class="badge-condicion-encargado" title="Funcionario en calidad de encargado">
                                                <i class="fas fa-user-clock mr-1 me-1"></i>Encargado(a)
                                            </span>
                                        @else
                                            <span class="badge-condicion-titular" title="Funcionario titular del cargo">
                                                <i class="fas fa-check-circle mr-1 me-1"></i>Titular
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($aut->gaceta)
                                            <span class="text-dark font-weight-bold fw-bold" style="font-size: 0.9rem;">
                                                <i class="fas fa-file-alt text-primary mr-1 me-1"></i>{{ $aut->gaceta }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-style: italic;">No especificada</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="vigencia-box">
                                            <span><strong>Desde:</strong> {{ \Carbon\Carbon::parse($aut->desde)->format('d/m/Y') }}</span>
                                            <span>
                                                <strong>Hasta:</strong> 
                                                @if ($aut->hasta)
                                                    {{ \Carbon\Carbon::parse($aut->hasta)->format('d/m/Y') }}
                                                @else
                                                    <span class="text-success font-weight-bold fw-bold">Indefinido</span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown dropstart text-center">
                                                <button class="btn btn-light btn-sm rounded-circle shadow-sm action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    @can('autoridad_firmante.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-warning"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditarAutoridad{{ $aut->id }}"
                                                                        title="Editar">
                                                                    <i class="fas fa-edit me-2 mr-2"></i>
                                                                    Editar
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item d-flex align-items-center text-danger"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $aut->id }}"
                                                                        title="Eliminar">
                                                                    <i class="fas fa-trash me-2 mr-2"></i>
                                                                    Eliminar
                                                                </button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <span class="dropdown-item text-muted disabled">Solo consulta</span>
                                                            </li>
                                                        @endif
                                                    @else
                                                        <li>
                                                            <span class="dropdown-item text-muted disabled">Sin permisos</span>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-signature"></i>
                                            </div>
                                            <h4>No hay autoridades firmantes registradas</h4>
                                            <p>Asigna al Director, Subdirector, Coordinadores o Docentes firmantes para este año escolar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$autoridades" />

        {{-- Modales de Edición y Eliminación fuera de la tabla --}}
        @foreach ($autoridades as $aut)
            @include('admin.maestros.stephania.autoridad_firmante.modales.editModal', ['aut' => $aut])

            {{-- Modal Confirmar Eliminación --}}
            <div class="modal fade" id="confirmarEliminar{{ $aut->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header-delete py-3 px-4 d-flex align-items-center">
                            <div class="modal-icon-delete" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin: 0;">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <div class="ml-3 me-auto">
                                <h5 class="modal-title-delete mb-0" style="font-size: 1.15rem; font-weight: 700;">Confirmar Eliminación</h5>
                                <small class="text-muted" style="font-size: 0.8rem;">Esta acción no se puede deshacer</small>
                            </div>
                            <button type="button" class="btn-close-modal ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body-delete p-4">
                            <p>¿Deseas remover a <strong>{{ $aut->persona?->nombre_completo }}</strong> como <strong>{{ $aut->cargo_nombre }}</strong>?</p>
                            <p class="delete-warning mb-0">La autoridad dejará de constar como firmante para este año escolar.</p>
                        </div>
                        <div class="modal-footer-delete py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb; gap: 0.75rem;">
                            <form action="{{ route('admin.autoridad-firmante.destroy', $aut) }}" method="POST" class="w-100 d-flex justify-content-end gap-2">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancelar</button>
                                <button type="submit" class="btn btn-danger btn-modal-delete px-4 py-2" style="border-radius: 8px; font-weight: 600;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Modal Crear --}}
        @include('admin.maestros.stephania.autoridad_firmante.modales.createModal')

    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Soporte para mensaje flash de éxito tras reload de formulario AJAX
        const flashMsg = sessionStorage.getItem('flash_success');
        if (flashMsg) {
            sessionStorage.removeItem('flash_success');
            if (!document.querySelector('.alerts-container .alert-modern.alert-success')) {
                let alertsContainer = document.querySelector('.alerts-container');
                if (!alertsContainer) {
                    alertsContainer = document.createElement('div');
                    alertsContainer.className = 'alerts-container';
                    const mainContainer = document.querySelector('.main-container');
                    if (mainContainer) {
                        mainContainer.insertBefore(alertsContainer, mainContainer.firstChild);
                    }
                }
                const alertHtml = `
                    <div class="alert-modern alert-success alert alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h4>Éxito</h4>
                            <p>${flashMsg}</p>
                        </div>
                        <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                alertsContainer.insertAdjacentHTML('afterbegin', alertHtml);
            }
        }

        const catalogoPersonas = @json($personasCatalogo ?? []);

        // Obtener token CSRF de la página o del formulario
        function obtenerCsrfToken(form) {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || form?.querySelector('input[name="_token"]')?.value
                || '';
        }

        // Función para mostrar alertas de error dentro del modal
        function mostrarAlertaErrorModal(form, mensajeHtml) {
            let alertBox = form.querySelector('.modal-error-banner');
            if (!alertBox) {
                alertBox = document.createElement('div');
                alertBox.className = 'alert alert-danger modal-error-banner alert-dismissible fade show mb-3';
                alertBox.style.borderRadius = '10px';
                alertBox.style.fontSize = '0.9rem';
                const body = form.querySelector('.modal-body-create') || form;
                body.insertBefore(alertBox, body.firstChild);
            }
            alertBox.innerHTML = `<i class="fas fa-exclamation-triangle mr-2 me-2"></i> ${mensajeHtml}`;
            alertBox.style.display = 'block';
        }

        // Marcar un input con error visual y mensaje
        function marcarErrorCampo(input, mensaje) {
            if (!input) return;
            input.classList.add('is-invalid');
            let parent = input.parentElement;
            let feedback = parent.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback error-message';
                parent.appendChild(feedback);
            }
            feedback.textContent = mensaje;
            feedback.style.display = 'block';
        }

        // Limpiar el estado de error de un input
        function limpiarErrorCampo(input) {
            if (!input) return;
            input.classList.remove('is-invalid');
            let parent = input.parentElement;
            let feedback = parent.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
                feedback.style.display = '';
            }
            let serverError = parent.querySelector('.server-error');
            if (serverError) {
                serverError.remove();
            }
        }

        // Función para limpiar errores en un formulario
        function limpiarErroresFormulario(form) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
                el.style.display = '';
            });
            form.querySelectorAll('.server-error').forEach(el => el.remove());
            form.querySelectorAll('.modal-error-banner').forEach(el => el.remove());
        }

        // Función para pintar errores 422 devueltos por Laravel
        function mostrarErrores(form, errors) {
            limpiarErroresFormulario(form);

            let erroresSinInput = [];

            for (const [campo, mensajes] of Object.entries(errors)) {
                const input = form.querySelector(`[name="${campo}"]`);
                if (input) {
                    marcarErrorCampo(input, mensajes[0]);
                } else {
                    erroresSinInput.push(mensajes[0]);
                }
            }

            if (erroresSinInput.length > 0) {
                mostrarAlertaErrorModal(form, erroresSinInput.join('<br>'));
            }
        }

        // Función para filtrar el select de Personas según el Cargo
        function actualizarSelectPersonas(selectPersona, cargo, valorPreseleccionado = '') {
            if (!selectPersona) return;

            const valorActual = valorPreseleccionado || selectPersona.value;
            selectPersona.innerHTML = '<option value="" disabled selected>-- Seleccione la Persona --</option>';

            let filtradas = catalogoPersonas;

            if (cargo === 'director' || cargo === 'subdirector_academico' || cargo === 'coordinador_control_estudios' || cargo === 'coordinador_evaluacion') {
                filtradas = catalogoPersonas.filter(p => p.es_docente || p.es_administrativo || String(p.id) === String(valorActual));
            } else if (cargo === 'docente_firmante_mppe') {
                filtradas = catalogoPersonas.filter(p => p.es_docente || String(p.id) === String(valorActual));
            }

            filtradas.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.texto;
                if (String(p.id) === String(valorActual)) {
                    opt.selected = true;
                }
                selectPersona.appendChild(opt);
            });
        }

        // Configurar filtros dinámicos en el modal de creación
        const selectCargoCrear = document.getElementById('cargo');
        const selectPersonaCrear = document.getElementById('persona_id');
        if (selectCargoCrear && selectPersonaCrear) {
            selectCargoCrear.addEventListener('change', function () {
                actualizarSelectPersonas(selectPersonaCrear, this.value);
            });
            if (selectCargoCrear.value) {
                actualizarSelectPersonas(selectPersonaCrear, selectCargoCrear.value, selectPersonaCrear.value);
            }
        }

        // Configurar filtros dinámicos en los modales de edición
        document.querySelectorAll('[id^="cargo_edit_"]').forEach(selectCargoEdit => {
            const idSufijo = selectCargoEdit.id.replace('cargo_edit_', '');
            const selectPersonaEdit = document.getElementById(`persona_id_edit_${idSufijo}`);
            if (selectPersonaEdit) {
                selectCargoEdit.addEventListener('change', function () {
                    actualizarSelectPersonas(selectPersonaEdit, this.value, selectPersonaEdit.value);
                });
                if (selectCargoEdit.value) {
                    actualizarSelectPersonas(selectPersonaEdit, selectCargoEdit.value, selectPersonaEdit.value);
                }
            }
        });

        // Validación del lado del cliente antes del submit
        function validarFormularioCliente(form) {
            let valido = true;
            let primerError = null;

            const inputCargo = form.querySelector('[name="cargo"]');
            const inputPersona = form.querySelector('[name="persona_id"]');
            const inputDesde = form.querySelector('[name="desde"]');
            const inputHasta = form.querySelector('[name="hasta"]');
            const inputGaceta = form.querySelector('[name="gaceta"]');

            // 1. Cargo
            if (inputCargo) {
                if (!inputCargo.value) {
                    marcarErrorCampo(inputCargo, 'El campo Cargo es obligatorio.');
                    valido = false;
                    if (!primerError) primerError = inputCargo;
                } else {
                    limpiarErrorCampo(inputCargo);
                }
            }

            // 2. Persona
            if (inputPersona) {
                if (!inputPersona.value) {
                    marcarErrorCampo(inputPersona, 'El campo Persona es obligatorio.');
                    valido = false;
                    if (!primerError) primerError = inputPersona;
                } else {
                    limpiarErrorCampo(inputPersona);
                }
            }

            // 3. Desde
            let desdeVal = '';
            if (inputDesde) {
                desdeVal = inputDesde.value.trim();
                if (!desdeVal) {
                    marcarErrorCampo(inputDesde, 'El campo Vigencia Desde es obligatorio.');
                    valido = false;
                    if (!primerError) primerError = inputDesde;
                } else {
                    limpiarErrorCampo(inputDesde);
                }
            }

            // 4. Hasta (opcional, pero si existe no puede ser menor a Desde)
            if (inputHasta) {
                const hastaVal = inputHasta.value.trim();
                if (hastaVal && desdeVal && hastaVal < desdeVal) {
                    marcarErrorCampo(inputHasta, 'El campo Vigencia Hasta debe ser una fecha posterior o igual a Vigencia Desde.');
                    valido = false;
                    if (!primerError) primerError = inputHasta;
                } else {
                    limpiarErrorCampo(inputHasta);
                }
            }

            // 5. Gaceta (opcional, max 100)
            if (inputGaceta && inputGaceta.value.length > 100) {
                marcarErrorCampo(inputGaceta, 'El campo Gaceta / Resolución no debe exceder los 100 caracteres.');
                valido = false;
                if (!primerError) primerError = inputGaceta;
            } else if (inputGaceta) {
                limpiarErrorCampo(inputGaceta);
            }

            if (primerError) {
                primerError.focus();
            }

            return valido;
        }

        // Validación en tiempo real (eventos input, change, blur)
        function adjuntarValidacionTiempoReal(form) {
            const inputCargo = form.querySelector('[name="cargo"]');
            const inputPersona = form.querySelector('[name="persona_id"]');
            const inputDesde = form.querySelector('[name="desde"]');
            const inputHasta = form.querySelector('[name="hasta"]');
            const inputGaceta = form.querySelector('[name="gaceta"]');

            if (inputCargo) {
                const valCargo = () => {
                    if (!inputCargo.value) marcarErrorCampo(inputCargo, 'El campo Cargo es obligatorio.');
                    else limpiarErrorCampo(inputCargo);
                };
                inputCargo.addEventListener('change', valCargo);
                inputCargo.addEventListener('blur', valCargo);
            }

            if (inputPersona) {
                const valPersona = () => {
                    if (!inputPersona.value) marcarErrorCampo(inputPersona, 'El campo Persona es obligatorio.');
                    else limpiarErrorCampo(inputPersona);
                };
                inputPersona.addEventListener('change', valPersona);
                inputPersona.addEventListener('blur', valPersona);
            }

            function validarFechas() {
                const desdeVal = inputDesde?.value ?? '';
                const hastaVal = inputHasta?.value ?? '';

                if (inputDesde) {
                    if (!desdeVal) marcarErrorCampo(inputDesde, 'El campo Vigencia Desde es obligatorio.');
                    else limpiarErrorCampo(inputDesde);
                }

                if (inputHasta) {
                    if (hastaVal && desdeVal && hastaVal < desdeVal) {
                        marcarErrorCampo(inputHasta, 'El campo Vigencia Hasta debe ser una fecha posterior o igual a Vigencia Desde.');
                    } else {
                        limpiarErrorCampo(inputHasta);
                    }
                }
            }

            if (inputDesde) {
                inputDesde.addEventListener('input', validarFechas);
                inputDesde.addEventListener('change', validarFechas);
                inputDesde.addEventListener('blur', validarFechas);
            }
            if (inputHasta) {
                inputHasta.addEventListener('input', validarFechas);
                inputHasta.addEventListener('change', validarFechas);
                inputHasta.addEventListener('blur', validarFechas);
            }

            if (inputGaceta) {
                const valGaceta = () => {
                    if (inputGaceta.value.length > 100) {
                        marcarErrorCampo(inputGaceta, 'El campo Gaceta / Resolución no debe exceder los 100 caracteres.');
                    } else {
                        limpiarErrorCampo(inputGaceta);
                    }
                };
                inputGaceta.addEventListener('input', valGaceta);
                inputGaceta.addEventListener('blur', valGaceta);
            }
        }

        // Manejador AJAX común para creación y edición
        function manejarEnvioAjax(form) {
            adjuntarValidacionTiempoReal(form);

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                // 1. Validación en tiempo real del lado del cliente
                if (!validarFormularioCliente(form)) {
                    return;
                }

                const btnSubmit = form.querySelector('button[type="submit"]');
                const btnOriginalHtml = btnSubmit ? btnSubmit.innerHTML : '';
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1 me-1"></i> Guardando...';
                }

                try {
                    const formData = new FormData(form);
                    const csrfToken = obtenerCsrfToken(form);

                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            mostrarErrores(form, data.errors);
                        } else {
                            const errorMsg = data.message || 'Ocurrió un error inesperado al procesar la solicitud.';
                            mostrarAlertaErrorModal(form, errorMsg);
                        }
                        return;
                    }

                    // Éxito: cerrar modal y recargar mostrando alerta institucional
                    const modalEl = form.closest('.modal');
                    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modalInstance.hide();
                    }

                    const mensajeExito = data.message || 'Autoridad firmante guardada exitosamente.';
                    sessionStorage.setItem('flash_success', mensajeExito);
                    window.location.reload();

                } catch (error) {
                    console.error('Error en envío AJAX de Autoridad:', error);
                    mostrarAlertaErrorModal(form, 'Error de conexión: No se pudo conectar con el servidor.');
                } finally {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = btnOriginalHtml;
                    }
                }
            });
        }

        // Aplicar a formulario de creación
        const formCrear = document.getElementById('formCrearAutoridad');
        if (formCrear) {
            manejarEnvioAjax(formCrear);
        }

        // Aplicar a formularios de edición
        document.querySelectorAll('form[id^="formEditarAutoridad"]').forEach(form => {
            manejarEnvioAjax(form);
        });

        // Limpieza y reset al abrir el modal de Crear
        const modalCrear = document.getElementById('modalCrearAutoridad');
        if (modalCrear) {
            modalCrear.addEventListener('show.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    limpiarErroresFormulario(form);
                    const inputDesde = form.querySelector('[name="desde"]');
                    if (inputDesde) inputDesde.value = new Date().toISOString().slice(0, 10);
                    const selectCargo = form.querySelector('[name="cargo"]');
                    const selectPersona = form.querySelector('[name="persona_id"]');
                    if (selectCargo && selectPersona) {
                        selectCargo.value = '';
                        actualizarSelectPersonas(selectPersona, '');
                    }
                }
            });
        }

        // Limpiar errores cuando se oculta cualquier modal
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) limpiarErroresFormulario(form);
            });
        });
    });
</script>
@stop

