<div class="modal fade" id="modalVerMencion{{ $mencion->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden; max-height: 90vh;">

            {{-- Cabecera --}}
            <div class="modal-header-show py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); border-bottom: none; flex-shrink: 0;">
                <div class="modal-icon-show" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-show mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Mención {{ $mencion->codigo }}</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Detalle oficial de mención</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-show p-4" style="max-height: calc(85vh - 140px); overflow-y: auto;">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-hashtag mr-2 me-2 text-primary"></i> Código de Mención:</span>
                        <span class="font-weight-bold fw-bold badge badge-primary px-3 py-1 font-monospace" style="font-size: 1rem;">{{ $mencion->codigo }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-award mr-2 me-2 text-primary"></i> Nombre:</span>
                        <span class="font-weight-bold fw-bold text-dark">{{ $mencion->nombre }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-sync mr-2 me-2 text-primary"></i> Ciclo MPPE:</span>
                        <span class="badge badge-light border font-weight-bold px-2 py-1" style="color: #0f172a !important; background: #f1f5f9;">Ciclo {{ $mencion->ciclo }}</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-chalkboard mr-2 me-2 text-primary"></i> Secciones con esta mención:</span>
                        <span class="font-weight-bold fw-bold text-dark">{{ $mencion->secciones_count ?? $mencion->secciones()->count() }} secciones</span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-toggle-on mr-2 me-2 text-primary"></i> Estado:</span>
                        @if($mencion->status)
                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Activa</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Inactiva</span>
                        @endif
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

