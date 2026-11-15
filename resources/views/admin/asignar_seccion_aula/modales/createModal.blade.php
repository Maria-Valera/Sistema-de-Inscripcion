<div class="modal fade" id="modalCrearSeccionAula" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCrearSeccionAulaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: visible; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-door-open" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="modalCrearSeccionAulaLabel" style="color: #1E293B; font-size: 1.1rem;">
                            Nueva Asignación de Aula
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Asignar aula física a una sección académica</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formSeccionAula" action="{{ route('admin.seccion_aula.modales.store') }}" method="POST" class="m-0">
                @csrf
                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <div class="mb-3">
                        <label for="id_grado" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-graduation-cap text-primary me-1"></i>
                            <span>Nivel Académico</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="id_grado" id="id_grado" class="form-select" style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required>
                            <option value="">Seleccione un nivel académico</option>
                            @foreach(\App\Models\Grado::where('status', true)->get() as $grado)
                                <option value="{{ $grado->id }}">Nivel {{ $grado->numero_grado }}</option>
                            @endforeach
                        </select>
                        @error('id_grado')
                            <div class="text-danger small mt-1">Este campo es obligatorio.</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_seccion" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-users text-primary me-1"></i>
                            <span>Sección</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="id_seccion" id="id_seccion" class="form-select" style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required disabled>
                            <option value="">Seleccione primero un nivel académico</option>
                        </select>
                        @error('id_seccion')
                            <div class="text-danger small mt-1">Este campo es obligatorio.</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_aula" class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="fas fa-door-open text-primary me-1"></i>
                            <span>Aula</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="id_aula" id="id_aula" class="form-select" style="height: 42px; border-radius: 10px; font-size: 0.95rem;" required>
                            <option value="">Seleccione un aula</option>
                            @foreach(\App\Models\Aula::where('status', true)->get() as $aula)
                                <option value="{{ $aula->id_aula }}">{{ $aula->nombre_aula }} ({{ $aula->tipo_aula }})</option>
                            @endforeach
                        </select>
                        @error('id_aula')
                            <div class="text-danger small mt-1">Este campo es obligatorio.</div>
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
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
