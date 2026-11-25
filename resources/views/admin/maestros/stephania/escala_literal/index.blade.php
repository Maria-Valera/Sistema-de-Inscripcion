@extends('adminlte::page')

{{ Breadcrumbs::render('admin.escala-literal.index') }}

@section('title', 'Escala Literal')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal-styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ @filemtime(public_path('css/design-system.css')) }}">
@stop

@section('content_header')
    <div class="content-header-modern hero-header-horario">
        <div class="header-content">
            <div class="header-title">
                <div class="icon-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="title-main">Escala Literal</h1>
                    <p class="title-subtitle">Equivalencia de calificaciones numéricas (1 a 20) a literales para el año escolar</p>
                </div>
            </div>

            @can('escala_literal.gestionar')
                @if (\App\Support\AnioEscolarSeleccionado::esActual())
                    <button type="button" class="btn-hero-white" data-bs-toggle="modal" data-bs-target="#modalCrearEscala">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Escala</span>
                    </button>
                @endif
            @endcan
        </div>
    </div>
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

        <div class="card-modern shadow-sm">
            <div class="card-header-modern d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-list-ol"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Escalas Configuradas</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">
                            {{ $escalas->total() }} escalas para el periodo 
                            <strong class="text-primary">{{ $anioModelo?->nombre_completo ?? 'Activo' }}</strong>
                        </p>
                    </div>
                </div>

                {{-- Buscador Redondeado (Design System) --}}
                <div class="header-right">
                    <div class="search-wrapper position-relative" style="width: 260px;">
                        <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; font-size: 0.85rem;"></i>
                        <input type="text"
                               id="buscarEscala"
                               class="form-control search-input-modern pl-5"
                               placeholder="Buscar escala..."
                               style="padding-left: 40px !important; border-radius: 10px; height: 38px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            <div class="card-body-modern p-0">
                <div class="table-wrapper">
                    <table class="table-modern" id="tablaEscalas">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 60px;">#</th>
                                <th style="width: 100px;">Letra</th>
                                <th class="text-start">Descripción Cualitativa</th>
                                <th>Rango Numérico (Puntos)</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($escalas as $index => $esc)
                                <tr>
                                    <td class="text-muted" style="font-size: 0.85rem;">{{ $escalas->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge badge-primary px-3 py-2 fw-bold" style="font-size: 0.95rem; min-width: 36px;">
                                            {{ $esc->letra }}
                                        </span>
                                    </td>
                                    <td class="text-start">
                                        <div class="fw-bold text-dark" style="font-size: 0.92rem;">
                                            {{ $esc->descripcion ?: 'Sin descripción' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #F8FAFC; color: #334155; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 8px; font-size: 0.85rem; border: 1px solid #E2E8F0;">
                                            <i class="fas fa-arrows-alt-h text-primary me-1"></i>
                                            <strong>{{ $esc->nota_desde }}</strong> a <strong>{{ $esc->nota_hasta }}</strong> pts
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons justify-content-center">
                                            <div class="dropdown text-center">
                                                <button class="action-btn"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Acciones">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-modern">
                                                    @can('escala_literal.gestionar')
                                                        @if (\App\Support\AnioEscolarSeleccionado::esActual())
                                                            <li>
                                                                <button class="dropdown-item dropdown-item-modern item-edit"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditarEscala{{ $esc->id }}"
                                                                        title="Editar">
                                                                    <i class="fas fa-edit me-2"></i>
                                                                    <span>Editar</span>
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button class="dropdown-item dropdown-item-modern item-delete"
                                                                        type="button"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmarEliminar{{ $esc->id }}"
                                                                        title="Eliminar">
                                                                    <i class="fas fa-trash-alt me-2"></i>
                                                                    <span>Eliminar</span>
                                                                </button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <span class="dropdown-item dropdown-item-modern text-muted disabled">Solo consulta</span>
                                                            </li>
                                                        @endif
                                                    @else
                                                        <li>
                                                            <span class="dropdown-item dropdown-item-modern text-muted disabled">Sin permisos</span>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon text-muted mb-2 opacity-50">
                                                <i class="fas fa-sort-alpha-down fa-3x"></i>
                                            </div>
                                            <h4 class="fw-bold" style="color: #1E293B; font-size: 1.1rem;">No hay escalas literales configuradas</h4>
                                            <p class="text-muted" style="font-size: 0.88rem;">Agrega los rangos alfabéticos (ej. A, B, C, D) para el año escolar actual.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-pagination :paginator="$escalas" />

        {{-- Modales de Edición y Eliminación fuera de la tabla --}}
        @foreach ($escalas as $esc)
            @include('admin.maestros.stephania.escala_literal.modales.editModal', ['escala' => $esc])

            {{-- Modal Confirmar Eliminación con Design System (Acento Rojo de Advertencia) --}}
            <div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $esc->id }}" tabindex="-1" aria-labelledby="confirmarEliminarLabel{{ $esc->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                    <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
                        <!-- Modal Header Limpio -->
                        <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                                    <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title m-0 fw-bold" id="confirmarEliminarLabel{{ $esc->id }}" style="color: #1E293B; font-size: 1.1rem;">
                                        Eliminar Escala Literal
                                    </h5>
                                    <p class="text-muted m-0" style="font-size: 0.82rem;">Confirmación de acción destructiva</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <!-- Formulario y Body de Advertencia -->
                        <form action="{{ route('admin.escala-literal.destroy', $esc) }}" method="POST" class="form-eliminar-escala m-0">
                            @csrf
                            @method('DELETE')

                            <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                                <p class="fw-semibold mb-3" style="color: #1E293B; font-size: 0.95rem;">
                                    ¿Estás seguro de que deseas eliminar esta escala literal?
                                </p>

                                <!-- Recuadro Gris de Resumen -->
                                <div class="p-3 mb-3 d-flex align-items-center gap-3"
                                     style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                         style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1; font-weight: 700; font-size: 1.1rem;">
                                        {{ $esc->letra }}
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.92rem;">
                                            {{ $esc->descripcion ?: 'Sin descripción' }}
                                        </div>
                                        <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                            <i class="fas fa-arrows-alt-h text-secondary"></i>
                                            <span>Rango: {{ $esc->nota_desde }} a {{ $esc->nota_hasta }} puntos</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advertencia en texto rojo suave -->
                                <div class="d-flex align-items-start gap-2 p-3 rounded-3"
                                     style="background-color: #FEF2F2; border: 1px solid #FEE2E2; border-left: 4px solid #EF4444;">
                                    <i class="fas fa-exclamation-triangle flex-shrink-0 mt-1" style="color: #DC2626; font-size: 0.85rem;"></i>
                                    <p class="m-0" style="color: #DC2626; font-size: 13px; line-height: 1.45; font-weight: 500;">
                                        Esta acción no se puede deshacer. La escala será removida de la configuración del año escolar actual.
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer Alineado a la Derecha -->
                            <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>
                                    <span>Cancelar</span>
                                </button>
                                <button type="submit" class="btn-danger-modern d-inline-flex align-items-center gap-2">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Sí, eliminar escala</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Modal Crear --}}
        @include('admin.maestros.stephania.escala_literal.modales.createModal')

    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Filtrado en tiempo real desde el input de búsqueda
        const inputBuscar = document.getElementById('buscarEscala');
        if (inputBuscar) {
            inputBuscar.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                document.querySelectorAll('#tablaEscalas tbody tr').forEach(row => {
                    if (row.cells.length === 1) return; // Fila de estado vacío
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }

        // Feedback visual al confirmar eliminación
        document.querySelectorAll('.form-eliminar-escala').forEach(form => {
            form.addEventListener('submit', function () {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><span>Eliminando...</span>';
                }
            });
        });

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
                const body = form.querySelector('.modal-body') || form;
                body.insertBefore(alertBox, body.firstChild);
            }
            alertBox.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${mensajeHtml}`;
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

        // Función utilitaria para limpiar todos los errores en un formulario
        function limpiarErroresFormulario(form) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
                el.style.display = '';
            });
            form.querySelectorAll('.server-error').forEach(el => el.remove());
            form.querySelectorAll('.modal-error-banner').forEach(el => el.remove());
        }

        // Función para mostrar errores 422 del backend en el formulario
        function mostrarErrores(form, errors) {
            limpiarErroresFormulario(form);

            let erroresSinInput = [];

            for (const [campo, mensajes] of Object.entries(errors)) {
                let input = form.querySelector(`[name="${campo}"]`);

                // Manejo si el backend retorna error en 'rango'
                if (!input && campo === 'rango') {
                    const inputHasta = form.querySelector('[name="nota_hasta"]');
                    const inputDesde = form.querySelector('[name="nota_desde"]');
                    if (inputHasta) marcarErrorCampo(inputHasta, mensajes[0]);
                    if (inputDesde) marcarErrorCampo(inputDesde, mensajes[0]);
                    continue;
                }

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

        // Validar campos en el cliente (retorna true si es válido)
        function validarFormularioCliente(form) {
            let valido = true;
            let primerError = null;

            const inputLetra = form.querySelector('[name="letra"]');
            const inputDesde = form.querySelector('[name="nota_desde"]');
            const inputHasta = form.querySelector('[name="nota_hasta"]');
            const inputDesc = form.querySelector('[name="descripcion"]');

            // 1. Letra
            if (inputLetra) {
                const letraVal = inputLetra.value.trim().toUpperCase();
                inputLetra.value = letraVal;
                if (!letraVal) {
                    marcarErrorCampo(inputLetra, 'La letra de la escala es obligatoria.');
                    valido = false;
                    if (!primerError) primerError = inputLetra;
                } else if (letraVal.length > 1) {
                    marcarErrorCampo(inputLetra, 'La letra debe ser un único carácter (ej. A, B, C, D).');
                    valido = false;
                    if (!primerError) primerError = inputLetra;
                } else {
                    limpiarErrorCampo(inputLetra);
                }
            }

            // 2. Nota Desde
            let desdeNum = NaN;
            if (inputDesde) {
                const rawDesde = inputDesde.value.trim();
                desdeNum = parseInt(rawDesde, 10);
                if (rawDesde === '' || isNaN(desdeNum)) {
                    marcarErrorCampo(inputDesde, 'El campo Nota Desde es obligatorio.');
                    valido = false;
                    if (!primerError) primerError = inputDesde;
                } else if (desdeNum < 1 || desdeNum > 20) {
                    marcarErrorCampo(inputDesde, 'El campo Nota Desde debe estar entre 1 y 20.');
                    valido = false;
                    if (!primerError) primerError = inputDesde;
                } else {
                    limpiarErrorCampo(inputDesde);
                }
            }

            // 3. Nota Hasta
            if (inputHasta) {
                const rawHasta = inputHasta.value.trim();
                const hastaNum = parseInt(rawHasta, 10);
                if (rawHasta === '' || isNaN(hastaNum)) {
                    marcarErrorCampo(inputHasta, 'El campo Nota Hasta es obligatorio.');
                    valido = false;
                    if (!primerError) primerError = inputHasta;
                } else if (hastaNum < 1 || hastaNum > 20) {
                    marcarErrorCampo(inputHasta, 'El campo Nota Hasta debe estar entre 1 y 20.');
                    valido = false;
                    if (!primerError) primerError = inputHasta;
                } else if (!isNaN(desdeNum) && hastaNum < desdeNum) {
                    marcarErrorCampo(inputHasta, 'El campo Nota Hasta debe ser mayor o igual que Nota Desde.');
                    valido = false;
                    if (!primerError) primerError = inputHasta;
                } else {
                    limpiarErrorCampo(inputHasta);
                }
            }

            // 4. Descripción (opcional, max 60)
            if (inputDesc && inputDesc.value.length > 60) {
                marcarErrorCampo(inputDesc, 'La descripción no debe exceder los 60 caracteres.');
                valido = false;
                if (!primerError) primerError = inputDesc;
            } else if (inputDesc) {
                limpiarErrorCampo(inputDesc);
            }

            if (primerError) {
                primerError.focus();
            }

            return valido;
        }

        // Adjuntar validación en tiempo real (eventos input y blur)
        function adjuntarValidacionTiempoReal(form) {
            const inputLetra = form.querySelector('[name="letra"]');
            const inputDesde = form.querySelector('[name="nota_desde"]');
            const inputHasta = form.querySelector('[name="nota_hasta"]');
            const inputDesc = form.querySelector('[name="descripcion"]');

            if (inputLetra) {
                const validarLetra = () => {
                    inputLetra.value = inputLetra.value.toUpperCase();
                    const val = inputLetra.value.trim();
                    if (!val) {
                        marcarErrorCampo(inputLetra, 'La letra de la escala es obligatoria.');
                    } else if (val.length > 1) {
                        marcarErrorCampo(inputLetra, 'La letra debe ser un único carácter (ej. A, B, C, D).');
                    } else {
                        limpiarErrorCampo(inputLetra);
                    }
                };
                inputLetra.addEventListener('input', validarLetra);
                inputLetra.addEventListener('blur', validarLetra);
            }

            function validarRangoNotas() {
                const desdeRaw = inputDesde?.value.trim() ?? '';
                const hastaRaw = inputHasta?.value.trim() ?? '';
                const desdeVal = parseInt(desdeRaw, 10);
                const hastaVal = parseInt(hastaRaw, 10);

                if (inputDesde) {
                    if (desdeRaw === '' || isNaN(desdeVal)) {
                        marcarErrorCampo(inputDesde, 'El campo Nota Desde es obligatorio.');
                    } else if (desdeVal < 1 || desdeVal > 20) {
                        marcarErrorCampo(inputDesde, 'El campo Nota Desde debe estar entre 1 y 20.');
                    } else {
                        limpiarErrorCampo(inputDesde);
                    }
                }

                if (inputHasta) {
                    if (hastaRaw === '' || isNaN(hastaVal)) {
                        marcarErrorCampo(inputHasta, 'El campo Nota Hasta es obligatorio.');
                    } else if (hastaVal < 1 || hastaVal > 20) {
                        marcarErrorCampo(inputHasta, 'El campo Nota Hasta debe estar entre 1 y 20.');
                    } else if (!isNaN(desdeVal) && hastaVal < desdeVal) {
                        marcarErrorCampo(inputHasta, 'El campo Nota Hasta debe ser mayor o igual que Nota Desde.');
                    } else {
                        limpiarErrorCampo(inputHasta);
                    }
                }
            }

            if (inputDesde) {
                inputDesde.addEventListener('input', validarRangoNotas);
                inputDesde.addEventListener('blur', validarRangoNotas);
            }
            if (inputHasta) {
                inputHasta.addEventListener('input', validarRangoNotas);
                inputHasta.addEventListener('blur', validarRangoNotas);
            }
            if (inputDesc) {
                const validarDesc = () => {
                    if (inputDesc.value.length > 60) {
                        marcarErrorCampo(inputDesc, 'La descripción no debe exceder los 60 caracteres.');
                    } else {
                        limpiarErrorCampo(inputDesc);
                    }
                };
                inputDesc.addEventListener('input', validarDesc);
                inputDesc.addEventListener('blur', validarDesc);
            }
        }

        // Manejar envío asíncrono (AJAX) para formularios de Escala
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
                    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
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

                    const mensajeExito = data.message || 'Escala literal guardada exitosamente.';
                    sessionStorage.setItem('flash_success', mensajeExito);
                    window.location.reload();

                } catch (error) {
                    console.error('Error en envío AJAX:', error);
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
        const formCrear = document.getElementById('formCrearEscala');
        if (formCrear) {
            manejarEnvioAjax(formCrear);
        }

        // Aplicar a todos los formularios de edición
        document.querySelectorAll('form[id^="formEditarEscala"]').forEach(form => {
            manejarEnvioAjax(form);
        });

        // Limpieza y reset al abrir el modal de Crear
        const modalCrear = document.getElementById('modalCrearEscala');
        if (modalCrear) {
            modalCrear.addEventListener('show.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    limpiarErroresFormulario(form);
                    const inputDesde = form.querySelector('[name="nota_desde"]');
                    const inputHasta = form.querySelector('[name="nota_hasta"]');
                    if (inputDesde) inputDesde.value = '1';
                    if (inputHasta) inputHasta.value = '20';
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
