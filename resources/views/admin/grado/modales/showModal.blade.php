<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-eye" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                            Detalles del Nivel Académico
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Información detallada del nivel configurado</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-hashtag text-primary me-1"></i> Nivel Académico
                            </span>
                            <span class="fw-bold text-dark" style="font-size: 1.15rem;">{{ $datos->numero_grado }}° Año</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-users text-primary me-1"></i> Capacidad Máxima
                            </span>
                            <span class="fw-bold text-dark" style="font-size: 1.15rem;">{{ $datos->capacidad_max }} cupos</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-layer-group text-primary me-1"></i> Mínimo de Secciones
                            </span>
                            <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $datos->min_seccion }} sección(es)</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                            <span class="text-muted d-block mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-layer-group text-primary me-1"></i> Máximo de Secciones
                            </span>
                            <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $datos->max_seccion }} sección(es)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con Botón Secundario Moderno -->
            <div class="modal-footer d-flex justify-content-end px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
