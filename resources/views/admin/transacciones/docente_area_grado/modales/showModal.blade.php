<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" style="max-width: 840px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-chalkboard-teacher" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.15rem;">
                            Información del Docente
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Ficha detallada y asignaciones académicas</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 py-3" style="background-color: #FFFFFF; max-height: calc(100vh - 200px); overflow-y: auto;">
                
                {{-- Sección 1: Datos Personales del Docente --}}
                <div class="d-flex align-items-center gap-2 pb-2 mb-3" style="border-bottom: 1px solid #F1F5F9;">
                    <i class="fas fa-id-card" style="color: #4A6FC1; font-size: 0.95rem;"></i>
                    <span class="fw-bold" style="font-size: 16px; color: #334155;">
                        Datos del Docente
                    </span>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Fila 1: Cédula (col-md-3), Nombre Completo (col-md-6), Género (col-md-3) -> 12 cols -->
                    <div class="col-md-3">
                        <label class="modal-detail-label">Cédula</label>
                        <span class="modal-detail-value fw-bold">
                            {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <label class="modal-detail-label">Nombre Completo</label>
                        <span class="modal-detail-value fw-semibold">
                            {{ $datos->persona->primer_nombre ?? '' }}
                            {{ $datos->persona->segundo_nombre ?? '' }}
                            {{ $datos->persona->primer_apellido ?? '' }}
                            {{ $datos->persona->segundo_apellido ?? '' }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label class="modal-detail-label">Género</label>
                        <span class="modal-detail-value">
                            {{ $datos->persona->genero->genero ?? 'N/A' }}
                        </span>
                    </div>

                    <!-- Fila 2: Fecha de Nacimiento (col-md-4), Código Docente (col-md-4), Dependencia (col-md-4) -> 12 cols -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Fecha de Nacimiento</label>
                        <span class="modal-detail-value">
                            {{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                            <small class="text-muted fw-normal">({{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->age }} años)</small>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Código Docente</label>
                        <span class="modal-detail-value fw-bold" style="color: #4A6FC1;">
                            {{ $datos->codigo ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Dependencia</label>
                        <span class="modal-detail-value">
                            {{ $datos->dependencia ?? 'Sin especificar' }}
                        </span>
                    </div>

                    <!-- Fila 3: Correo (col-md-4), Teléfono Principal (col-md-4), Teléfono Secundario (col-md-4) -> 12 cols -->
                    <div class="col-md-4">
                        <label class="modal-detail-label">Correo Electrónico</label>
                        <span class="modal-detail-value">
                            {{ $datos->persona->email ?? 'Sin registrar' }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Teléfono Principal</label>
                        <span class="modal-detail-value">
                            {{ $datos->persona->telefono_completo ?? 'Sin registrar' }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <label class="modal-detail-label">Teléfono Secundario</label>
                        <span class="modal-detail-value">
                            {{ $datos->persona->telefono_dos_completo ?? 'Sin registrar' }}
                        </span>
                    </div>

                    <!-- Fila 4: Estudios Realizados (col-md-12) -> 12 cols -->
                    <div class="col-md-12">
                        <label class="modal-detail-label">Estudios Realizados</label>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @forelse ($datos->detalleEstudios as $est)
                                <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.8rem; font-weight: 500; border-radius: 6px; padding: 0.35rem 0.65rem;">
                                    <i class="fas fa-graduation-cap me-1 text-primary"></i>{{ $est->estudiosRealizado->estudios }}
                                </span>
                            @empty
                                <span class="text-muted fst-italic" style="font-size: 0.85rem;">Sin registrar</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Áreas, Niveles y Secciones Asignadas --}}
                <div class="d-flex align-items-center gap-2 pb-2 mb-3 mt-2" style="border-bottom: 1px solid #F1F5F9;">
                    <i class="fas fa-book-open" style="color: #4A6FC1; font-size: 0.95rem;"></i>
                    <span class="fw-bold" style="font-size: 16px; color: #334155;">
                        Áreas de Formación, Niveles Académicos y Secciones Asignadas
                    </span>
                </div>

                <div class="mb-3" style="padding-bottom: 24px;">
                    @php
                        $asigs = $datos->asignacionesAreasActivas
                            ->where('status', true)
                            ->sortBy('tipo_asignacion'); 
                    @endphp

                    @forelse($asigs as $asignacion)
                        <div class="d-flex align-items-center justify-content-between p-3 mb-3"
                             style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);">
                            <div class="d-flex align-items-center gap-3">
                                @if ($asignacion->tipo_asignacion === 'area')
                                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0 rounded-circle"
                                         style="width: 40px; height: 40px; background-color: #F1F5F9; color: #4A6FC1;">
                                        <i class="fas fa-book-open" style="font-size: 0.95rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #1E293B; font-size: 0.95rem;">
                                            {{ optional($asignacion->areaEstudios->areaFormacion)->nombre_area_formacion ?? 'N/A' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.84rem; margin-top: 2px;">
                                            Nivel {{ optional($asignacion->grado)->numero_grado ?? 'N/A' }}º · Sección {{ optional($asignacion->seccion)->nombre ?? 'N/A' }}
                                        </div>
                                    </div>
                                @elseif ($asignacion->tipo_asignacion === 'grupo_estable')
                                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0 rounded-circle"
                                         style="width: 40px; height: 40px; background-color: #F1F5F9; color: #4A6FC1;">
                                        <i class="fas fa-users" style="font-size: 0.95rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #1E293B; font-size: 0.95rem;">
                                            {{ optional($asignacion->grupoEstable)->nombre_grupo_estable ?? 'N/A' }}
                                            <span class="badge ms-1" style="background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.72rem; font-weight: 500;">Grupo Estable</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.84rem; margin-top: 2px;">
                                            Nivel {{ optional($asignacion->gradoGrupoEstable)->numero_grado ?? 'N/A' }}º · Grupo Estable
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="text-end text-muted flex-shrink-0 ps-3" style="font-size: 0.8rem; color: #94A3B8 !important; white-space: nowrap;">
                                Asignado: {{ $asignacion->created_at ? $asignacion->created_at->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-minus-circle fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0" style="font-size: 0.88rem;">Este docente no tiene asignaciones activas.</p>
                        </div>
                    @endforelse
                </div>

            </div>
            
            <div class="modal-footer d-flex justify-content-end px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important;">
                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
