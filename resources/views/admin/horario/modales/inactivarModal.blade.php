<div class="modal fade modal-danger-delete" id="confirmarEliminar{{ $horario['id'] }}" tabindex="-1" aria-labelledby="confirmarEliminarLabel{{ $horario['id'] }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            <!-- Modal Header Limpio con Acento de Advertencia Rojo -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 44px; height: 44px; border-radius: 12px; background-color: #FEE2E2; color: #EF4444;">
                        <i class="fas fa-trash-alt" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="confirmarEliminarLabel{{ $horario['id'] }}" style="color: #1E293B; font-size: 1.1rem;">
                            Eliminar Horario
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Confirmación de acción destructiva</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Formulario y Body de Advertencia -->
            <form action="{{ route('admin.horario.destroy', ['grado_id' => $horario['grado_id'], 'seccion_id' => $horario['seccion_id']]) }}"
                  method="POST"
                  class="form-eliminar-horario m-0">
                @csrf
                @method('DELETE')

                <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                    <p class="fw-semibold mb-3" style="color: #1E293B; font-size: 0.95rem;">
                        ¿Estás seguro de que deseas eliminar este horario?
                    </p>

                    <!-- Recuadro Gris de Resumen -->
                    <div class="p-3 mb-3 d-flex align-items-center gap-3"
                         style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px;">
                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                             style="width: 38px; height: 38px; background-color: #F1F5F9; color: #475569;">
                            <i class="fas fa-calendar-times" style="font-size: 1rem;"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.92rem;">
                                {{ $horario['grado'] }}° Año - Sección {{ $horario['seccion'] }}
                            </div>
                            <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                <i class="fas fa-clock" style="font-size: 0.75rem; color: #64748B;"></i>
                                <span>{{ $horario['total_asignaciones'] }} {{ \Illuminate\Support\Str::plural('bloque', $horario['total_asignaciones']) }} asignados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Advertencia en texto rojo suave -->
                    <div class="d-flex align-items-start gap-2 p-3 rounded-3"
                         style="background-color: #FEF2F2; border: 1px solid #FEE2E2; border-left: 4px solid #EF4444;">
                        <i class="fas fa-exclamation-triangle flex-shrink-0 mt-1" style="color: #DC2626; font-size: 0.85rem;"></i>
                        <p class="m-0" style="color: #DC2626; font-size: 13px; line-height: 1.45; font-weight: 500;">
                            Esta acción no se puede deshacer y se perderán todas las asignaciones de clases para esta sección.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer Alineado a la Derecha -->
                <div class="modal-footer d-flex justify-content-end align-items-center gap-2 px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                    <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="btn-danger-modern d-inline-flex align-items-center gap-2" id="btnConfirmarEliminar{{ $horario['id'] }}">
                        <i class="fas fa-trash-alt"></i>
                        <span>Sí, eliminar horario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
