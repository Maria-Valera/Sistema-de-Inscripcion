<div>
    @if (session()->has('success') || session()->has('error') || session()->has('warning'))
        <div class="alerts-container mb-3">
            @if (session()->has('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if (session()->has('warning'))
                <div class="alert-modern alert-warning alert alert-dismissible fade show">
                    <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="alert-content">
                        <h4>Advertencia</h4>
                        <p>{{ session('warning') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
    @endif
    @if (!$alumnoSeleccionado)
        <div class="card-modern card-step-blue mb-4 shadow-sm" style="overflow: hidden; border-radius: 12px;">
            <div class="card-header-modern card-header-pastel-blue p-3 px-4 d-flex align-items-center justify-content-between">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon-pastel header-icon-pastel-blue d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: var(--pastel-blue-text);">Paso 1: Seleccionar Estudiante</h3>
                        <p class="mb-0" style="font-size: 0.82rem; color: #4338CA;">Seleccione el estudiante que desea inscribir por prosecución</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                <div class="row">
                    <div class="col-md-12" wire:ignore>
                        <label class="form-label-modern">
                            <i class="fas fa-search"></i>
                            Buscar Estudiante
                            <span class="required-badge">*</span>
                        </label>
                        <select id="alumno_select"
                            class="form-control-modern selectpicker @error('alumnoId') is-invalid @enderror"
                            data-live-search="true" data-size="8" data-width="100%">
                            <option value="">Seleccione un estudiante</option>
                            @foreach ($alumnos as $alumno)
                                @php
                                    $anioActual = \App\Models\AnioEscolar::whereIn('status', [
                                        'Activo',
                                        'Extendido',
                                    ])->first();
                                    $inscripcionAnterior = $alumno->ultimaInscripcionAntesDe($anioActual->id);
                                    $gradoAnterior = $inscripcionAnterior?->grado?->numero_grado;
                                @endphp
                                <option value="{{ $alumno->id }}"
                                    data-subtext="{{ $alumno->persona->tipoDocumento->nombre ?? '' }}
                                    -{{ $alumno->persona->numero_documento }} 
                                    {{ $gradoAnterior ? ' | ' . $gradoAnterior . ' ° nivel academico' : '' }}">
                                    {{ $alumno->persona->primer_nombre }} {{ $alumno->persona->primer_apellido }}
                                </option>
                            @endforeach
                        </select>
                        @error('alumnoId')
                            <div class="invalid-feedback-modern" style="display:block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($alumnoSeleccionado && $gradoAnteriorId)
        <div class="card-modern card-step-blue mb-4 shadow-sm" style="overflow: hidden; border-radius: 12px;">
            <div class="card-header-modern card-header-pastel-blue p-3 px-4 d-flex align-items-center justify-content-between">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon-pastel header-icon-pastel-blue d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: var(--pastel-blue-text);">Datos del Estudiante</h3>
                        <p class="mb-0" style="font-size: 0.82rem; color: #4338CA;">Información personal del estudiante inscrito</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                <livewire:admin.alumnos.alumno-edit :alumnoId="$alumnoId" :key="'alumno-edit-' . $alumnoId" />
            </div>
        </div>

        @php
            $ins = $inscripcionAnterior;
        @endphp
        @if ($ins && ($ins->padre || $ins->madre || $ins->representanteLegal))
            <div class="card-modern mb-4 shadow-sm" style="overflow: hidden; border-radius: 12px;">
                <div class="card-header-modern bg-white border-bottom p-3 px-4 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #E2E8F0 !important;">
                    <div class="header-left d-flex align-items-center gap-3">
                        <div class="header-icon d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                             style="width: 40px; height: 40px; background-color: #EFF6FF; color: #4A6FC1;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Representantes del Estudiante</h3>
                            <p class="text-muted mb-0" style="font-size: 0.82rem;">Información de padres y responsables legales</p>
                        </div>
                    </div>
                </div>
                <div class="card-body-modern" style="padding: 1.25rem;">
                    @if ($ins->padre || $ins->madre)
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                            <!-- Tarjeta del Padre -->
                            @if ($ins->padre)
                                <div class="representante-card representante-card-padre mb-0 h-100">
                                        <div class="representante-header-padre d-flex align-items-center gap-2">
                                            <i class="fas fa-user"></i>
                                            <span class="fw-bold" style="font-size: 0.88rem; letter-spacing: 0.5px;">PADRE</span>
                                        </div>
                                        <div class="representante-grid">
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-id-card"></i>
                                                        <span>Identificación</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Documento:</span>
                                                            <span class="info-val">
                                                                {{ $ins->padre->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $ins->padre->persona->numero_documento ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Telefono:</span>
                                                            <span class="info-val">
                                                                {{ $ins->padre->persona->telefono_completo ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        @if ($ins->padre->persona->telefono_dos_completo)
                                                            <div class="info-col">
                                                                <span class="info-key">Segundo Telefono:</span>
                                                                <span class="info-val">
                                                                    {{ $ins->padre->persona->telefono_dos_completo ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-user"></i>
                                                        <span>Informacion personal</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Nombre:</span>
                                                            <span class="info-val">
                                                                {{ $ins->padre->persona->primer_nombre ?? 'N/A' }}
                                                                {{ $ins->padre->persona->segundo_nombre ?? 'N/A' }}
                                                                {{ $ins->padre->persona->primer_apellido ?? 'N/A' }}
                                                                {{ $ins->padre->persona->segundo_apellido ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Ocupacion:</span>
                                                            <span class="info-val">
                                                                {{ $ins->padre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            <!-- Tarjeta de la Madre -->
                            @if ($ins->madre)
                                <div class="representante-card representante-card-madre mb-0 h-100">
                                        <div class="representante-header-madre d-flex align-items-center gap-2">
                                            <i class="fas fa-user"></i>
                                            <span class="fw-bold" style="font-size: 0.88rem; letter-spacing: 0.5px;">MADRE</span>
                                        </div>

                                        <div class="representante-grid">
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-id-card"></i>
                                                        <span>Identificación</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Documento:</span>
                                                            <span class="info-val">
                                                                {{ $ins->madre->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $ins->madre->persona->numero_documento ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Telefono:</span>
                                                            <span class="info-val">
                                                                {{ $ins->madre->persona->telefono_completo ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        @if ($ins->madre->persona->telefono_dos_completo)
                                                            <div class="info-col">
                                                                <span class="info-key">Segundo Telefono:</span>
                                                                <span class="info-val">
                                                                    {{ $ins->madre->persona->telefono_dos_completo ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-user"></i>
                                                        <span>Informacion personal</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Nombre:</span>
                                                            <span class="info-val">
                                                                {{ $ins->madre->persona->primer_nombre ?? 'N/A' }}
                                                                {{ $ins->madre->persona->segundo_nombre ?? 'N/A' }}
                                                                {{ $ins->madre->persona->primer_apellido ?? 'N/A' }}
                                                                {{ $ins->madre->persona->segundo_apellido ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Ocupacion:</span>
                                                            <span class="info-val">
                                                                {{ $ins->madre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Tarjeta del Representante Legal (100% de ancho, debajo) -->
                        @if ($ins->representanteLegal)
                            <div class="col-12 mt-3">
                                <div class="representante-card representante-card-legal">
                                        <div class="representante-header-legal d-flex align-items-center gap-2">
                                            <i class="fas fa-gavel"></i>
                                            <span class="fw-bold" style="font-size: 0.88rem; letter-spacing: 0.5px;">REPRESENTANTE LEGAL</span>
                                        </div>
                                        <div class="representante-grid">
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-id-card"></i>
                                                        <span>Identificación</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Documento:</span>
                                                            <span class="info-val">
                                                                {{ $ins->representanteLegal->representante->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $ins->representanteLegal->representante->persona->numero_documento ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Telefono:</span>
                                                            <span class="info-val">
                                                                {{ $ins->representanteLegal->representante->persona->telefono_completo ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        @if ($ins->representanteLegal->representante->persona->telefono_dos_completo)
                                                            <div class="info-col">
                                                                <span class="info-key">Segundo Telefono:</span>
                                                                <span class="info-val">
                                                                    {{ $ins->representanteLegal->representante->persona->telefono_dos_completo ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="representante-column">
                                                <div class="info-block">
                                                    <div class="info-block-header">
                                                        <i class="fas fa-user"></i>
                                                        <span>Informacion personal</span>
                                                    </div>
                                                    <div class="info-row-inline">
                                                        <div class="info-col">
                                                            <span class="info-key">Nombre:</span>
                                                            <span class="info-val">
                                                                {{ $ins->representanteLegal->representante->persona->primer_nombre ?? 'N/A' }}
                                                                {{ $ins->representanteLegal->representante->persona->segundo_nombre ?? 'N/A' }}
                                                                {{ $ins->representanteLegal->representante->persona->primer_apellido ?? 'N/A' }}
                                                                {{ $ins->representanteLegal->representante->persona->segundo_apellido ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <div class="info-col">
                                                            <span class="info-key">Ocupacion:</span>
                                                            <span class="info-val">
                                                                {{ $ins->representanteLegal->representante->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="legal-info-section">
                                            <div class="info-block-header">
                                                <i class="fas fa-file-contract"></i>
                                                <span>Información Legal</span>
                                            </div>
                                            <div class="legal-grid">
                                                <div class="info-col">
                                                    <span class="info-key">Parentesco:</span>
                                                    <span
                                                        class="info-val">{{ $ins->representanteLegal->parentesco ?? 'N/A' }}</span>
                                                </div>
                                                <div class="info-col">
                                                    <span class="info-key">Correo:</span>
                                                    <span
                                                        class="info-val">{{ $ins->representanteLegal->correo_representante ?? 'N/A' }}</span>
                                                </div>
                                                @if ($ins->representanteLegal->pertenece_a_organizacion_representante)
                                                    <div class="info-col">
                                                        <span class="info-key">Organización:</span>
                                                        <span
                                                            class="info-val">{{ $ins->representanteLegal->cual_organizacion_representante ?? 'N/A' }}</span>
                                                    </div>
                                                @endif
                                                <div class="info-col">
                                                    <span class="info-key">Carnet Patria:</span>
                                                    <span
                                                        class="info-val-badge {{ $ins->representanteLegal->carnet_patria_afiliado ? 'badge-yes' : 'badge-no' }}">
                                                        {{ $ins->representanteLegal->carnet_patria_afiliado ? 'Sí' : 'No' }}
                                                    </span>
                                                </div>
                                                @if ($ins->representanteLegal->carnet_patria_afiliado)
                                                    <div class="info-col">
                                                        <span class="info-key">Serial:</span>
                                                        <span
                                                            class="info-val">{{ $ins->representanteLegal->serial_carnet_patria_representante ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="info-col">
                                                        <span class="info-key">Código:</span>
                                                        <span
                                                            class="info-val">{{ $ins->representanteLegal->codigo_carnet_patria_representante ?? 'N/A' }}</span>
                                                    </div>
                                                @endif
                                                <div class="info-col">
                                                    <span class="info-key">Banco:</span>
                                                    <span
                                                        class="info-val">{{ $ins->representanteLegal->banco->nombre_banco ?? 'N/A' }}</span>
                                                </div>
                                                <div class="info-col">
                                                    <span class="info-key">Tipo Cuenta:</span>
                                                    <span
                                                        class="info-val">{{ $ins->representanteLegal->tipo_cuenta ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            </div>
                        </div>
                    @endif

                    {{-- @if ($inscripcionAnterior)
                        <div class="alert alert-info">
                            <strong>Debug - Datos cargados:</strong><br>
                            Padre ID: {{ $inscripcionAnterior->padre_id }}<br>
                            Madre ID: {{ $inscripcionAnterior->madre_id }}<br>
                            Rep. Legal ID: {{ $inscripcionAnterior->representante_legal_id }}<br>

                            @if ($inscripcionAnterior->padre)
                                Nombre Padre: {{ $inscripcionAnterior->padre->persona->primer_nombre ?? 'N/A' }}<br>
                            @endif

                            @if ($inscripcionAnterior->madre)
                                Nombre Madre: {{ $inscripcionAnterior->madre->persona->primer_nombre ?? 'N/A' }}<br>
                            @endif

                            @if ($inscripcionAnterior->representanteLegal)
                                Nombre Rep:
                                {{ $inscripcionAnterior->representanteLegal->representante->persona->primer_nombre ?? 'N/A' }}<br>
                            @endif
                        </div>
                    @else 
                        <div class="alert alert-warning">No hay inscripción anterior cargada</div>
                    @endif--}}
                    <div class="alert alert-info mt-3" style="background-color: #EFF6FF !important; border: 1px solid #BFDBFE !important; color: #1E40AF !important; border-radius: 10px !important; padding: 1rem 1.25rem !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                 style="width: 38px; height: 38px; background-color: rgba(74, 111, 193, 0.15); color: #4A6FC1;">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-4">
                                <div>
                                    <span class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 2px;">Nivel Académico Cursado</span>
                                    <strong style="color: #1E293B; font-size: 0.95rem;">{{ $grados->firstWhere('id', $gradoAnteriorId)?->numero_grado ?? 'N/A' }}° Nivel Académico</strong>
                                </div>
                                <div style="border-left: 1px solid #BFDBFE; padding-left: 1rem;">
                                    <span class="text-muted" style="font-size: 0.78rem; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 2px;">Sección Anterior</span>
                                    <strong style="color: #1E293B; font-size: 0.95rem;">Sección {{ $inscripcionAnterior->seccion->nombre ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
        @endif
    @if ($alumnoSeleccionado)
        <div class="card-modern card-step-purple mb-4 shadow-sm" style="overflow: hidden; border-radius: 12px;">
            @php
                $materiasArrastradas = collect($materias)->where('origen', 'pendiente_anterior')->sortBy('nombre');
                $materiasActuales = collect($materias)->where('origen', 'grado_actual')->sortBy('nombre');
                $idsArrastradas = $materiasArrastradas->pluck('id')->toArray();
                $pendientesActuales = collect($materiasSeleccionadas)->diff($idsArrastradas)->count();
            @endphp
            <div class="card-header-modern card-header-pastel-purple p-3 px-4 d-flex align-items-center justify-content-between">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon-pastel header-icon-pastel-purple d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: var(--pastel-purple-text);">Paso 2: Áreas de Formación</h3>
                        <p class="mb-0" style="font-size: 0.82rem; color: #7E22CE;">Selección de áreas aprobadas y reprobadas</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                @if (count($materias) > 0)
                    @if ($materiasArrastradas->isNotEmpty())
                        <div class="materias-section mb-4">
                            <div class="section-header-warning mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h5 class="mb-0">Areas de formacion reprobadas</h5>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i>
                                    El estudiante tiene areas de formacion reprobadas de niveles academicos anteriores.
                                    Debe
                                    aprobarlas todas;
                                    de lo contrario, deberá repetir el nivel academico completo.
                                    <b>MARQUE</b> las áreas de formación que el estudiante <b>APROBÓ</b>
                                </small>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="checkbox-item-modern d-flex align-items-center">
                                        <input type="checkbox" id="select_all_arrastradas"
                                            wire:model.live="seleccionarTodasArrastradas"
                                            wire:loading.attr="disabled"
                                            wire:target="seleccionarTodasArrastradas, materiasSeleccionadas"
                                            class="form-check-input me-3 my-0">
                                        <label for="select_all_arrastradas" class="form-check-label mb-0 cursor-pointer fw-semibold d-flex align-items-center flex-wrap" style="color: #1E293B;">
                                            <span>Seleccionar todas como <span class="text-success fw-bold">APROBADAS</span></span>
                                            <span wire:loading wire:target="seleccionarTodasArrastradas" class="text-primary ms-3" style="font-size: 0.85rem; font-weight: normal;">
                                                <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="position-relative" wire:loading.class="opacity-50 pe-none" wire:target="seleccionarTodasArrastradas, materiasSeleccionadas">
                                <div class="row">
                                    @foreach ($materiasArrastradas as $materia)
                                        <div class="col-md-6 mb-3">
                                            <div class="checkbox-item-modern border-warning bg-warning-light d-flex align-items-center">
                                                <input type="checkbox" wire:model.live="materiasSeleccionadas"
                                                    wire:loading.attr="disabled"
                                                    wire:target="seleccionarTodasArrastradas"
                                                    value="{{ $materia['id'] }}" id="materia_{{ $materia['id'] }}"
                                                    class="form-check-input checkbox-warning me-3 my-0">
                                                <label for="materia_{{ $materia['id'] }}" class="form-check-label mb-0 cursor-pointer w-100">
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <strong style="color: #92400E;">{{ $materia['nombre'] }}</strong>
                                                            </div>
                                                            <small class="text-muted" style="font-size: 0.8rem;">
                                                                <i class="fas fa-tag"></i> Código:
                                                                {{ $materia['codigo'] }}
                                                                |
                                                                <i class="fas fa-layer-group"></i> Nivel académico:
                                                                {{ $materia['grado'] }}°
                                                            </small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="divider-section my-4">
                            <hr class="divider-line">
                        </div>
                    @endif
                    @if ($materiasActuales->isNotEmpty())
                        <div class="materias-section">
                            <div class="section-header-primary mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-book-open"></i>
                                    <h5 class="mb-0">Materias del Nivel Académico Actual</h5>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i>
                                    <b>MARQUE</b> las materias que el estudiante <b>APROBÓ</b>
                                </small>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="checkbox-item-modern d-flex align-items-center">
                                        <input type="checkbox" id="select_all_actuales"
                                            wire:model.live="seleccionarTodasActuales"
                                            wire:loading.attr="disabled"
                                            wire:target="seleccionarTodasActuales, materiasSeleccionadas"
                                            class="form-check-input me-3 my-0">
                                        <label for="select_all_actuales" class="form-check-label mb-0 cursor-pointer fw-semibold d-flex align-items-center flex-wrap" style="color: #1E293B;">
                                            <span>Seleccionar todas como <span class="text-success fw-bold">APROBADAS</span></span>
                                            <span wire:loading wire:target="seleccionarTodasActuales" class="text-primary ms-3" style="font-size: 0.85rem; font-weight: normal;">
                                                <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="position-relative" wire:loading.class="opacity-50 pe-none" wire:target="seleccionarTodasActuales, materiasSeleccionadas">
                                <div class="row">
                                    @foreach ($materiasActuales as $materia)
                                        <div class="col-md-6 mb-3">
                                            <div class="checkbox-item-modern materia-actual d-flex align-items-center">
                                                <input type="checkbox" wire:model.live="materiasSeleccionadas"
                                                    wire:loading.attr="disabled"
                                                    wire:target="seleccionarTodasActuales"
                                                    value="{{ $materia['id'] }}" id="materia_{{ $materia['id'] }}"
                                                    class="form-check-input me-3 my-0">
                                                <label for="materia_{{ $materia['id'] }}" class="form-check-label mb-0 cursor-pointer w-100">
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        <div>
                                                            <strong style="color: #1E293B;">{{ $materia['nombre'] }}</strong>
                                                            <br>
                                                            <small class="text-muted" style="font-size: 0.8rem;">
                                                                <i class="fas fa-tag"></i> Código:
                                                                {{ $materia['codigo'] }} |
                                                                <i class="fas fa-layer-group"></i> Nivel académico:
                                                                {{ $materia['grado'] }}°
                                                            </small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                    @endif
                @else
                    <div class="alert alert-info" style="background-color: #EFF6FF !important; border: 1px solid #BFDBFE !important; color: #1E40AF !important; border-radius: 8px !important;">
                        <i class="fas fa-info-circle me-1"></i>
                        No hay materias disponibles para este estudiante.
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-modern card-step-green mb-4 shadow-sm" style="overflow: hidden; border-radius: 12px;">
            <div class="card-header-modern card-header-pastel-green p-3 px-4 d-flex align-items-center justify-content-between">
                <div class="header-left d-flex align-items-center gap-3">
                    <div class="header-icon-pastel header-icon-pastel-green d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: var(--pastel-green-text);">Paso 3: Nivel Académico y Sección</h3>
                        <p class="mb-0" style="font-size: 0.82rem; color: #047857;">Seleccione el nivel académico al que será promovido el estudiante</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern" style="padding: 2rem;">
                @if ($mensajeSugerencia)
                    <div class="mb-3">
                        <div class="alert alert-info" style="background-color: #EFF6FF !important; border: 1px solid #BFDBFE !important; color: #1E40AF !important; border-radius: 8px !important; padding: 0.85rem 1.15rem !important;">
                            <i class="fas fa-lightbulb me-2 text-primary"></i>
                            {{ $mensajeSugerencia }}
                        </div>
                    </div>
                @endif

                <div class="mb-3" style="display: grid; grid-template-columns: {{ (!$esPrimerGrado && $gradoPromocionId) ? '1fr 1fr 1fr' : '1fr 1fr' }}; gap: 24px;">
                    <!-- Campo Nivel Académico -->
                    <div>
                        <label for="grado_promocion" class="form-label fw-semibold mb-2" style="color: #334155;">
                            <i class="fas fa-layer-group me-1" style="color: #4A6FC1;"></i>
                            Nivel Académico de Promoción <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="gradoPromocionId" id="grado_promocion"
                            class="form-select @error('gradoPromocionId') is-invalid @enderror">
                            <option value="">Seleccione un nivel académico</option>
                            @foreach ($grados as $grado)
                                <option value="{{ $grado->id }}">
                                    {{ $grado->numero_grado }}°
                                </option>
                            @endforeach
                        </select>
                        @error('gradoPromocionId')
                            <div class="invalid-feedback-modern" style="display: block;">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                        @if ($repite_grado)
                            <small class="form-text text-danger mt-1">
                                <i class="fas fa-info-circle"></i>
                                El estudiante debe repetir el mismo nivel académico
                            </small>
                        @endif
                    </div>

                    @if (!$esPrimerGrado && $gradoPromocionId)
                        <!-- Campo Sección -->
                        <div>
                            <label for="seccion" class="form-label fw-semibold mb-2" style="color: #334155;">
                                <i class="fas fa-th-large me-1" style="color: #4A6FC1;"></i>
                                Sección <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="seccion_id" id="seccion"
                                class="form-select @error('seccion_id') is-invalid @enderror">
                                <option value="">Seleccione una sección</option>
                                @foreach ($secciones as $seccion)
                                    <option value="{{ $seccion->id }}">
                                        Sección {{ $seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seccion_id')
                                <div class="invalid-feedback-modern" style="display: block;">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endif

                    <!-- Campo Fecha de Inscripción -->
                    <div>
                        <label for="fecha" class="form-label fw-semibold mb-2" style="color: #334155;">
                            <i class="fas fa-calendar me-1" style="color: #4A6FC1;"></i>
                            Fecha de Inscripción
                        </label>
                        <input type="text" id="fecha"
                            value="{{ now('America/Caracas')->format('m/d/Y h:i A') }}"
                            class="form-control" readonly
                            style="background-color: #F8FAFC !important; height: 42px; color: #475569;">
                    </div>
                </div>

                <!-- Campo Observaciones -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label fw-semibold mb-2" style="color: #334155;">
                        <i class="fas fa-comment me-1" style="color: #4A6FC1;"></i>
                        Observaciones
                    </label>
                    <textarea wire:model.live="observaciones" id="observaciones"
                        class="form-control @error('observaciones') is-invalid @enderror" rows="3"
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

                <!-- Normas de Convivencia -->
                <div class="mb-2">
                    <div class="checkbox-item-modern d-flex align-items-center">
                        <input type="checkbox" id="acepta_normas_contrato"
                            wire:model.live="acepta_normas_contrato" class="form-check-input me-2 my-0">
                        <label for="acepta_normas_contrato" class="form-check-label mb-0 cursor-pointer">
                            He leído y acepto
                            <a class="text-primary ms-1 fw-semibold" data-bs-toggle="modal"
                                data-bs-target="#modalContratoConvivencia" style="text-decoration: none;">
                                las normas de convivencia
                            </a>
                            <span class="required-badge text-danger ms-1">*</span>
                        </label>
                    </div>
                    @error('acepta_normas_contrato')
                        <div class="invalid-feedback-modern" style="display: block;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-modern shadow-sm">
            <div class="card-body-modern" style="padding: 1.5rem 2rem;">
                <div class="d-flex justify-content-end gap-3 align-items-center">
                    <a href="{{ route('admin.transacciones.inscripcion_prosecucion.index') }}"
                        class="btn-secondary-modern text-decoration-none">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </a>
                    <button type="button" wire:click="finalizar" class="btn-primary-modern"
                        wire:loading.attr="disabled">
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
    @endif
    @include('admin.transacciones.inscripcion.modales.showContratoModal')
</div>

@push('js')
    <script>
        document.addEventListener('livewire:init', () => {
            const select = $('#alumno_select');
            select.selectpicker();
            select.on('changed.bs.select', function() {
                const alumnoId = $(this).val();
                Livewire.dispatch('seleccionarAlumno', {
                    alumnoId
                });
            });
            Livewire.on('refreshSelectAlumno', () => {
                select.selectpicker('destroy');
                select.selectpicker();
            });

        });
    </script>
@endpush
