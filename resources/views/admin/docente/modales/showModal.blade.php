<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow">
            <div class="modal-header modal-header-view">
                <div class="w-100 text-center">
                    <h5 class="modal-title-view mb-2">Información del Docente</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body modal-body-view">
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        <span>Datos del Docente</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Cedula
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $datos->persona->numero_documento ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Fecha de Nacimiento
                                    </span>
                                    <span class="detail-value">
                                        {{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                        <small
                                            class="text-muted">({{ \Carbon\Carbon::parse($datos->persona->fecha_nacimiento)->age }}
                                            años)</small>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Nombre Completo
                                    </span>
                                    <span class="detail-value fw-bold">
                                        {{ $datos->persona->primer_nombre ?? '' }}
                                        {{ $datos->persona->segundo_nombre ?? '' }}
                                        {{ $datos->persona->primer_apellido ?? '' }}
                                        {{ $datos->persona->segundo_apellido ?? '' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Género
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->persona->genero->genero ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">Estudios</span>
                                    <span class="detail-value">
                                        @forelse ($datos->detalleEstudios as $est)
                                            • {{ $est->estudiosRealizado->estudios }} <br>
                                        @empty
                                            Sin estudios registrados
                                        @endforelse
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="section-title">
                        <i class="fas fa-phone"></i>
                        <span>Informacion de Contacto</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Correo Electronico
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->persona->email ?? 'Sin registrar' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 mt-3">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Telefono
                                    </span>
                                    <span class="detail-value">
                                        @if ($datos->persona->telefono_completo)
                                            {{ $datos->persona->telefono_completo }}
                                        @else
                                            Sin registrar
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @if ($datos->persona->telefono_dos_completo)
                                <div class="col-md-3 mt-3">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Telefono
                                        </span>
                                        <span class="detail-value">
                                            @if ($datos->persona->telefono_dos_completo)
                                                {{ $datos->persona->telefono_dos_completo }}
                                            @else
                                                Sin registrar
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="section-title">
                        <i class="fas fa-building"></i>
                        <span>Informacion de dependencia</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Codigo
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->codigo ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Dependencia
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->dependencia ?? 'Sin especificar' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Horas academicas
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->horas_academicas ?? 'Sin especificar' }}
                                    </span>
                                </div>
                            </div
                        </div>
                    </div>
                </div>
                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
            </div>
            <div class="modal-footer modal-footer-view">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>