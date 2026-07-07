<div class="modal fade" id="viewModalEditar{{ $datos->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title-edit" id="viewModalEditarLabel{{ $datos->id }}">Editar Bloque Horario</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit">
                <form action="{{ route('admin.bloque_horario.modales.update', $datos->id) }}" method="POST" id="formEditarBloqueHorario{{ $datos->id }}">
                    @csrf
                    @method('PUT')

                    <div id="contenedorAlertaEditar{{ $datos->id }}"></div>

                    <div class="form-group-modern">
                        <label for="hora_inicio_edit_{{ $datos->id }}" class="form-label-modern">
                            <i class="fas fa-play-circle me-2"></i> Hora de Inicio
                        </label>
                        <input type="time" class="form-control-modern" id="hora_inicio_edit_{{ $datos->id }}" name="hora_inicio"
                            value="{{ $datos->hora_inicio }}" required>
                        @error('hora_inicio')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="hora_fin_edit_{{ $datos->id }}" class="form-label-modern">
                            <i class="fas fa-stop-circle me-2"></i> Hora de Fin
                        </label>
                        <input type="time" class="form-control-modern" id="hora_fin_edit_{{ $datos->id }}" name="hora_fin"
                            value="{{ $datos->hora_fin }}" required>
                        @error('hora_fin')
                            <div class="error-message">
                                Este campo es obligatorio y debe ser posterior a la hora de inicio.
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
