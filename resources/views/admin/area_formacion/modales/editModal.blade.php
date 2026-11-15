<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1"
    aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 620px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: visible; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-pen" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalEditarLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                            Editar Área de Formación
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Modificación de parámetros académicos</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.area_formacion.modales.update', $datos->id) }}" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="id" value="{{ $datos->id }}">

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div class="row">
                        <!-- Fila 1: Nombre (col-md-8) y Código (col-md-4) -->
                        <div class="col-md-8 mb-3">
                            <label for="nombre_area_formacion_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-book text-primary me-1"></i>
                                <span>Nombre del Área de Formación</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre_area_formacion_{{ $datos->id }}"
                                name="nombre_area_formacion" value="{{ $datos->nombre_area_formacion }}"
                                style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required>
                            @error('nombre_area_formacion')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio.
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="codigo_area_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                <span>Código</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="codigo_area_{{ $datos->id }}"
                                name="codigo_area" value="{{ $datos->codigo_area }}"
                                style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required
                                inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('codigo_area')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio.
                                </div>
                            @enderror
                        </div>

                        <!-- Fila 2: Siglas (col-md-4) y Aula Asignada (col-md-8) -->
                        <div class="col-md-4 mb-3">
                            <label for="siglas_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-font text-primary me-1"></i>
                                <span>Siglas</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="siglas_{{ $datos->id }}"
                                name="siglas" value="{{ $datos->siglas }}"
                                style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required>
                            @error('siglas')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio.
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="aula_id_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-door-open text-primary me-1"></i>
                                <span>Aula Asignada</span>
                                <span class="text-danger">*</span>
                            </label>
                            <select name="aula_id" id="aula_id_{{ $datos->id }}" class="form-select" style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required>
                                <option value="">Seleccione un aula</option>
                                @foreach($aulas ?? [] as $aula)
                                    <option value="{{ $aula->id_aula }}" {{ $datos->aula_id == $aula->id_aula ? 'selected' : '' }}>
                                        {{ $aula->nombre_aula }} - {{ $aula->tipo_aula }}
                                    </option>
                                @endforeach
                            </select>
                            @error('aula_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio.
                                </div>
                            @enderror
                        </div>

                        <!-- Fila 3: Bloques Semanales (col-md-6) y Bloques Máximos por Día (col-md-6) -->
                        <div class="col-md-6 mb-3">
                            <label for="bloques_semanales_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-clock text-primary me-1"></i>
                                <span>Bloques Semanales</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="bloques_semanales_{{ $datos->id }}"
                                name="bloques_semanales" value="{{ $datos->bloques_semanales }}"
                                style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required
                                min="1" max="40">
                            <div class="form-text text-muted" style="font-size: 0.78rem;">Total semanal (1-40).</div>
                            @error('bloques_semanales')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio.
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bloques_maximos_por_dia_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-day text-primary me-1"></i>
                                <span>Bloques Máx. por Día</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="bloques_maximos_por_dia_{{ $datos->id }}"
                                name="bloques_maximos_por_dia" value="{{ $datos->bloques_maximos_por_dia ?? 2 }}"
                                style="height: 42px; border-radius: 8px; font-size: 0.9rem;" required
                                min="1" max="8">
                            <div class="form-text text-muted" style="font-size: 0.78rem;">Máximo de bloques diarios (1-8).</div>
                            @error('bloques_maximos_por_dia')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> Este campo es obligatorio (1-8).
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Footer Alineado a la Derecha -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-primary-modern">
                        <i class="fas fa-save me-1"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
