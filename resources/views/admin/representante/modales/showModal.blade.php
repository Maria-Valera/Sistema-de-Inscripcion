{{-- Modal Ver Detalles del Representante (Design System - SaaS Grid Equilibrado) --}}
<div class="modal fade" id="modalVerDetalleRegistro" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="modalVerDetalleRegistroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 940px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; background: #FFFFFF; overflow: hidden;">

            <!-- Cabecera Limpia (Design System) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-user-tie" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title m-0 fw-bold" id="modalVerDetalleRegistroLabel" style="color: #1E293B; font-size: 1.15rem;">
                                Detalles del Representante
                            </h5>
                            <span id="tipo-representante-badge" class="badge-pill-modern" style="background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE;"></span>
                        </div>
                        <p class="text-muted m-0" style="font-size: 0.8rem;">Información completa registrada en el sistema</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo del modal con Grid Equilibrado y Scroll Suave de Respaldo -->
            <div class="modal-body p-4" style="background-color: #FFFFFF; max-height: 72vh; overflow-y: auto;">

                <!-- 1. SECCIÓN: IDENTIFICACIÓN Y DATOS PERSONALES -->
                <div class="modal-section-title">
                    <i class="fas fa-id-card"></i>
                    <span>Identificación y Datos Personales</span>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Fila 1: Documentación (3 columnas) -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Tipo de Documento</label>
                        <span class="modal-detail-value" id="modal-tipo-documento">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Número de Cédula</label>
                        <span class="modal-detail-value fw-bold" id="modal-numero_documento">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Fecha de Nacimiento</label>
                        <span class="modal-detail-value" id="modal-fecha-nacimiento">N/A</span>
                    </div>

                    <!-- Fila 2: Nombres y Apellidos (Ancho completo 12 columnas) -->
                    <div class="col-md-12">
                        <label class="modal-detail-label">Nombres y Apellidos</label>
                        <span class="modal-detail-value fw-semibold" style="font-size: 15px;">
                            <span id="modal-primer-nombre">N/A</span>
                            <span id="modal-segundo-nombre"></span>
                            <span id="modal-tercer-nombre"></span>
                            <span id="modal-primer-apellido"></span>
                            <span id="modal-segundo-apellido"></span>
                        </span>
                    </div>

                    <!-- Fila 3: Datos Personales (3 columnas) -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Género</label>
                        <span class="modal-detail-value" id="modal-genero">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Ocupación</label>
                        <span class="modal-detail-value" id="modal-ocupacion">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Convive con Estudiante</label>
                        <div>
                            <span id="modal-convive" class="badge-pill-modern badge-pill-no">N/A</span>
                        </div>
                    </div>
                </div>

                <!-- 2. SECCIÓN: CONTACTO Y UBICACIÓN -->
                <div class="modal-section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Contacto y Ubicación</span>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Fila 1: Contacto (3 columnas) -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Teléfono Principal</label>
                        <span class="modal-detail-value fw-semibold" id="modal-telefono">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Teléfono Secundario</label>
                        <span class="modal-detail-value" id="modal-telefono-dos">N/A</span>
                    </div>
                    <div class="col-md-4" id="correo-detail-item">
                        <label class="modal-detail-label">Correo Electrónico</label>
                        <span class="modal-detail-value" id="modal-correo">N/A</span>
                    </div>

                    <!-- Fila 2: Región y Localidad (3 columnas) -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">País</label>
                        <span class="modal-detail-value" id="modal-pais">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Estado</label>
                        <span class="modal-detail-value" id="modal-estado">N/A</span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Municipio</label>
                        <span class="modal-detail-value" id="modal-municipio">N/A</span>
                    </div>

                    <!-- Fila 3: Parroquia (4 col) y Dirección de Habitación (8 col) -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Parroquia</label>
                        <span class="modal-detail-value" id="modal-parroquia">N/A</span>
                    </div>
                    <div class="col-md-8">
                        <label class="modal-detail-label">Dirección de Habitación</label>
                        <span class="modal-detail-value" id="modal-direccion">N/A</span>
                    </div>
                </div>

                <!-- 3. SECCIÓN: DATOS DE REPRESENTANTE LEGAL (Condicional) -->
                <div id="legal-info-section" style="display: none;">
                    <div class="modal-section-title mt-4">
                        <i class="fas fa-gavel"></i>
                        <span>Datos de Representante Legal</span>
                    </div>

                    <div class="row g-4">
                        <!-- Fila 1: Filiación y Patria (3 columnas) -->
                        <div class="col-md-4">
                            <label class="modal-detail-label">Parentesco</label>
                            <span class="modal-detail-value fw-semibold" id="modal-parentesco">N/A</span>
                        </div>
                        <div class="col-md-4">
                            <label class="modal-detail-label">Carnet de la Patria</label>
                            <div>
                                <span id="modal-carnet-afiliado" class="badge-pill-modern" style="background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">N/A</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="modal-detail-label">Pertenece a Organización</label>
                            <div>
                                <span id="modal-pertenece-org" class="badge-pill-modern badge-pill-no">N/A</span>
                            </div>
                        </div>

                        <!-- Fila 2: Código, Serial y Organización (3 columnas) -->
                        <div class="col-md-4" id="campo-codigo">
                            <label class="modal-detail-label">Código Carnet</label>
                            <span class="modal-detail-value" id="modal-codigo">N/A</span>
                        </div>
                        <div class="col-md-4" id="campo-serial">
                            <label class="modal-detail-label">Serial Carnet</label>
                            <span class="modal-detail-value" id="modal-serial">N/A</span>
                        </div>
                        <div class="col-md-4" id="campo-organizacion" style="display: none;">
                            <label class="modal-detail-label">Organización</label>
                            <span class="modal-detail-value" id="modal-org-pertenece">N/A</span>
                        </div>

                        <!-- Fila 3: Datos Bancarios (2 columnas de 6) -->
                        <div class="col-md-6">
                            <label class="modal-detail-label">Tipo de Cuenta</label>
                            <span class="modal-detail-value" id="modal-tipo-cuenta">N/A</span>
                        </div>
                        <div class="col-md-6">
                            <label class="modal-detail-label">Banco</label>
                            <span class="modal-detail-value fw-semibold" id="modal-banco">N/A</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Pie del modal (Fijo abajo a la derecha) -->
            <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                <button type="button" class="btn btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-detail-label {
        display: block;
        font-size: 11.5px;
        font-weight: 600;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-style: normal !important;
        margin-bottom: 4px;
    }

    .modal-detail-value {
        font-size: 14.5px;
        font-weight: 500;
        color: #1E293B;
        font-style: normal !important;
        line-height: 1.35;
        display: block;
    }

    .modal-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1E293B;
        border-bottom: 1px solid #E2E8F0;
        padding-bottom: 8px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .modal-section-title i {
        color: #4A6FC1;
        font-size: 0.95rem;
    }

    .badge-pill-modern {
        border-radius: 50px !important;
        padding: 3px 12px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1.2 !important;
    }

    .badge-pill-yes {
        background-color: #F0FDF4 !important;
        color: #16A34A !important;
        border: 1px solid #BBF7D0 !important;
    }

    .badge-pill-no {
        background-color: #FEF2F2 !important;
        color: #EF4444 !important;
        border: 1px solid #FCA5A5 !important;
    }
</style>