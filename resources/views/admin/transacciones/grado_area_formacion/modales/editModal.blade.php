<div class="modal fade" id="viewModalEditar{{ $datos->id }}" tabindex="-1"
    aria-labelledby="viewModalEditarLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
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
                            Editar Asignación
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Modificación de nivel académico y área de formación</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('admin.transacciones.grado_area_formacion.modales.update', $datos->id) }}" method="POST" class="m-0">
                @csrf
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div class="mb-3">
                        <label for="grado_id_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-graduation-cap text-primary me-1"></i>
                            <span>Nivel Académico (Año)</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="grado_id" id="grado_id_{{ $datos->id }}"
                            class="form-control selectpicker" data-live-search="true" title="Seleccione un Nivel"
                            required>
                            @foreach ($grados as $grado)
                                <option value="{{ $grado->id }}"
                                    {{ old('grado_id', $datos->grado_id) == $grado->id ? 'selected' : '' }}>
                                    Nivel {{ $grado->numero_grado }}
                                </option>
                            @endforeach
                        </select>
                        @error('grado_id')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Este campo es obligatorio.
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="area_formacion_id_{{ $datos->id }}" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-bookmark text-primary me-1"></i>
                            <span>Área de Formación</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="area_formacion_id" id="area_formacion_id_{{ $datos->id }}"
                            class="form-control selectpicker" data-live-search="true"
                            title="Seleccione un área de formación" required>
                            @foreach ($areaFormacion as $area)
                                <option value="{{ $area->id }}"
                                    {{ old('area_formacion_id', $datos->area_formacion_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->nombre_area_formacion }}
                                </option>
                            @endforeach
                        </select>
                        @error('area_formacion_id')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Este campo es obligatorio.
                            </div>
                        @enderror
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

@push('js')
    <script>
        $(document).ready(function() {
            $('#viewModalEditar{{ $datos->id }}').on('shown.bs.modal', function() {
                $('#grado_id_{{ $datos->id }}, #area_formacion_id_{{ $datos->id }}').each(
            function() {
                    if (!$(this).data('selectpicker-initialized')) {
                        $(this).selectpicker();
                        $(this).data('selectpicker-initialized', true);
                    } else {
                        $(this).selectpicker('render');
                    }
                });
            });
        });
    </script>
@endpush
