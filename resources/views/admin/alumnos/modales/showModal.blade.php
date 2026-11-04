<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow">
            @php
                $alumno = $datos;
                $persona = $alumno->persona;
            @endphp
            <div class="modal-header modal-header-view">
                <div class="w-100 text-center">
                    <h5 class="modal-title-view mb-2">Información del Alumno</h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body modal-body-view">
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        <span>Datos Personales</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Cedula
                                    </span>
                                    <span class="detail-value">
                                        {{ $persona?->tipoDocumento->nombre ?? 'N/A' }}-{{ $persona?->numero_documento ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Fecha de Nacimiento
                                    </span>
                                    <span class="detail-value">
                                        {{ \Carbon\Carbon::parse($persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                        <small
                                            class="text-muted">({{ \Carbon\Carbon::parse($persona?->fecha_nacimiento)->age }}
                                            años)</small>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Género
                                    </span>
                                    <span class="detail-value">
                                        {{ $persona?->genero->genero ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Nombre Completo
                                    </span>
                                    <span class="detail-value fw-bold">
                                        {{ $persona?->primer_nombre ?? '' }}
                                        {{ $persona?->segundo_nombre ?? '' }}
                                        {{ $persona?->tercer_nombre ?? '' }}
                                        {{ $persona?->primer_apellido ?? '' }}
                                        {{ $persona?->segundo_apellido ?? '' }}
                                    </span>
                                </div>
                            </div>
                            @if(!auth()->user()?->esSoloDocente())
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Lugar de nacimiento
                                    </span>

                                    <span class="detail-value fw-bold">
                                        @php
                                            $pais = $persona?->localidad?->municipio?->estado?->pais?->nameES;
                                        @endphp

                                        {{-- Mostrar país SOLO si NO es Venezuela --}}
                                        @if ($pais && strtolower($pais) !== 'venezuela')
                                            {{ $pais }} /
                                        @endif

                                        {{ $persona?->localidad?->municipio?->estado?->nombre_estado ?? 'N/A' }}
                                        /
                                        {{ $persona?->localidad?->municipio?->nombre_municipio ?? 'N/A' }}
                                        /
                                        {{ $persona?->localidad?->nombre_localidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Orden de Nacimiento
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->ordenNacimiento->orden_nacimiento ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            @endif

                        </div>
                        <div class="row">
                            @if (!auth()->user()?->esSoloDocente() && $alumno?->etniaIndigena)
                                <div class="col-md-6 mt-3">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Etnia Indígena
                                        </span>
                                        @if ($alumno?->etniaIndigena)
                                            <div class="d-flex flex-wrap mt-1">
                                                {{ $alumno?->etniaIndigena->nombre }}
                                            </div>
                                        @else
                                            <span class="detail-value text-muted">
                                                Ninguna registrada
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($alumno?->discapacidades)
                                <div class="col-md-6 mt-3">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Discapacidades
                                        </span>
                                        @if ($alumno?->discapacidades->count() > 0)
                                            <div class="d-flex flex-wrap mt-1">
                                                @foreach ($alumno?->discapacidades as $discapacidad)
                                                    • {{ $discapacidad->nombre_discapacidad }} <br>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="detail-value text-muted">
                                                Ninguna registrada
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!auth()->user()?->esSoloDocente())
                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">

                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        <span>Datos Fisicos</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Peso
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->peso ?? 'N/A' }} kg
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Estatura
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->estatura ?? 'N/A' }} cm
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Lateralidad
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->lateralidad->lateralidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Zapato
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->talla_zapato ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Camisa
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->tallaCamisa->nombre ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Pantalones
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno?->tallaPantalon->nombre ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer modal-footer-view">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
