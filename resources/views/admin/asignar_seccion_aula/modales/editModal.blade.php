<div class="modal fade" id="modalEditarSeccionAula" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEditarSeccionAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-edit"></i>
                </div>
                <h5 class="modal-title-edit" id="modalEditarSeccionAulaLabel">Editar Asignación de Aula a Sección</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit">
                <form id="formEditarSeccionAula" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group-modern">
                        <label for="edit_id_grado" class="form-label-modern">
                            <i class="fas fa-layer-group"></i>
                            Nivel Académico
                        </label>
                        <select name="id_grado" id="edit_id_grado" class="form-control-modern" required>
                            <option value="">Seleccione un nivel académico</option>
                            @foreach(\App\Models\Grado::where('status', true)->get() as $grado)
                                <option value="{{ $grado->id }}">{{ $grado->nombre_grado ?? $grado->numero_grado }}</option>
                            @endforeach
                        </select>
                        
                        @error('id_grado')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="edit_id_seccion" class="form-label-modern">
                            <i class="fas fa-users"></i>
                            Sección
                        </label>
                        <select name="id_seccion" id="edit_id_seccion" class="form-control-modern" required disabled>
                            <option value="">Seleccione primero un nivel académico</option>
                        </select>
                        
                        @error('id_seccion')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="edit_id_aula" class="form-label-modern">
                            <i class="fas fa-door-open"></i>
                            Aula
                        </label>
                        <select name="id_aula" id="edit_id_aula" class="form-control-modern" required>
                            <option value="">Seleccione un aula</option>
                            @foreach(\App\Models\Aula::where('status', true)->get() as $aula)
                                <option value="{{ $aula->id_aula }}">{{ $aula->nombre_aula }} - {{ $aula->tipo_aula }}</option>
                            @endforeach
                        </select>
                        
                        @error('id_aula')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="modal-footer-edit">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-update">
                                <i class="fas fa-save me-1"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
