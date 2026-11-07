<div class="modal fade" id="editarCorte{{ $corte->id }}" tabindex="-1"
    aria-labelledby="editarCorteLabel{{ $corte->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit" id="editarCorteLabel{{ $corte->id }}">Editar {{ $corte->nombre }}</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-edit" style="overflow-y: auto;">
                <form action="{{ route('admin.cortes.update', [$lapso, $corte]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('admin.cortes.modales._campos', ['corte' => $corte, 'idModal' => 'editarCorte' . $corte->id, 'numeroPorDefecto' => null, 'pesoPorDefecto' => null])

                    <div class="modal-footer-edit">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-modal-edit">Guardar cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
