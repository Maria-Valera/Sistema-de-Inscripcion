{{-- Modal para confirmar la eliminación de un calendario y todos sus eventos --}}
<div class="modal fade" id="eliminarCalendario{{ $calendario->id }}" tabindex="-1"
    aria-labelledby="eliminarCalendarioLabel{{ $calendario->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title-delete" id="eliminarCalendarioLabel{{ $calendario->id }}">Confirmar eliminación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-delete">
                <p>¿Eliminar el calendario
                    <strong>{{ $calendario->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $calendario->anioEscolar->cierre_anio_escolar->format('Y') }}</strong>?</p>
                @if ($calendario->dias_count > 0)
                    <p class="text-danger mb-0">También se eliminarán sus <strong>{{ $calendario->dias_count }} eventos</strong>. Esta acción no se puede deshacer.</p>
                @endif
            </div>
            <div class="modal-footer-delete">
                <form action="{{ route('admin.calendario_academico.destroy', $calendario) }}" method="POST">
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
