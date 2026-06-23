<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1"
    aria-labelledby="viewModalLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-view">
                <div class="modal-icon-view">
                    <i class="fas fa-eye"></i>
                </div>
                <h5 class="modal-title-view" id="viewModalLabel{{ $datos->id }}">Detalles del Bloque Horario</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-view">
                <div class="view-details">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-play-circle me-2"></i> Hora de Inicio
                        </div>
                        <div class="detail-value">{{ $datos->hora_inicio }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-stop-circle me-2"></i> Hora de Fin
                        </div>
                        <div class="detail-value">{{ $datos->hora_fin }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-info-circle me-2"></i> Estado
                        </div>
                        <div class="detail-value">
                            @if ($datos->status)
                                <span class="status-badge status-active">
                                    <span class="status-dot"></span>
                                    Activo
                                </span>
                            @else
                                <span class="status-badge status-inactive">
                                    <span class="status-dot"></span>
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-view">
                <button type="button" class="btn-modal-close" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
