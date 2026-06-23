<div class="modal fade" id="modalEditar{{ $aula->id_aula }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit">Editar Aula</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit">
                <form action="{{ route('aulas.update', $aula) }}" method="POST" id="formEditarAula{{ $aula->id_aula }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group-modern">
                        <label for="nombre_aula_editar_{{ $aula->id_aula }}" class="form-label-modern">
                            <i class="fas fa-chalkboard"></i> Nombre del Aula
                        </label>
                        <input type="text"
                               class="form-control-modern"
                               id="nombre_aula_editar_{{ $aula->id_aula }}"
                               name="nombre_aula"
                               maxlength="100"
                               placeholder="Ej: Aula 101"
                               value="{{ $aula->nombre_aula }}"
                               autocomplete="off"
                               data-id="{{ $aula->id_aula }}">

                        <div class="error-message" id="error-editar-vacio-{{ $aula->id_aula }}">
                            <i class="fas fa-exclamation-circle"></i>
                            El nombre del aula es obligatorio.
                        </div>

                        <div class="error-message" id="error-editar-duplicado-{{ $aula->id_aula }}">
                            <i class="fas fa-exclamation-circle"></i>
                            Ya existe un aula con ese nombre.
                        </div>

                        <div class="error-message" id="ok-editar-{{ $aula->id_aula }}" style="background: var(--soft-success-bg); color: var(--soft-success-text); display:none;">
                            <i class="fas fa-check-circle"></i>
                            Nombre disponible.
                        </div>
                    </div>

                    <div class="modal-footer-edit">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-edit" id="btnGuardarEditar{{ $aula->id_aula }}">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
