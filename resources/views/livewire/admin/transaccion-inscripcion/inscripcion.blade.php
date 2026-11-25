<div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (payload) => {
                const data = payload[0];

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    </script>
    <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3>Seleccionar Representantes</h3>
                    <p>Debe seleccionar al menos un representante</p>
                </div>
            </div>
        </div>
        <div class="card-body-modern" style="padding: 2rem;">
            {{-- Selectores de Representantes en CSS Grid de 3 columnas --}}
            <div class="mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div wire:ignore>
                    <label for="padre_select" class="form-label-modern">
                        <i class="fas fa-male text-primary me-1"></i>
                        Padre
                    </label>
                    <select id="padre_select" class="form-select selectpicker select-modern" data-live-search="true"
                        data-size="8" data-width="100%">
                        <option value="">Seleccione al padre (opcional)</option>
                        @foreach ($padres as $padre)
                            <option value="{{ $padre['id'] }}"
                                data-subtext="{{ $padre['tipo_documento'] }}-{{ $padre['numero_documento'] }}">
                                {{ $padre['nombre_completo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div wire:ignore>
                    <label for="madre_select" class="form-label-modern">
                        <i class="fas fa-female text-danger me-1"></i>
                        Madre
                    </label>
                    <select id="madre_select" class="form-select selectpicker select-modern" data-live-search="true"
                        data-size="8" data-width="100%">
                        <option value="">Seleccione a la madre (opcional)</option>
                        @foreach ($madres as $madre)
                            <option value="{{ $madre['id'] }}"
                                data-subtext="{{ $madre['tipo_documento'] }}-{{ $madre['numero_documento'] }}">
                                {{ $madre['nombre_completo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div wire:ignore>
                    <label for="representante_legal_select" class="form-label-modern">
                        <i class="fas fa-gavel text-warning me-1"></i>
                        Representante Legal
                        <span class="required-badge">*</span>
                    </label>
                    <select id="representante_legal_select" class="form-select selectpicker select-modern"
                        data-live-search="true" data-size="8" data-width="100%">
                        <option value="">Seleccione un representante legal</option>
                        @foreach ($representantes as $rep)
                            <option value="{{ $rep['id'] }}"
                                data-subtext="{{ $rep['tipo_documento'] }}-{{ $rep['numero_documento'] }}">
                                {{ $rep['nombre_completo'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('representanteLegalId')
                        <div class="invalid-feedback-modern">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- Fila horizontal: Alerta y Botón + Crear Representante --}}
            <div class="d-flex justify-content-between align-items-center gap-3 mt-3 mb-3">
                <div class="alert alert-info d-flex align-items-center flex-grow-1 mb-0 py-2 px-3" role="alert"
                    style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; color: #1E40AF; min-height: 44px;">
                    <i class="fas fa-info-circle me-2 flex-shrink-0" style="color: #3B82F6; font-size: 1.15rem;"></i>
                    <span style="font-size: 0.9rem; color: #1E3A8A;">
                        <strong style="font-weight: 600;">Atención:</strong> Si el representante no existe, puede crearlo ahora.
                    </span>
                </div>
                <div class="flex-shrink-0">
                    <button type="button" onclick="confirmarEnvio()" class="btn-primary-modern text-nowrap"
                        style="height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fas fa-plus me-1"></i> Crear Representante
                    </button>
                </div>
            </div>
            @php
                // Determinar si el Representante Legal es la misma persona que el Padre o la Madre
                $repLegalEsPadre = $padreSeleccionado && $representanteLegalSeleccionado && (
                    $representanteLegalSeleccionado->representante_id == $padreSeleccionado->id ||
                    ($representanteLegalSeleccionado->representante && $representanteLegalSeleccionado->representante->persona_id == $padreSeleccionado->persona_id)
                );
                $repLegalEsMadre = $madreSeleccionado && $representanteLegalSeleccionado && (
                    $representanteLegalSeleccionado->representante_id == $madreSeleccionado->id ||
                    ($representanteLegalSeleccionado->representante && $representanteLegalSeleccionado->representante->persona_id == $madreSeleccionado->persona_id)
                );
            @endphp

            @if ($padreSeleccionado)
                <div class="summary-card-readonly p-4 mt-3 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-male text-primary fs-5"></i>
                            <h5 class="m-0 fw-bold" style="color: #1E293B; font-size: 1rem;">Ficha del Padre</h5>
                            @if ($repLegalEsPadre)
                                <span class="badge bg-success text-white px-2 py-1 ms-1" style="font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-user-shield me-1"></i> REPRESENTANTE LEGAL
                                </span>
                            @endif
                        </div>
                        <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 11px; font-weight: 600;">
                            <i class="fas fa-lock me-1"></i> SOLO LECTURA
                        </span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-id-badge"></i>
                                <span>Datos de Identificación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Documento</span>
                                    <span class="data-value">
                                        {{ $padreSeleccionado->persona->tipoDocumento->nombre ?? 'N/A' }} -
                                        {{ $padreSeleccionado->persona->numero_documento }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-user"></i>
                                <span>Información Personal</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Nombre Completo</span>
                                    <span class="data-value">
                                        {{ $padreSeleccionado->persona->primer_nombre }}
                                        {{ $padreSeleccionado->persona->segundo_nombre }}
                                        {{ $padreSeleccionado->persona->tercer_nombre }}
                                        {{ $padreSeleccionado->persona->primer_apellido }}
                                        {{ $padreSeleccionado->persona->segundo_apellido }}
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Género</span>
                                    <span class="data-value">{{ $padreSeleccionado->persona->genero->genero ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Teléfono</span>
                                    <span class="data-value">{{ $padreSeleccionado->persona->telefono_completo ?? 'N/A' }}</span>
                                </div>
                                @if ($padreSeleccionado->persona->telefono_dos_completo)
                                    <div class="col-sm-6">
                                        <span class="data-label">Segundo Teléfono</span>
                                        <span class="data-value">{{ $padreSeleccionado->persona->telefono_dos_completo }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Ubicación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Estado / Municipio / Localidad</span>
                                    <span class="data-value">
                                        {{ $padreSeleccionado->estado->nombre_estado ?? 'N/A' }},
                                        {{ $padreSeleccionado->municipios->nombre_municipio ?? 'N/A' }},
                                        {{ $padreSeleccionado->localidads->nombre_localidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-briefcase"></i>
                                <span>Información Laboral</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Ocupación</span>
                                    <span class="data-value">{{ $padreSeleccionado->ocupacion->nombre_ocupacion ?? 'N/A' }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="data-label">Convive con el Estudiante</span>
                                    <span class="data-value">{{ $padreSeleccionado->convivenciaestudiante_representante ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($madreSeleccionado)
                <div class="summary-card-readonly p-4 mt-3 mb-3" style="border-left-color: #EC4899 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-female fs-5" style="color: #EC4899 !important;"></i>
                            <h5 class="m-0 fw-bold" style="color: #1E293B; font-size: 1rem;">Ficha de la Madre</h5>
                            @if ($repLegalEsMadre)
                                <span class="badge bg-success text-white px-2 py-1 ms-1" style="font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-user-shield me-1"></i> REPRESENTANTE LEGAL
                                </span>
                            @endif
                        </div>
                        <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 11px; font-weight: 600;">
                            <i class="fas fa-lock me-1"></i> SOLO LECTURA
                        </span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-id-badge" style="color: #EC4899 !important;"></i>
                                <span>Datos de Identificación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Documento</span>
                                    <span class="data-value">
                                        {{ $madreSeleccionado->persona->tipoDocumento->nombre ?? 'N/A' }} -
                                        {{ $madreSeleccionado->persona->numero_documento }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-user" style="color: #EC4899 !important;"></i>
                                <span>Información Personal</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Nombre Completo</span>
                                    <span class="data-value">
                                        {{ $madreSeleccionado->persona->primer_nombre }}
                                        {{ $madreSeleccionado->persona->segundo_nombre }}
                                        {{ $madreSeleccionado->persona->tercer_nombre }}
                                        {{ $madreSeleccionado->persona->primer_apellido }}
                                        {{ $madreSeleccionado->persona->segundo_apellido }}
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Género</span>
                                    <span class="data-value">{{ $madreSeleccionado->persona->genero->genero ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Teléfono</span>
                                    <span class="data-value">{{ $madreSeleccionado->persona->telefono_completo ?? 'N/A' }}</span>
                                </div>
                                @if ($madreSeleccionado->persona->telefono_dos_completo)
                                    <div class="col-sm-6">
                                        <span class="data-label">Segundo Teléfono</span>
                                        <span class="data-value">{{ $madreSeleccionado->persona->telefono_dos_completo }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-map-marker-alt" style="color: #EC4899 !important;"></i>
                                <span>Ubicación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Estado / Municipio / Localidad</span>
                                    <span class="data-value">
                                        {{ $madreSeleccionado->estado->nombre_estado ?? 'N/A' }},
                                        {{ $madreSeleccionado->municipios->nombre_municipio ?? 'N/A' }},
                                        {{ $madreSeleccionado->localidads->nombre_localidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-briefcase" style="color: #EC4899 !important;"></i>
                                <span>Información Laboral</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Ocupación</span>
                                    <span class="data-value">{{ $madreSeleccionado->ocupacion->nombre_ocupacion ?? 'N/A' }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="data-label">Convive con el Estudiante</span>
                                    <span class="data-value">{{ $madreSeleccionado->convivenciaestudiante_representante ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Mostrar Ficha del Representante Legal SOLO si es alguien distinto al padre y a la madre --}}
            @if ($representanteLegalSeleccionado && !$repLegalEsPadre && !$repLegalEsMadre)
                <div class="summary-card-readonly p-4 mt-3 mb-3" style="border-left-color: #10B981 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: #E2E8F0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-user-shield fs-5" style="color: #10B981 !important;"></i>
                            <h5 class="m-0 fw-bold" style="color: #1E293B; font-size: 1rem;">Ficha del Representante Legal</h5>
                        </div>
                        <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 11px; font-weight: 600;">
                            <i class="fas fa-lock me-1"></i> SOLO LECTURA
                        </span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-id-badge" style="color: #10B981 !important;"></i>
                                <span>Datos de Identificación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Documento</span>
                                    <span class="data-value">
                                        {{ $representanteLegalSeleccionado->representante->persona->tipoDocumento->nombre ?? 'N/A' }} -
                                        {{ $representanteLegalSeleccionado->representante->persona->numero_documento }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-user" style="color: #10B981 !important;"></i>
                                <span>Información Personal</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Nombre Completo</span>
                                    <span class="data-value">
                                        {{ $representanteLegalSeleccionado->representante->persona->primer_nombre }}
                                        {{ $representanteLegalSeleccionado->representante->persona->segundo_nombre }}
                                        {{ $representanteLegalSeleccionado->representante->persona->tercer_nombre }}
                                        {{ $representanteLegalSeleccionado->representante->persona->primer_apellido }}
                                        {{ $representanteLegalSeleccionado->representante->persona->segundo_apellido }}
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Género</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->representante->persona->genero->genero ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="data-label">Teléfono</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->representante->persona->telefono_completo ?? 'N/A' }}</span>
                                </div>
                                @if ($representanteLegalSeleccionado->representante->persona->telefono_dos_completo)
                                    <div class="col-sm-6">
                                        <span class="data-label">Segundo Teléfono</span>
                                        <span class="data-value">{{ $representanteLegalSeleccionado->representante->persona->telefono_dos_completo }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-subheading">
                                <i class="fas fa-map-marker-alt" style="color: #10B981 !important;"></i>
                                <span>Ubicación</span>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <span class="data-label">Estado / Municipio / Localidad</span>
                                    <span class="data-value">
                                        {{ $representanteLegalSeleccionado->representante->estado->nombre_estado ?? 'N/A' }},
                                        {{ $representanteLegalSeleccionado->representante->municipios->nombre_municipio ?? 'N/A' }},
                                        {{ $representanteLegalSeleccionado->representante->localidads->nombre_localidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="section-subheading">
                                <i class="fas fa-briefcase" style="color: #10B981 !important;"></i>
                                <span>Información Laboral</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <span class="data-label">Ocupación</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->representante->ocupacion->nombre_ocupacion ?? 'N/A' }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="data-label">Convive con el Estudiante</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->representante->convivenciaestudiante_representante ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Información Legal y Bancaria -->
                        <div class="col-12 pt-3 border-top" style="border-color: #E2E8F0 !important;">
                            <div class="section-subheading">
                                <i class="fas fa-gavel" style="color: #10B981 !important;"></i>
                                <span>Información Legal y Bancaria</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Parentesco</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->parentesco ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Correo Electrónico</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->correo_representante ?? 'N/A' }}</span>
                                </div>
                                @if ($representanteLegalSeleccionado->pertenece_a_organizacion_representante)
                                    <div class="col-sm-6 col-md-3">
                                        <span class="data-label">Organización</span>
                                        <span class="data-value">{{ $representanteLegalSeleccionado->cual_organizacion_representante ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Carnet de la Patria</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->carnet_patria_afiliado ? 'Sí' : 'No' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Serial Carnet Patria</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->serial_carnet_patria_representante ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Código Carnet Patria</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->codigo_carnet_patria_representante ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Banco</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->banco->codigo_banco ?? 'N/A' }} - {{ $representanteLegalSeleccionado->banco->nombre_banco ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <span class="data-label">Tipo de Cuenta</span>
                                    <span class="data-value">{{ $representanteLegalSeleccionado->tipo_cuenta ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <script>
                function confirmarEnvio() {
                    Swal.fire({
                        title: '¿Desea crear un nuevo representante?',
                        text: 'Esta acción no guardará la información actual.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, crear representante',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('representante.formulario', ['from' => 'inscripcion']) }}";
                        }
                    });
                }
            </script>
        </div>
    </div>
    <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3>Seleccionar el Nivel Academico</h3>
                    <p>Seleccione el nivel academico al que desea inscribir al estudiante</p>
                </div>
            </div>
        </div>
        <div class="card-body-modern" style="padding: 2rem;">
            <div class="mb-3" style="display: grid; grid-template-columns: {{ $esPrimerGrado ? '1fr 1fr' : 'repeat(3, 1fr)' }}; gap: 24px;">
                <!-- Campo Nivel Académico -->
                <div class="floating-group">
                    <label for="grado_id">
                        Nivel Académico <span class="text-danger">*</span>
                    </label>
                    <select wire:model.live="gradoId" id="grado_id"
                        class="form-select @error('gradoId') is-invalid @enderror">
                        <option value="">Seleccione un nivel académico</option>
                        @foreach ($grados as $grado)
                            <option value="{{ $grado->id }}">{{ $grado->numero_grado }}°</option>
                        @endforeach
                    </select>
                    @error('gradoId')
                        <div class="invalid-feedback-modern">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                @if (!$esPrimerGrado)
                    <!-- Campo Sección -->
                    <div class="floating-group">
                        <label for="seccion_id">
                            Sección <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="seccion_id" id="seccion_id"
                            class="form-select @error('seccion_id') is-invalid @enderror">
                            <option value="">Seleccione una sección</option>
                            @foreach ($secciones as $seccion)
                                <option value="{{ $seccion->id }}">
                                    {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('seccion_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                @endif

                <!-- Campo Fecha de Inscripción -->
                <div class="floating-group">
                    <label for="fecha">
                        Fecha de inscripción <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" value="{{ now('America/Caracas')->format('Y-m-d\TH:i') }}"
                        id="fecha" placeholder=" " class="form-control @error('fecha') is-invalid @enderror" readonly disabled>
                    @error('fecha')
                        <div class="invalid-feedback-modern">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-4 d-flex align-items-center p-3"
        style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; color: #1E40AF;">
        <i class="fas fa-info-circle me-2 flex-shrink-0" style="color: #3B82F6;"></i>
        <span>Los campos con <span class="text-danger" style="font-weight: 700;">(*)</span> son obligatorios</span>
    </div>

    @livewire('admin.alumnos.alumno-create')

    <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h3>Plantel de Procedencia</h3>
                    <p>Información de la institución de origen</p>
                </div>
            </div>
        </div>
        <div class="card-body-modern" style="padding: 2rem;">
            @if (!$esPrimerGrado)
                <div class="alert alert-info mb-4 d-flex align-items-center p-3"
                    style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; color: #1E40AF;">
                    <i class="fas fa-info-circle me-2 flex-shrink-0" style="color: #3B82F6;"></i>
                    <span>Seleccione el <b>Liceo</b> del que proviene el estudiante</span>
                </div>
            @else
                <div class="alert alert-info mb-4 d-flex align-items-center p-3"
                    style="background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; color: #1E40AF;">
                    <i class="fas fa-info-circle me-2 flex-shrink-0" style="color: #3B82F6;"></i>
                    <span>Seleccione la <b>Escuela</b> de la que proviene el estudiante</span>
                </div>
            @endif

            <!-- Fila 1: Ubicación Geográfica del Plantel (4 columnas) -->
            <div class="mb-3" style="display: grid; grid-template-columns: repeat({{ $esVenezolano ? 4 : 1 }}, 1fr); gap: 16px;">
                <div class="floating-group mb-0">
                    <label for="paisId_procedencia">País <span class="text-danger">*</span></label>
                    <select wire:model.live="paisId" id="paisId_procedencia" placeholder=" "
                        class="form-select @error('paisId') is-invalid @enderror">
                        <option value="">Seleccione un país</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais->id }}">{{ $pais->nameES }}</option>
                        @endforeach
                    </select>
                    @error('paisId')
                        <div class="invalid-feedback-modern">{{ $message }}</div>
                    @enderror
                </div>

                @if ($esVenezolano)
                    <div class="floating-group mb-0">
                        <label for="estado_id_procedencia">Estado <span class="text-danger">*</span></label>
                        <select wire:model.live="estado_id" id="estado_id_procedencia" placeholder=" "
                            @disabled(!$paisId)
                            class="form-select @error('estado_id') is-invalid @enderror">
                            <option value="">Seleccione un estado</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre_estado }}</option>
                            @endforeach
                        </select>
                        @error('estado_id')
                            <div class="invalid-feedback-modern">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="floating-group mb-0">
                        <label for="municipio_id_procedencia">Municipio <span class="text-danger">*</span></label>
                        <select wire:model.live="municipio_id" id="municipio_id_procedencia" placeholder=" "
                            @disabled(!$estado_id)
                            class="form-select @error('municipio_id') is-invalid @enderror">
                            <option value="">Seleccione un municipio</option>
                            @foreach ($municipios as $municipio)
                                <option value="{{ $municipio->id }}">{{ $municipio->nombre_municipio }}</option>
                            @endforeach
                        </select>
                        @error('municipio_id')
                            <div class="invalid-feedback-modern">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="floating-group mb-0">
                        <label for="localidad_id_procedencia">Localidad <span class="text-danger">*</span></label>
                        <select wire:model.live="localidad_id" id="localidad_id_procedencia" placeholder=" "
                            @disabled(!$municipio_id)
                            class="form-select @error('localidad_id') is-invalid @enderror">
                            <option value="">Seleccione una localidad</option>
                            @foreach ($localidades as $localidad)
                                <option value="{{ $localidad->id }}">{{ $localidad->nombre_localidad }}</option>
                            @endforeach
                        </select>
                        @error('localidad_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Fila 2: Datos de la Institución y Egreso -->
            <div class="mb-3" style="display: grid; grid-template-columns: {{ $esPrimerGrado ? 'repeat(4, 1fr)' : 'repeat(3, 1fr)' }}; gap: 16px;">
                @if ($esPrimerGrado)
                    <div class="floating-group mb-0">
                        <label for="numero_zonificacion">Número Zonificación</label>
                        <input type="text" wire:model.live="numero_zonificacion" id="numero_zonificacion"
                            class="form-control" maxlength="3" inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g,'')"
                            placeholder=" ">
                    </div>
                @endif

                <div>
                    <div class="floating-group mb-0">
                        <label for="institucion_procedencia_id">
                            Institución <span class="text-danger">*</span>
                        </label>
                        @if ($esVenezolano)
                            <select wire:model.live="institucion_procedencia_id" id="institucion_procedencia_id" placeholder=" "
                                class="form-select @error('institucion_procedencia_id') is-invalid @enderror">
                                <option value="">Seleccione</option>
                                @foreach ($instituciones as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->nombre_institucion }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" class="form-control"
                                wire:model.live="otroPaisNombre" id="institucion_procedencia_id"
                                placeholder=" ">
                        @endif
                        @error('institucion_procedencia_id')
                            <div class="invalid-feedback-modern">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    @if ($esVenezolano)
                        <small class="form-text-modern d-flex align-items-center gap-1 mt-1">
                            <i class="fas fa-plus-circle text-primary"></i>
                            <a href="#" class="text-primary text-decoration-none"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCrearInstitucionInscripcion">
                                Crear nueva institución
                            </a>
                        </small>
                    @endif
                </div>

                <div class="floating-group mb-0">
                    <label for="expresion_literaria">
                        Literal <span class="text-danger">*</span>
                    </label>
                    <select wire:model.live="expresion_literaria_id" id="expresion_literaria" placeholder=" "
                        class="form-select @error('expresion_literaria_id') is-invalid @enderror">
                        <option value="">Seleccione</option>
                        @foreach ($expresiones_literarias as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->letra_expresion_literaria }}
                            </option>
                        @endforeach
                    </select>
                    @error('expresion_literaria_id')
                        <div class="invalid-feedback-modern">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="floating-group mb-0">
                    <label for="anio_egreso">
                        Año de Egreso <span class="text-danger">*</span>
                    </label>
                    <input type="date" wire:model.live="anio_egreso" id="anio_egreso" placeholder=" "
                        class="form-control @error('anio_egreso') is-invalid @enderror">
                    @error('anio_egreso')
                        <div class="invalid-feedback-modern">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <i class="fas fa-wheelchair"></i>
                </div>
                <div>
                    <h3>Discapacidades</h3>
                    <p>Agregue las discapacidades que presente el estudiante (opcional)</p>
                </div>
            </div>
        </div>
        <div class="card-body-modern" style="padding: 2rem;">
            @if (session()->has('success_temp'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success_temp') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="mb-4" style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                <div>
                    <label for="discapacidad_select" class="form-label-modern">
                        <i class="fas fa-list me-1"></i>
                        ¿Presenta alguna discapacidad?
                    </label>
                    <select wire:model.defer="discapacidadSeleccionada" id="discapacidad_select"
                        class="form-select form-control-modern @error('discapacidadSeleccionada') is-invalid @enderror">
                        <option value="">Seleccione una discapacidad</option>
                        @foreach ($discapacidades as $discapacidad)
                            <option value="{{ $discapacidad->id }}">
                                {{ $discapacidad->nombre_discapacidad }}
                            </option>
                        @endforeach
                    </select>
                    @error('discapacidadSeleccionada')
                        <div class="invalid-feedback-modern" style="display: block;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div>
                    <button type="button" wire:click="agregarDiscapacidad" class="btn-primary-modern text-nowrap"
                        style="height: 42px; min-width: 140px;"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="agregarDiscapacidad">
                            <i class="fas fa-plus me-1"></i> Agregar
                        </span>
                        <span wire:loading wire:target="agregarDiscapacidad">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </div>
            @if (!empty($discapacidadesAgregadas))
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center;">#</th>
                                <th>Discapacidad</th>
                                <th style="width: 120px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($discapacidadesAgregadas as $index => $discapacidad)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <span class="badge number-badge">{{ $index + 1 }}</span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-wheelchair text-primary"></i>
                                            <strong>{{ $discapacidad['nombre'] }}</strong>
                                        </div>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button"
                                            wire:click="eliminarDiscapacidad({{ $index }})"
                                            class="btn btn-sm btn-danger" wire:loading.attr="disabled"
                                            title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="card-modern mb-4" id="bloque-documentos" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
        <div class="card-header-modern">
            <div class="header-left">
                <div class="header-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <h3>Documentos Entregados</h3>
                    <p>Marque los documentos que el estudiante ha entregado</p>
                </div>
            </div>
            <div class="header-right">
                @if ($estadoDocumentos === 'Completos')
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle"></i> Completos
                    </span>
                @elseif($estadoDocumentos === 'Incompletos')
                    <span class="badge bg-warning">
                        <i class="fas fa-exclamation-circle"></i> Incompletos
                    </span>
                @else
                    <span class="badge bg-secondary">
                        <i class="fas fa-clock"></i> Pendientes
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body-modern" style="padding: 2rem;">
            <!-- Checkbox Seleccionar Todos (arriba, solo, ocupando toda la fila) -->
            <div class="mb-4 pb-2 border-bottom" style="border-color: #E2E8F0 !important;">
                <div class="form-check d-flex align-items-center">
                    <input type="checkbox" id="seleccionar_todos" wire:model.live="seleccionarTodos"
                        class="form-check-input" style="cursor: pointer; width: 1.2rem; height: 1.2rem; margin-top: 0;">
                    <label for="seleccionar_todos" class="form-check-label ms-2 mb-0 fw-semibold" style="cursor: pointer; font-size: 0.95rem; color: #1E293B;">
                        Seleccionar todos los documentos
                    </label>
                </div>
            </div>

            @php
                // Definir documentos obligatorios
                $documentosObligatorios = [
                    'partida_nacimiento',
                    'boletin_6to_grado',
                    'certificado_calificaciones',
                    'constancia_aprobacion_primaria',
                ];
                
                // Para 2do grado en adelante
                if (!$esPrimerGrado) {
                    $documentosObligatorios[] = 'notas_certificadas';
                    $documentosObligatorios[] = 'liberacion_cupo';
                }

                // Agregar autorización si no hay padre ni madre
                $requiereAutorizacion = !$padreId && !$madreId;
                if ($requiereAutorizacion) {
                    $documentosObligatorios[] = 'autorizacion_tercero';
                }

                $documentosOpcionales = [
                    'copia_cedula_representante',
                    'copia_cedula_estudiante',
                    'foto_estudiante',
                    'foto_representante',
                    'carnet_vacunacion',
                ];

                $todosDocumentos = [
                    'partida_nacimiento' => 'Partida de Nacimiento',
                    'boletin_6to_grado' => 'Boletín de 6to Grado',
                    'notas_certificadas' => 'Notas Certificadas',
                    'liberacion_cupo' => 'Liberación de Cupo',
                    'certificado_calificaciones' => 'Certificado de Calificaciones',
                    'constancia_aprobacion_primaria' => 'Constancia de Aprobación Primaria',
                    'copia_cedula_representante' => 'Copia de Cédula del Representante',
                    'copia_cedula_estudiante' => 'Copia de Cédula del Estudiante',
                    'foto_estudiante' => 'Fotografía Tipo Carnet Del Estudiante',
                    'foto_representante' => 'Fotografía Tipo Carnet Del Representante',
                    'carnet_vacunacion' => 'Carnet de Vacunación Vigente',
                    'autorizacion_tercero' => 'Autorización Firmada (si inscribe un tercero)',
                ];
            @endphp

            <!-- SECCIÓN: DOCUMENTOS OBLIGATORIOS (2 Columnas con CSS Grid) -->
            <div class="mb-4">
                <h6 class="text-danger fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="fas fa-exclamation-circle"></i> Documentos Obligatorios
                </h6>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px;">
                    @foreach ($todosDocumentos as $documento => $etiqueta)
                        @if (in_array($documento, $documentosObligatorios))
                            @php
                                // Omitir notas_certificadas y liberacion_cupo para primer grado
                                if ($esPrimerGrado && in_array($documento, ['notas_certificadas', 'liberacion_cupo'])) {
                                    continue;
                                }
                                
                                $esFaltante = in_array($documento, $documentosFaltantes);
                                $estaSeleccionado = in_array($documento, $documentos);
                            @endphp
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" id="doc_{{ $documento }}" wire:model.live="documentos"
                                    value="{{ $documento }}" class="form-check-input"
                                    style="cursor: pointer; width: 1.15rem; height: 1.15rem; margin-top: 0;">
                                <label for="doc_{{ $documento }}" class="form-check-label ms-2 mb-0" style="cursor: pointer; font-size: 0.9rem; color: #334155;">
                                    <span class="{{ $esFaltante ? 'text-danger fw-bold' : '' }}">
                                        {{ $etiqueta }}
                                    </span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- SECCIÓN: DOCUMENTOS OPCIONALES (2 Columnas con CSS Grid) -->
            <div class="mb-4">
                <h6 class="text-primary fw-bold mb-3 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                    <i class="fas fa-info-circle"></i> Documentos Opcionales
                </h6>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px;">
                    @foreach ($todosDocumentos as $documento => $etiqueta)
                        @if (in_array($documento, $documentosOpcionales))
                            @php
                                $esFaltante = in_array($documento, $documentosFaltantes);
                                $estaSeleccionado = in_array($documento, $documentos);
                            @endphp
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" id="doc_{{ $documento }}" wire:model.live="documentos"
                                    value="{{ $documento }}" class="form-check-input"
                                    style="cursor: pointer; width: 1.15rem; height: 1.15rem; margin-top: 0;">
                                <label for="doc_{{ $documento }}" class="form-check-label ms-2 mb-0" style="cursor: pointer; font-size: 0.9rem; color: #334155;">
                                    <span>{{ $etiqueta }}</span>
                                </label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Observaciones -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-group">
                        <label for="observaciones" class="form-label-modern">
                            <i class="fas fa-comment"></i>
                            Observaciones
                        </label>
                        <textarea wire:model.live="observaciones" id="observaciones"
                            class="form-control form-control-modern @error('observaciones') is-invalid @enderror" rows="3"
                            placeholder="Agregue observaciones adicionales sobre la inscripción (opcional)" maxlength="500"></textarea>
                        @error('observaciones')
                            <div class="invalid-feedback-modern" style="display: block;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        <small class="form-text text-muted">
                            {{ strlen($observaciones ?? '') }}/500 caracteres
                        </small>
                    </div>
                </div>
            </div>

            <!-- Contrato -->
            <div class="row mt-3">
                <div class="col-md-6 ms-auto d-flex flex-column align-items-end gap-1">
                    <div class="form-check d-flex align-items-center">
                        <input type="checkbox" id="acepta_normas_contrato" wire:model.live="acepta_normas_contrato"
                            class="form-check-input" style="cursor: pointer; width: 1.15rem; height: 1.15rem; margin-top: 0;">
                        <label for="acepta_normas_contrato" class="form-check-label ms-2 mb-0"
                            style="cursor: pointer; font-size: 0.9rem; color: #334155;">
                            He leído y acepto
                            <a class="text-primary fw-semibold" style="cursor: pointer; text-decoration: none;"
                                data-bs-toggle="modal" data-bs-target="#modalContratoConvivencia"
                                title="Ver Detalles">
                                las normas de convivencia
                            </a>
                        </label>
                    </div>
                    @error('acepta_normas_contrato')
                        <div class="invalid-feedback-modern">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>

        @include('admin.transacciones.inscripcion.modales.showContratoModal')

        <div class="card-modern mb-4" style="border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #FFFFFF;">
            <div class="card-body-modern pb-4" style="padding: 1.5rem 2rem;">
                <div class="d-flex justify-content-end align-items-center gap-2">
                    <a href="{{ route('admin.transacciones.inscripcion.index') }}"
                        class="btn-secondary-modern">
                        <i class="fas fa-arrow-left me-1"></i>
                        Cancelar
                    </a>
                    <button type="button" wire:click="finalizar" class="btn-primary-modern" wire:loading.attr="disabled"
                        @if (!$acepta_normas_contrato) disabled @endif
                        @if ($gradoSinCupos) disabled @endif>
                        <span wire:loading.remove wire:target="finalizar">
                            <i class="fas fa-save me-1"></i>
                            Guardar
                        </span>
                        <span wire:loading wire:target="finalizar">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @livewire('admin.modales.institucion-procedencia-create')
    @livewire('admin.modales.localidad-create')
    @livewire('admin.modales.municipio-create')
    @livewire('admin.modales.estado-create')
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Eliminar cualquier backdrop atascado al cargar la página
            if (window.$) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');

                // Limpiar backdrop después de que cualquier modal termine su animación de cierre
                $(document).on('hidden.bs.modal', '.modal', function () {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                });
            } else {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            $('.selectpicker').selectpicker();

            $('#alumno_select').on('changed.bs.select', function() {
                let val = $(this).val();
                @this.set('alumnoId', val);
            });

            $('#padre_select').on('changed.bs.select', function() {
                let val = $(this).val();
                @this.set('padreId', val);
                Livewire.dispatch('padreSeleccionadoEvento', { value: val });
            });

            $('#madre_select').on('changed.bs.select', function() {
                let val = $(this).val();
                @this.set('madreId', val);
                Livewire.dispatch('madreSeleccionadoEvento', { value: val });
            });

            $('#representante_legal_select').on('changed.bs.select', function() {
                let val = $(this).val();
                @this.set('representanteLegalId', val);
                Livewire.dispatch('representanteLegalSeleccionadoEvento', { value: val });
            });
        });

        document.addEventListener('livewire:updated', function() {
            $('.selectpicker').selectpicker('refresh');
        });

        Livewire.on('resetSelects', () => {
            $('.selectpicker').val('').selectpicker('refresh');
        });

        // Actualizar select de Institución de Procedencia al crear una nueva desde el modal y cerrar backdrop
        Livewire.on('institucionCreada', (data) => {
            const item = Array.isArray(data) ? data[0] : data;
            if (!item) return;

            // 1. Cerrar el modal explícitamente y limpiar backdrop
            const modalEl = document.getElementById('modalCrearInstitucionInscripcion');
            if (modalEl) {
                if (window.$ && typeof $(modalEl).modal === 'function') {
                    $(modalEl).modal('hide');
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const m = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (m) m.hide();
                }
            }

            setTimeout(() => {
                if (window.$) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                } else {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }, 300);

            // 2. Actualizar y seleccionar la nueva institución en el <select> principal
            if (item.id) {
                const select = document.getElementById('institucion_procedencia_id');
                if (select) {
                    let opt = select.querySelector(`option[value="${item.id}"]`);
                    if (!opt && item.nombre) {
                        opt = new Option(item.nombre, item.id, true, true);
                        select.add(opt);
                    }
                    select.value = item.id;
                    select.dispatchEvent(new Event('change'));
                }
                if (window.$ && typeof $('#institucion_procedencia_id').val === 'function') {
                    $('#institucion_procedencia_id').val(item.id).trigger('change');
                    if (typeof $.fn.selectpicker === 'function') {
                        $('#institucion_procedencia_id').selectpicker('refresh');
                    }
                }
            }
        });

        Livewire.on('cerrarModal', () => {
            if (window.$) {
                $('.modal').modal('hide');
                setTimeout(() => {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                }, 300);
            }
        });
    </script>
@endpush
