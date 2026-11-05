<div class="modal fade" id="modalCrear" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">

            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearLabel">Nuevo Permiso/Reposo</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <form action="{{ route('admin.permisos_reposos.modales.store') }}" method="POST" id="formCrearPermiso">
                    @csrf
                    <div id="contenedorAlertaCrear"></div>
                    <div class="form-group-modern">
                        <label for="nombre_reposo" class="form-label-modern">
                            <i class="fas fa-file-alt me-2"></i> Nombre
                        </label>
                        <input type="text" class="form-control" id="nombre_reposo" name="nombre_reposo"
                            placeholder="Ejemplo: Licencia por enfermedad" required>
                        @error('nombre_reposo')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    <div class="form-group-modern">
                        <label for="tipo" class="form-label-modern">
                            <i class="fas fa-tag me-2"></i> Tipo
                        </label>
                        <select class="form-control" id="tipo" name="tipo" required>
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
                        <label for="dias_reposo" class="form-label-modern">
                            <i class="fas fa-calendar-alt me-2"></i> Días
                        </label>
                        <input type="number" class="form-control" id="dias_reposo" name="dias_reposo"
                            inputmode="numeric" pattern="[0-9]+" min="1"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Ejemplo: 3" required>
                        @error('dias_reposo')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-create">
                                Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
