<div class="modal fade" id="modalVer{{ $institucion->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-modern shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">

            {{-- Cabecera Compacta en Índigo --}}
            <div class="modal-header-create py-3 px-4 d-flex align-items-center" style="background: linear-gradient(135deg, var(--purple, #5b6ee1), var(--purple-dark, #4a5ad4)); border-bottom: none;">
                <div class="modal-icon-create" style="width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; margin: 0;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="ml-3 me-auto">
                    <h5 class="modal-title-create mb-0" style="font-size: 1.15rem; font-weight: 700; color: #ffffff;">Detalles del Plantel</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Información oficial registrada</small>
                </div>
                <button type="button" class="btn-close-modal text-white ml-auto" data-bs-dismiss="modal" aria-label="Cerrar" style="background: transparent; border: none; font-size: 1.1rem; opacity: 0.8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-create p-4">
                <div class="row align-items-center mb-4 pb-3 border-bottom">
                    <div class="col-auto">
                        @php
                            $logoExiste = !empty($institucion->logo_ruta) && file_exists(public_path($institucion->logo_ruta));
                        @endphp
                        @if($logoExiste)
                            <img src="{{ asset($institucion->logo_ruta) }}" alt="Logo" class="rounded shadow-sm" style="max-height: 75px; max-width: 75px; object-fit: contain; background: #fff; padding: 4px; border: 1px solid #e5e7eb;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-primary shadow-sm" style="width: 70px; height: 70px; background: rgba(91, 110, 225, 0.12); border: 1px solid rgba(91, 110, 225, 0.2);">
                                <i class="fas fa-school fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col">
                        <h4 class="mb-1 text-primary font-weight-bold" style="font-weight: 700;">{{ $institucion->nombre }}</h4>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge badge-light border text-dark mr-2" style="font-size: 0.85rem; padding: 0.35rem 0.6rem;">Código: <strong>{{ $institucion->codigo_plantel }}</strong></span>
                            @if($institucion->activo)
                                <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.35rem 0.6rem;"><i class="fas fa-check-circle mr-1"></i>Sede Activa</span>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.85rem; padding: 0.35rem 0.6rem;"><i class="fas fa-archive mr-1"></i>Inactiva</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-user-tie text-primary mr-1"></i>Director(a)</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">
                            @if($institucion->director)
                                {{ $institucion->director->nombre_completo }}
                                <small class="text-muted">(CI: {{ $institucion->director->numero_documento }})</small>
                                @if($institucion->director_encargado)
                                    <span class="badge badge-warning text-dark ml-1">Encargado(a)</span>
                                @endif
                            @else
                                <span class="text-muted font-italic">No asignado</span>
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-phone text-primary mr-1"></i>Teléfono de Contacto</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">{{ $institucion->telefono ?: 'No registrado' }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-map-marker-alt text-primary mr-1"></i>Dirección</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">{{ $institucion->direccion ?: 'No registrada' }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-globe text-primary mr-1"></i>Parroquia / Municipio / Estado</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">
                            @if($institucion->localidad)
                                {{ $institucion->localidad->nombre_localidad }}, {{ $institucion->localidad->municipio->nombre_municipio ?? '' }} ({{ $institucion->localidad->municipio->estado->nombre_estado ?? '' }})
                            @else
                                <span class="text-muted font-italic">No asignada</span>
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-compass text-primary mr-1"></i>Zona Educativa</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">{{ $institucion->zona_educativa ?: 'No registrada' }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-sitemap text-primary mr-1"></i>CDCEE</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">{{ $institucion->cdcee ?: 'No registrado' }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold mb-1 d-block"><i class="fas fa-building text-primary mr-1"></i>Dependencia</label>
                        <p class="h6 font-weight-normal mb-0 text-dark">{{ $institucion->dependencia ?: 'No registrada' }}</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer-create py-3 px-4 d-flex justify-content-end align-items-center border-top" style="background: #f9fafb;">
                <button type="button" class="btn btn-light btn-modal-cancel border px-4 py-2" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
