<div class="modal fade" id="eliminarConfiguracion{{ $config->id }}" tabindex="-1"
    aria-labelledby="eliminarConfiguracionLabel{{ $config->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-delete">
                <div class="modal-icon-delete">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="modal-title-delete" id="eliminarConfiguracionLabel{{ $config->id }}">Confirmar eliminación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-delete">
                <p>¿Eliminar la configuración de evaluación del año
                    <strong>{{ $config->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $config->anioEscolar->cierre_anio_escolar->format('Y') }}</strong>?</p>
            </div>
            <div class="modal-footer-delete">
                <form action="{{ route('admin.configuracion_evaluacion.destroy', $config) }}" method="POST">
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
