<div class="modal fade" id="modalCrearSeccionAula" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearSeccionAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearSeccionAulaLabel">Nueva Asignación de Aula a Sección</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <form id="formSeccionAula" action="{{ route('admin.seccion_aula.modales.store') }}" method="POST">
                    @csrf
                    <div class="form-group-modern">
                        <label for="id_grado" class="form-label-modern">
                            <i class="fas fa-layer-group"></i>
                            Nivel Académico
                        </label>
                        <select name="id_grado" id="id_grado" class="form-control-modern" required>
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
                        <label for="id_seccion" class="form-label-modern">
                            <i class="fas fa-users"></i>
                            Sección
                        </label>
                        <select name="id_seccion" id="id_seccion" class="form-control-modern" required disabled>
                            <option value="">Seleccione primero un nivel académico</option>
                        </select>
                        
                        @error('id_seccion')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="id_aula" class="form-label-modern">
                            <i class="fas fa-door-open"></i>
                            Aula
                        </label>
                        <select name="id_aula" id="id_aula" class="form-control-modern" required>
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

                    <div class="modal-footer-create">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </button>
                            <button type="submit" class="btn-modal-create">
                                <i class="fas fa-save me-1"></i>
                                Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
