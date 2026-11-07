<div class="modal fade" id="editarConfiguracion{{ $config->id }}" tabindex="-1"
    aria-labelledby="editarConfiguracionLabel{{ $config->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit" id="editarConfiguracionLabel{{ $config->id }}">
                    Editar configuración {{ $config->anioEscolar->inicio_anio_escolar->format('Y') }} - {{ $config->anioEscolar->cierre_anio_escolar->format('Y') }}
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-edit" style="overflow-y: auto;">
                <form action="{{ route('admin.configuracion_evaluacion.update', $config) }}" method="POST">
                    @csrf
                    @method('PUT')
                    {{-- El año no se cambia al editar --}}
                    <input type="hidden" name="anio_escolar_id" value="{{ $config->anio_escolar_id }}">

                    @include('admin.configuracion_evaluacion.modales._campos', ['config' => $config, 'prefijo' => 'editar' . $config->id])

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
