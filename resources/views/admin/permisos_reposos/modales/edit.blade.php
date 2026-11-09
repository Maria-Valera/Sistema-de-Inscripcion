<div class="modal fade" id="modalEditar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title-edit" id="modalEditarLabel">Editar Permiso/Reposo</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit">
                <form action="" method="POST" id="formEditarPermiso">
                    @csrf
                    @method('POST')
                    <div id="contenedorAlertaEditar"></div>
                    <div class="form-group-modern">
                        <label for="edit_nombre_reposo" class="form-label-modern">
                            <i class="fas fa-file-alt me-2"></i> Nombre
                        </label>
                        <input type="text" class="form-control" id="edit_nombre_reposo" name="nombre_reposo"
                            placeholder="Ejemplo: Licencia por enfermedad" required>
                        @error('nombre_reposo')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label for="edit_tipo" class="form-label-modern">
                            <i class="fas fa-tag me-2"></i> Tipo
                        </label>
                        <select class="form-control" id="edit_tipo" name="tipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Permiso">Permiso</option>
                            <option value="Reposo">Reposo</option>
                        </select>
                        @error('tipo')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label for="edit_dias_reposo" class="form-label-modern">
                            <i class="fas fa-calendar-alt me-2"></i> Días
                        </label>
                        <input type="number" class="form-control" id="edit_dias_reposo" name="dias_reposo"
                            inputmode="numeric" pattern="[0-9]+" min="1"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Ejemplo: 3" required>
                        @error('dias_reposo')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    <div class="modal-footer-edit">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-edit">
                                Actualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
