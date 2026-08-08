<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1"
    aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header-edit">
                <div class="modal-icon-edit">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title-edit" id="viewModalEditarLabel{{ $datos->id }}">
                    Editar Area de Formación
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-edit">
                <form action="{{ route('admin.area_formacion.modales.update', $datos->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $datos->id }}">

                    <div class="form-group-modern">
                        <label for="nombre_area_formacion_{{ $datos->id }}" class="form-label-modern">
                            Nombre del Area de Formación
                        </label>
                        <input type="text" class="form-control-modern" id="nombre_area_formacion_{{ $datos->id }}"
                            name="nombre_area_formacion" value="{{ $datos->nombre_area_formacion }}" required>
                        @error('nombre_area_formacion')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="codigo_area_{{ $datos->id }}" class="form-label-modern">
                            Codigo del Area de Formación
                        </label>
                        <input type="text" class="form-control-modern" id="codigo_area_{{ $datos->id }}"
                            name="codigo_area" value="{{ $datos->codigo_area }}" required inputmode="numeric"
                            pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        @error('codigo_area')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="siglas_{{ $datos->id }}" class="form-label-modern">
                            Siglas del Area de Formación
                        </label>
                        <input type="text" class="form-control-modern" id="siglas_{{ $datos->id }}"
                            name="siglas" value="{{ $datos->siglas }}" required>
                        @error('siglas')
                            <div class="error-message">
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="form-group-modern">
                        <label for="aula_id_{{ $datos->id }}" class="form-label-modern">
                            <i class="fas fa-door-open"></i>
                            Aula Asignada
                        </label>
                        <select name="aula_id" id="aula_id_{{ $datos->id }}" class="form-control-modern" required>
                            <option value="">Seleccione un aula</option>
                            @foreach($aulas ?? [] as $aula)
                                <option value="{{ $aula->id_aula }}" {{ $datos->aula_id == $aula->id_aula ? 'selected' : '' }}>
                                    {{ $aula->nombre_aula }} - {{ $aula->tipo_aula }}
                                </option>
                            @endforeach
                        </select>
                        
                        @error('aula_id')
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
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>