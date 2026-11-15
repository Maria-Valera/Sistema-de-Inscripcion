<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 720px;">
        <div class="modal-content modal-modern shadow-lg" style="border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; background: #FFFFFF;">
            
            <!-- Header Limpio (Fondo Blanco) -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 42px; height: 42px; border-radius: 12px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-user-graduate" style="font-size: 1.15rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0 fw-bold" id="viewModalLabel{{ $datos->id }}" style="color: #1E293B; font-size: 1.1rem;">
                            Información del Docente
                        </h5>
                        <p class="text-muted m-0" style="font-size: 0.82rem;">Ficha detallada del personal docente</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 py-3" style="background-color: #FFFFFF;">
                
                {{-- Sección 1: Datos Personales e Identidad --}}
                <div class="text-uppercase fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 11px; color: #64748B; letter-spacing: 0.5px;">
                    <i class="fas fa-id-card text-primary"></i>
                    <span>Datos del Docente</span>
                </div>
                <div class="row align-items-start">
                    <!-- Fila 1: Cédula, Fecha Nacimiento, Género -->
                    <div class="col-md-4" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Cédula</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            {{ $datos->persona->tipoDocumento->nombre ?? 'V' }}-{{ $datos->persona->numero_documento ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="col-md-4" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Fecha de Nacimiento</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            {{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                            <small class="text-muted fw-normal" style="font-size: 13px;">({{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->age }} años)</small>
                        </span>
                    </div>
                    <div class="col-md-4" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Género</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            {{ $datos->persona->genero->genero ?? 'N/A' }}
                        </span>
                    </div>

                    <!-- Fila 2: Nombre Completo y Estudios Realizados -->
                    <div class="col-md-8 d-flex flex-column justify-content-start" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Nombre Completo</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            {{ $datos->persona->primer_nombre ?? '' }} {{ $datos->persona->segundo_nombre ?? '' }} {{ $datos->persona->primer_apellido ?? '' }} {{ $datos->persona->segundo_apellido ?? '' }}
                        </span>
                    </div>
                    <div class="col-md-4 d-flex flex-column justify-content-start" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Estudios Realizados</span>
                        <div style="margin-top: 0; display: inline-block;">
                            @forelse ($datos->detalleEstudios as $est)
                                <span class="badge badge-primary">
                                    <i class="fas fa-graduation-cap me-1"></i>{{ $est->estudiosRealizado->estudios }}
                                </span>
                            @empty
                                <span class="text-muted fst-italic" style="font-size: 13px;">Sin registrar</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Separador Sutil -->
                <div style="border-bottom: 1px solid #E5E7EB; margin-bottom: 16px; margin-top: 4px;"></div>

                {{-- Sección 2: Información de Contacto --}}
                <div class="text-uppercase fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 11px; color: #64748B; letter-spacing: 0.5px;">
                    <i class="fas fa-phone-alt text-primary"></i>
                    <span>Información de Contacto</span>
                </div>
                <div class="row align-items-start">
                    <div class="col-md-6" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Correo Electrónico</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            <i class="fas fa-envelope text-muted me-1"></i>
                            {{ $datos->persona->email ?? 'Sin registrar' }}
                        </span>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Teléfono(s)</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            <i class="fas fa-phone text-muted me-1"></i>
                            {{ $datos->persona->telefono_completo ?? 'Sin registrar' }}
                            @if ($datos->persona->telefono_dos_completo)
                                <span class="text-muted fw-normal" style="font-size: 13px;">/ {{ $datos->persona->telefono_dos_completo }}</span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Separador Sutil -->
                <div style="border-bottom: 1px solid #E5E7EB; margin-bottom: 16px; margin-top: 4px;"></div>

                {{-- Sección 3: Dependencia y Horas --}}
                <div class="text-uppercase fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 11px; color: #64748B; letter-spacing: 0.5px;">
                    <i class="fas fa-building text-primary"></i>
                    <span>Información de Dependencia</span>
                </div>
                <div class="row align-items-start">
                    <div class="col-md-4 d-flex flex-column justify-content-start" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Código</span>
                        <div style="margin-top: 0; display: inline-block;">
                            <span class="badge badge-primary">
                                {{ $datos->codigo ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-5 d-flex flex-column justify-content-start" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Dependencia</span>
                        <span class="d-block" style="font-size: 15px; font-weight: 600; color: #1E293B;">
                            {{ $datos->dependencia ?? 'Sin especificar' }}
                        </span>
                    </div>
                    <div class="col-md-3 d-flex flex-column justify-content-start" style="margin-bottom: 12px;">
                        <span class="d-block mb-1" style="font-size: 13px; color: #64748B;">Horas Académicas</span>
                        <div style="margin-top: 0; display: inline-block;">
                            <span class="badge badge-primary">
                                <i class="fas fa-clock me-1"></i>{{ $datos->horas_academicas ?? '0' }} horas
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Separador Sutil -->
                <div style="border-bottom: 1px solid #E5E7EB; margin-bottom: 16px; margin-top: 4px;"></div>

                {{-- Sección 4: No Disponibilidad Horaria --}}
                <div class="text-uppercase fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 11px; color: #64748B; letter-spacing: 0.5px;">
                    <i class="fas fa-calendar-times text-primary"></i>
                    <span>No Disponibilidad Horaria</span>
                </div>

                @if($datos->noDisponibilidades->count() > 0)
                    <div class="row g-2">
                        @foreach($datos->noDisponibilidades as $noDisponibilidad)
                            <div class="col-md-6">
                                <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                                    <div>
                                        <span class="fw-semibold text-dark d-block" style="font-size: 14px;">
                                            <i class="fas fa-calendar-day text-primary me-1"></i>
                                            {{ $noDisponibilidad->diaSemana->nombre_dia ?? 'N/A' }}
                                        </span>
                                        <small class="text-muted" style="font-size: 12px;">
                                            {{ $noDisponibilidad->bloqueHorario->hora_inicio ?? 'N/A' }} - {{ $noDisponibilidad->bloqueHorario->hora_fin ?? 'N/A' }}
                                        </small>
                                    </div>
                                    @if($noDisponibilidad->motivo)
                                        <span class="badge" style="background-color: #F1F5F9; color: #475569; font-size: 11px;">
                                            {{ $noDisponibilidad->motivo }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Estado Vacío Compacto (16px padding) --}}
                    <div class="d-flex align-items-center justify-content-center gap-2 rounded text-center"
                         style="background-color: #F8FAFC; border: 1px dashed #CBD5E1; padding: 16px;">
                        <i class="fas fa-calendar-check text-muted" style="font-size: 1rem;"></i>
                        <span class="text-muted" style="font-size: 13px;">Sin horarios no disponibles registrados</span>
                    </div>
                @endif

            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-end align-items-center px-4 py-3 bg-white border-top" style="border-top-color: #F1F5F9 !important; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </div>
    </div>
</div>
