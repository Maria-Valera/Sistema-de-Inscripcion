<div class="modal fade" id="modalEliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title-delete" id="modalEliminarLabel">Eliminar Permiso/Reposo</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-delete">
                <div class="delete-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>¿Está seguro de que desea eliminar este permiso/reposo?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <form action="" method="POST" id="formEliminarPermiso">
                    @csrf
                    @method('DELETE')
                    <div class="modal-footer-delete">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-delete">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
