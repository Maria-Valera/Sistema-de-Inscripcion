<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-door-open"></i>
                </div>
                <h5 class="modal-title-create">Nueva Aula</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <form action="{{ route('aulas.store') }}" method="POST" id="formCrearAula">
                    @csrf

                    <div class="form-group-modern">
                        <label for="nombre_aula_crear" class="form-label-modern">
                            <i class="fas fa-chalkboard"></i> Nombre del Aula
                        </label>
                        <input type="text"
                               class="form-control-modern"
                               id="nombre_aula_crear"
                               name="nombre_aula"
                               maxlength="100"
                               placeholder="Ej: Aula 101"
                               autocomplete="off">

                        {{-- Mensaje vacío --}}
                        <div class="error-message" id="error-crear-vacio">
                            <i class="fas fa-exclamation-circle"></i>
                            El nombre del aula es obligatorio.
                        </div>

                        {{-- Mensaje duplicado --}}
                        <div class="error-message" id="error-crear-duplicado">
                            <i class="fas fa-exclamation-circle"></i>
                            Ya existe un aula con ese nombre.
                        </div>

                        {{-- Mensaje OK --}}
                        <div class="error-message" id="ok-crear" style="background: var(--soft-success-bg); color: var(--soft-success-text); display:none;">
                            <i class="fas fa-check-circle"></i>
                            Nombre disponible.
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="tipo_aula_crear" class="form-label-modern">
                            <i class="fas fa-layer-group"></i> Tipo de Aula
                        </label>
                        <select class="form-control-modern"
                               id="tipo_aula_crear"
                               name="tipo_aula"
                               required>
                            <option value="">Seleccione el tipo de aula</option>
                            <option value="Aula Regular">Aula Regular</option>
                            <option value="Laboratorio">Laboratorio</option>
                            <option value="Sala Especializada">Sala Especializada</option>
                            <option value="Aula Magna">Aula Magna</option>
                            <option value="Biblioteca">Biblioteca</option>
                            <option value="Gimnasio">Gimnasio</option>
                            <option value="Patio">Patio</option>
                        </select>

                        <div class="error-message" id="error-tipo-crear-vacio">
                            <i class="fas fa-exclamation-circle"></i>
                            El tipo de aula es obligatorio.
                        </div>
                    </div>

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-create" id="btnGuardarCrear">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
