<div class="modal fade" id="crearCorte" tabindex="-1" aria-labelledby="crearCorteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus"></i>
                </div>
                <h5 class="modal-title-create" id="crearCorteLabel">Nuevo corte</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-create" style="overflow-y: auto;">
                <form action="{{ route('admin.cortes.store', $lapso) }}" method="POST">
                    @csrf

                    @include('admin.cortes.modales._campos', ['corte' => null, 'idModal' => 'crearCorte', 'numeroPorDefecto' => $siguienteNumero, 'pesoPorDefecto' => $pesoSugerido])

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-create">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
