<div class="modal fade" id="modalCrearBloqueHorario" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalCrearBloqueHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearBloqueHorarioLabel">Nuevo Bloque Horario</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <form action="{{ route('admin.bloque_horario.modales.store') }}" method="POST" id="formCrearBloqueHorario">
                    @csrf

                    <div id="contenedorAlertaCrear"></div>

                    <div class="form-group-modern">
                        <label for="hora_inicio" class="form-label-modern">
                            <i class="fas fa-play-circle me-2"></i> Hora de Inicio
                        </label>
                        <input type="time" class="form-control-modern" id="hora_inicio" name="hora_inicio" required>
                        @error('hora_inicio')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="hora_fin" class="form-label-modern">
                            <i class="fas fa-stop-circle me-2"></i> Hora de Fin
                        </label>
                        <input type="time" class="form-control-modern" id="hora_fin" name="hora_fin" required>
                        @error('hora_fin')
                            <div class="error-message">
                                Este campo es obligatorio y debe ser posterior a la hora de inicio.
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
