<div class="modal fade" id="modalCrearAreaFormacion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearAreaFormacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-create">
                <div class="modal-icon-create">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h5 class="modal-title-create" id="modalCrearAreaFormacionLabel">Nueva Area de Formación</h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create">
                <form id="formAreaFormacion" action="{{ route('admin.area_formacion.modales.store') }}" method="POST">
                    @csrf
                    <div class="form-group-modern">
                        <label for="nombre_area_formacion" class="form-label-modern">
                            <i class="fas fa-book"></i>
                            Nombre del area de formación
                        </label>
                        <input type="text" 
                            name="nombre_area_formacion" 
                            id="nombre_area_formacion" 
                            class="form-control-modern" 
                            placeholder="Ingrese el nombre del area de formación"
                            required>
                        
                        @error('nombre_area_formacion')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div> 
                    
                    <div class="form-group-modern">
                        <label for="codigo_area" class="form-label-modern">
                            <i class="fas fa-book"></i>
                            Código del area de formación
                        </label>
                        <input type="text" 
                            name="codigo_area" 
                            id="codigo_area" 
                            class="form-control-modern" 
                            placeholder="Ingrese el código del area de formación"
                            required
                            inputmode="numeric"
                            pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('codigo_area')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="siglas" class="form-label-modern">
                            <i class="fas fa-book"></i>
                            Siglas del area de formación
                        </label>
                        <input type="text" 
                            name="siglas" 
                            id="siglas" 
                            class="form-control-modern" 
                            placeholder="Ingrese las siglas del area de formación"
                            required>
                        
                        @error('siglas')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="horas_semanales" class="form-label-modern">
                            <i class="fas fa-clock"></i>
                            Horas Semanales
                        </label>
                        <input type="number" 
                            name="horas_semanales" 
                            id="horas_semanales" 
                            class="form-control-modern" 
                            placeholder="Ingrese las horas semanales"
                            required
                            min="1"
                            max="40">
                        
                        @error('horas_semanales')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="bloques_maximos_por_dia" class="form-label-modern">
                            <i class="fas fa-calendar-day"></i>
                            Bloques Máximos por Día
                        </label>
                        <input type="number" 
                            name="bloques_maximos_por_dia" 
                            id="bloques_maximos_por_dia" 
                            class="form-control-modern" 
                            placeholder="Máximo de bloques por día (1-8)"
                            required
                            min="1"
                            max="8"
                            value="2">
                        <small class="text-muted">Cantidad máxima de bloques de esta materia que se pueden asignar en un mismo día.</small>
                        
                        @error('bloques_maximos_por_dia')
                            <div class="error-message">
                                Este campo es obligatorio (mínimo 1, máximo 8).
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="aula_id" class="form-label-modern">
                            <i class="fas fa-door-open"></i>
                            Aula Asignada
                        </label>
                        <select name="aula_id" id="aula_id" class="form-control-modern" required>
                            <option value="">Seleccione un aula</option>
                            @foreach($aulas ?? [] as $aula)
                                <option value="{{ $aula->id_aula }}">{{ $aula->nombre_aula }} - {{ $aula->tipo_aula }}</option>
                            @endforeach
                        </select>
                        
                        @error('aula_id')
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
