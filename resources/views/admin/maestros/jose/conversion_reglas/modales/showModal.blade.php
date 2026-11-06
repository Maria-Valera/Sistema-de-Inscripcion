<div class="modal fade" id="modalVerRegla{{ $regla->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh;">

            {{-- Cabecera --}}
            <div class="modal-header-show py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-show" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-show mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Regla de Equivalencia</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Plan {{ $regla->pensumOrigen->codigo ?? '31059' }} &rarr; Plan {{ $regla->pensumDestino->codigo ?? '31060' }}</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-show p-4" style="max-height: calc(85vh - 140px); overflow-y: auto;">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-arrows-alt-h mr-2 me-2 text-primary"></i> Transición:</span>
                        <span class="font-weight-bold fw-bold badge badge-primary px-3 py-1">
                            Plan {{ $regla->pensumOrigen->codigo ?? '' }} &rarr; Plan {{ $regla->pensumDestino->codigo ?? '' }}
                        </span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-book mr-2 me-2 text-primary"></i> Asignatura Destino:</span>
                        <div class="text-end text-right">
                            <span class="font-weight-bold fw-bold text-dark d-block">{{ $regla->areaDestino->nombre_area_formacion ?? '' }}</span>
                            <small class="text-muted">{{ $regla->gradoDestino ? ($regla->gradoDestino->nombre_grado ?? ($regla->gradoDestino->numero_grado . '° Año')) : '' }}</small>
                        </div>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-cogs mr-2 me-2 text-primary"></i> Tipo de Conversión:</span>
                        @if($regla->tipo_regla === 'directa')
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Homologación Directa</span>
                        @elseif($regla->tipo_regla === 'promedio')
                            <span class="badge badge-info px-2 py-1"><i class="fas fa-calculator mr-1"></i> Promedio de Materias</span>
                        @elseif($regla->tipo_regla === 'exonerada')
                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-ban mr-1"></i> Exonerada (EX)</span>
                        @else
                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-minus-circle mr-1"></i> No se toma en cuenta</span>
                        @endif
                    </div>

                    <div class="list-group-item px-0 py-3 border-bottom">
                        <span class="text-muted d-block mb-2 font-weight-bold"><i class="fas fa-link mr-2 me-2 text-primary"></i> Asignaturas de Origen:</span>
                        @if($regla->origenes->isEmpty())
                            <div class="p-2 bg-light rounded text-muted font-italic small">
                                No requiere asignaturas de origen (asignatura exonerada o no contemplada).
                            </div>
                        @else
                            <ul class="list-group list-group-sm">
                                @foreach($regla->origenes as $idx => $origen)
                                    <li class="list-group-item d-flex justify-content-between align-items-center p-2 bg-light mb-1 rounded border-0">
                                        <span>
                                            <span class="badge badge-secondary mr-2">#{{ $idx + 1 }}</span>
                                            <strong>{{ $origen->areaOrigen->nombre_area_formacion ?? 'N/A' }}</strong>
                                        </span>
                                        <small class="badge badge-outline-dark border">{{ $origen->gradoOrigen->nombre_grado ?? '' }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-toggle-on mr-2 me-2 text-primary"></i> Estado:</span>
                        @if($regla->status)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Activa</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Inactiva</span>
                        @endif
                    </div>

                    <div class="px-0 pt-3">
                        <label class="text-muted mb-1 d-block"><i class="fas fa-file-contract mr-2 me-2 text-primary"></i> Cita Oficial en Certificación:</label>
                        <div class="p-2 bg-light rounded text-dark small" style="line-height: 1.5;">
                            {{ $regla->observacion ?: 'Sin observación adicional registrada.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-show py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f8fafc; flex-shrink: 0;">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

