<div class="modal fade" id="confirmarEliminar{{ $horario['id'] }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash"></i>
                </div>
                <h5 class="modal-title-delete">Confirmar eliminación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-delete">
                <p>¿Deseas eliminar el horario de {{ $horario['grado'] }}° Año - Sección {{ $horario['seccion'] }}?</p>
                <p class="delete-warning">
                    Esta acción eliminará todas las asignaciones de horario para este grado y sección.
                </p>
            </div>
            <div class="modal-footer-delete">
                <form action="{{ route('admin.horario.destroy', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}" method="POST"
                    class="w-100">
                    @csrf
                    @method('DELETE')
                    <div class="footer-buttons">
                        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-modal-delete">
                            Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
