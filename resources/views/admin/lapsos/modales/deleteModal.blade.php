<div class="modal fade" id="eliminarLapso{{ $lapso->id }}" tabindex="-1"
    aria-labelledby="eliminarLapsoLabel{{ $lapso->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title-delete" id="eliminarLapsoLabel{{ $lapso->id }}">Confirmar eliminación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-delete">
                <p>¿Eliminar <strong>{{ $lapso->nombre }}</strong>?</p>
                @if ($lapso->cortes_count > 0)
                    <p class="text-danger mb-0">También se eliminarán sus <strong>{{ $lapso->cortes_count }} cortes</strong>. Esta acción no se puede deshacer.</p>
                @endif
            </div>
            <div class="modal-footer-delete">
                <form action="{{ route('admin.lapsos.destroy', $lapso) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="footer-buttons">
                        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-modal-delete">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
