<div>
    @if (session()->has('success') || session()->has('error'))
        <div class="alerts-container mb-3">
            @if (session()->has('success'))
                <div class="alert-modern alert-success alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Éxito</h4>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ session('error') }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
    @endif
    @if (!$modoEditar)
        <div class="card card-modern shadow-sm mb-4" style="border: 1px solid #E2E8F0; border-radius: 14px; background: #FFFFFF; overflow: visible !important;">
            <div class="card-header-modern d-flex align-items-center justify-content-between p-3 px-4 bg-white border-bottom" style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-search" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Buscar Docente</h3>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">Seleccione el docente para asignarle áreas de formación</p>
                    </div>
                </div>
            </div>
            <div class="card-body-modern p-4" style="background-color: #FFFFFF; overflow: visible !important; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                <div class="row">
                    <div class="col-md-12" wire:ignore style="overflow: visible !important;">
                        <label for="docente_select" class="form-label-modern fw-semibold mb-2" style="color: #334155; font-size: 0.9rem;">
                            <i class="fas fa-chalkboard-teacher me-1" style="color: #4A6FC1;"></i>
                            Docente
                            <span class="text-danger">*</span>
                        </label>
                        <select id="docente_select"
                            class="form-select select-modern selectpicker @error('docenteId') is-invalid @enderror"
                            data-live-search="true" data-size="8" data-style="btn-default" data-width="100%"
                            wire:model.live="docenteId">
                            <option value="" selected disabled>Seleccione un docente</option>
                            @foreach ($docentes as $docente)
                                @if ($docente->detalleEstudios->count() > 0)
                                    <option value="{{ $docente->id }}"
                                        data-subtext="{{ $docente->persona->tipoDocumento->nombre ?? 'N/A' }}-{{ $docente->persona->numero_documento }}">
                                        {{ $docente->nombre_completo }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('docenteId')
                            <div class="invalid-feedback-modern text-danger mt-1" style="display: block; font-size: 0.82rem;">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <small class="text-muted d-block mt-2" style="font-size: 13px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Busque por nombre, apellido o cédula del docente
                        </small>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if ($docenteSeleccionado)
        <div class="card card-modern shadow-sm mb-4" 
             style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-left: 4px solid #4A6FC1 !important; border-radius: 14px; overflow: visible;">
            <div class="card-header-modern d-flex align-items-center justify-content-between p-3 px-4 bg-white border-bottom" 
                 style="border-bottom-color: #F1F5F9 !important; border-top-left-radius: 14px; border-top-right-radius: 14px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px; border-radius: 10px; background-color: #EFF6FF; color: #4A6FC1;">
                        <i class="fas fa-id-card" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #1E293B;">Información del Docente</h3>
                            <span class="badge d-inline-flex align-items-center gap-1"
                                  style="background-color: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.55rem; border-radius: 6px;">
                                <i class="fas fa-lock" style="font-size: 0.7rem; color: #64748B;"></i> Solo lectura
                            </span>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">Ficha y datos personales del docente seleccionado</p>
                    </div>
                </div>
            </div>
            
            <div class="card-body-modern p-4" style="background-color: #F8FAFC; border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                <div class="row g-4">
                    {{-- Columna 1: Identificación y Contacto --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-bottom-color: #E2E8F0 !important;">
                            <i class="fas fa-id-card" style="color: #4A6FC1; font-size: 0.95rem;"></i>
                            <span class="fw-bold" style="font-size: 0.92rem; color: #334155;">Identificación y Contacto</span>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Número de Cédula</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 600; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->tipoDocumento->nombre ?? 'V' }}-{{ $docenteSeleccionado->persona->numero_documento }}
                                </span>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Código Docente</label>
                                <span class="d-block fw-bold" style="color: #4A6FC1; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->codigo ?? 'Sin código' }}
                                </span>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Teléfono Principal</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 500; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->telefono_completo ?? 'Sin registrar' }}
                                </span>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Teléfono Secundario</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 500; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->telefono_dos_completo ?? 'Sin registrar' }}
                                </span>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Dependencia</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 500; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->dependencia ?? 'Sin especificar' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Columna 2: Personal y Estudios --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-bottom-color: #E2E8F0 !important;">
                            <i class="fas fa-user-graduate" style="color: #4A6FC1; font-size: 0.95rem;"></i>
                            <span class="fw-bold" style="font-size: 0.92rem; color: #334155;">Personal y Estudios</span>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Nombre Completo</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 600; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->nombreCompleto }}
                                </span>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Fecha de Nacimiento</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 500; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->fecha_nacimiento ? $docenteSeleccionado->persona->fecha_nacimiento->format('d/m/Y') : 'N/A' }}
                                    @if($docenteSeleccionado->persona->fecha_nacimiento)
                                        <small class="text-muted fw-normal">({{ $docenteSeleccionado->persona->fecha_nacimiento->age }} años)</small>
                                    @endif
                                </span>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Género</label>
                                <span class="d-block" style="color: #1E293B; font-weight: 500; font-size: 0.95rem;">
                                    {{ $docenteSeleccionado->persona->genero->genero ?? 'Sin registrar' }}
                                </span>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="text-muted d-block mb-1" style="font-size: 13px;">Estudios Realizados</label>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @forelse($docenteSeleccionado->detalleDocenteEstudio as $detalle)
                                        @if ($detalle->status)
                                            <span class="badge" style="background-color: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.8rem; font-weight: 500; border-radius: 6px; padding: 0.35rem 0.65rem;">
                                                <i class="fas fa-graduation-cap me-1 text-primary"></i>{{ $detalle->estudiosRealizado->estudios ?? 'N/A' }}
                                            </span>
                                        @endif
                                    @empty
                                        <span class="text-muted fst-italic" style="font-size: 0.85rem;">No tiene estudios registrados</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="alerts-container mb-3">
            @if ($alertaAsignacion)
                <div class="alert-modern alert-error alert alert-dismissible fade show">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Error</h4>
                        <p>{{ $alertaAsignacion }}</p>
                    </div>
                    <button type="button" class="alert-close btn-close" data-bs-dismiss="alert"
                        aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>

        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h3>Agregar Nueva Asignación</h3>
                        <p>Asigne areas de formacion, niveles academicos y secciones al docente</p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem;">
                <div class="row">
                    <div class="col-md-4">
                        <label for="materia_select" class="form-label-modern">
                            <i class="fas fa-book"></i>
                            Area de Formacion
                            <span class="required-badge">*</span>
                        </label>

                        <select wire:model.live="materiaId" id="materia_select"
                            class="form-control-modern @error('materiaId') is-invalid @enderror">
                            <option value="">Seleccione un area de formacion</option>
                            @foreach ($materias as $materia)
                                <option value="{{ $materia->id }}">
                                    {{ $materia->areaFormacion->nombre_area_formacion }}
                                </option>
                            @endforeach
                        </select>

                        @error('materiaId')
                            <div class="invalid-feedback-modern" style="display: block;">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="grado_area_select" class="form-label-modern">
                            <i class="fas fa-layer-group"></i>
                            Nivel Academico
                            <span class="required-badge">*</span>
                        </label>
                        <select wire:model.live="gradoAreaId" id="grado_area_select"
                            class="form-control-modern @error('gradoAreaId') is-invalid @enderror"
                            {{ !$materiaId ? 'disabled' : '' }}>
                            <option value="">
                                @if (!$materiaId)
                                    Primero seleccione un area de formacion
                                @elseif($gradosArea->isEmpty())
                                    No hay niveles academicos disponibles
                                @else
                                    Seleccione un nivel academico
                                @endif
                            </option>
                            @foreach ($gradosArea as $grado)
                                <option value="{{ $grado->id }}">
                                    {{ $grado->numero_grado }}
                                </option>
                            @endforeach
                        </select>

                        @error('gradoAreaId')
                            <div class="invalid-feedback-modern" style="display: block;">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="seccion_area_select" class="form-label-modern">
                            <i class="fas fa-users"></i>
                            Sección
                            <span class="required-badge">*</span>
                        </label>

                        <select wire:model.live="seccionAreaId" id="seccion_area_select"
                            class="form-control-modern @error('seccionAreaId') is-invalid @enderror"
                            {{ !$gradoAreaId ? 'disabled' : '' }}>
                            <option value="">
                                @if (!$gradoAreaId)
                                    Primero seleccione un nivel academico
                                @elseif($seccionesArea->isEmpty())
                                    No hay secciones disponibles
                                @else
                                    Seleccione una sección
                                @endif
                            </option>
                            @foreach ($seccionesArea as $seccion)
                                <option value="{{ $seccion->id }}">
                                    Sección {{ $seccion->nombre }}
                                </option>
                            @endforeach
                        </select>

                        @error('seccionAreaId')
                            <div class="invalid-feedback-modern" style="display: block;">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn-primary-modern w-100" wire:click="agregarAsignacionArea"
                            wire:loading.attr="disabled" style="margin-bottom: 0rem;" @disabled(!$this->puedeAgregarArea)
                            title="{{ !$this->puedeAgregarArea ? 'Seleccione materia, año y sección' : '' }}">
                            <span wire:loading.remove wire:target="agregarAsignacionArea">
                                <i class="fas fa-plus"></i> Agregar
                            </span>
                            <span wire:loading wire:target="agregarAsignacionArea">
                                <i class="fas fa-spinner fa-spin"></i> Agregando...
                            </span>
                        </button>
                    </div>
                </div>
                <small class="form-text-modern d-flex align-items-center gap-1">
                    <i class="fas fa-plus-circle text-primary"></i>
                    <a href="#" class="text-primary text-decoration-none" data-bs-toggle="modal"
                        data-bs-target="#modalCrearAsignacion">
                        Crear nueva asignacion
                    </a>
                </small>
            </div>
            <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
            <div class="card-body-modern pt-0">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="text-align: center; vertical-align: middle;">#</th>
                            <th style="text-align: center; vertical-align: middle;">Area de formacion</th>
                            <th style="text-align: center; vertical-align: middle;">Nivel academico</th>
                            <th style="text-align: center; vertical-align: middle;">Sección</th>
                            <th style="text-align: center; vertical-align: middle;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($asignaciones->where('tipo_asignacion', 'area') as $index => $detalle)
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="number-badge">{{ $index + 1 }}</span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">

                                        <div style="font-weight: 600; color: var(--gray-900); font-size: 0.95rem;">
                                            {{ $detalle->areaEstudios->areaFormacion->nombre_area_formacion ?? 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="font-weight: 600; color: var(--gray-900); font-size: 0.95rem;">
                                        {{ $detalle->grado->numero_grado ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="font-weight: 600; color: var(--gray-900); font-size: 0.95rem;">
                                        {{ $detalle->seccion->nombre ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="display: flex; justify-content: center;">
                                        <button class="action-btn btn-delete"
                                            wire:click="$set('asignacionAEliminar', {{ $detalle->id }})"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminarAsignacion"
                                            title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-inbox"
                                        style="font-size: 3rem; color: var(--gray-400); margin-bottom: 1rem;"></i>
                                    <p style="color: var(--gray-600); font-size: 1.1rem; margin: 0;">
                                        No hay asignaciones registradas
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <div wire:ignore.self class="modal fade" id="modalEliminarAsignacion" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-modern">
                    <div class="modal-header-delete">
                        <div class="modal-icon-delete">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <h5 class="modal-title-delete">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body-delete">
                        <p>¿Deseas eliminar esta asignación?</p>
                        <p class="delete-warning">Esta acción es irreversible.</p>
                    </div>
                    <div class="modal-footer-delete">
                        <div class="footer-buttons">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button class="btn-modal-delete"
                                wire:click="eliminarAsignacion({{ $asignacionAEliminar }})"
                                wire:loading.attr="disabled" data-bs-dismiss="modal">
                                <span wire:loading.remove wire:target="eliminarAsignacion">
                                    Eliminar
                                </span>
                                <span wire:loading wire:target="eliminarAsignacion">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    Eliminando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h3>Agregar Grupo Estable</h3>
                        <p>Asigne un grupo estable y su nivel académico </p>
                    </div>
                </div>
            </div>

            <div class="card-body-modern" style="padding: 2rem;">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label-modern">
                            <i class="fas fa-users"></i>
                            Grupo Estable
                            <span class="required-badge">*</span>
                        </label>

                        <select wire:model.live="grupoEstableId"
                            class="form-control-modern @error('grupoEstableId') is-invalid @enderror">
                            <option value="">Seleccione un grupo estable</option>
                            @foreach ($gruposEstables as $grupo)
                                <option value="{{ $grupo->id }}">
                                    {{ $grupo->nombre_grupo_estable }}
                                </option>
                            @endforeach
                        </select>

                        @error('grupoEstableId')
                            <div class="invalid-feedback-modern" style="display:block">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-modern">
                            <i class="fas fa-layer-group"></i>
                            Nivel Académico
                            <span class="required-badge">*</span>
                        </label>

                        <select wire:model.live="gradoGrupoId"
                            class="form-control-modern @error('gradoGrupoId') is-invalid @enderror"
                            {{ !$grupoEstableId ? 'disabled' : '' }}>
                            <option value="">
                                @if (!$grupoEstableId)
                                    Primero seleccione un grupo estable
                                @elseif($gradosGrupo->isEmpty())
                                    No hay niveles académicos disponibles
                                @else
                                    Seleccione un nivel académico
                                @endif
                            </option>

                            @foreach ($gradosGrupo as $grado)
                                <option value="{{ $grado->id }}">
                                    {{ $grado->numero_grado }}
                                </option>
                            @endforeach
                        </select>

                        @error('gradoGrupoId')
                            <div class="invalid-feedback-modern" style="display:block">
                                <i class="fas fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn-primary-modern w-100" wire:click="agregarAsignacionGrupo"
                            wire:loading.attr="disabled" style="margin-bottom: 0rem;" @disabled(!$this->puedeAgregarGrupo)
                            title="{{ !$this->puedeAgregarGrupo ? 'Complete todos los campos' : '' }}">
                            <span wire:loading.remove wire:target="agregarAsignacionGrupo">
                                <i class="fas fa-plus"></i> Agregar
                            </span>
                            <span wire:loading wire:target="agregarAsignacionGrupo">
                                <i class="fas fa-spinner fa-spin"></i> Agregando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <hr class=" pt-0" style="border-top: 2px dashed #e5e7eb;">


            <div class="card-body-modern pt-0">
                <div class="table-wrapper">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="text-align: center; vertical-align: middle;">Grupo Estable</th>
                                <th style="text-align: center; vertical-align: middle;">Nivel Académico</th>
                                <th style="text-align: center; vertical-align: middle;">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($asignaciones->where('tipo_asignacion','grupo_estable') as  $detalle)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <strong>{{ $detalle->grupoEstable->nombre_grupo_estable ?? 'N/A' }}</strong>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $detalle->gradoGrupoEstable->numero_grado ?? 'N/A' }}</td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div style="display: flex; justify-content: center;">
                                            <button class="action-btn btn-delete"
                                                wire:click="$set('asignacionAEliminar', {{ $detalle->id }})"
                                                data-bs-toggle="modal" data-bs-target="#modalEliminarAsignacion"
                                                title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted"></i>
                                        <p class="mt-2">No hay grupos estables asignados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        @livewire('admin.modales.area-estudio-create')

    @endif

    {{-- Botones de Acción del Footer --}}
    <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top" style="border-top-color: #E2E8F0 !important;">
        <a href="{{ route('admin.transacciones.docente_area_grado.index') }}" class="btn-secondary-modern text-decoration-none">
            <i class="fas fa-times me-1"></i> Cancelar
        </a>
        <button type="button" id="btnGuardar"
            onclick="window.location.href='{{ route('admin.transacciones.docente_area_grado.index') }}'"
            class="btn btn-primary-modern"
            wire:loading.attr="disabled"
            wire:target="docenteId, docente_id">
            <span wire:loading.remove wire:target="docenteId, docente_id">
                <i class="fas fa-save me-1"></i> Guardar Asignación
            </span>
            <span wire:loading wire:target="docenteId, docente_id">
                <i class="fas fa-spinner fa-spin me-1"></i> Cargando...
            </span>
        </button>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $.fn.selectpicker !== 'undefined') {
                    $('.selectpicker').selectpicker({
                        width: '100%',
                        dropdownAlignRight: false
                    });
                }

                $('#docente_select').on('changed.bs.select change', function() {
                    var valorDocente = $(this).val();
                    if (typeof @this !== 'undefined') {
                        @this.set('docenteId', valorDocente);
                        @this.set('docente_id', valorDocente);
                    }
                });

                $('#area_estudio_realizados_id').on('changed.bs.select change', function() {
                    if (typeof @this !== 'undefined') {
                        @this.set('materiaId', $(this).val());
                    }
                });
            });

            document.addEventListener('livewire:updated', () => {
                if (typeof $.fn.selectpicker !== 'undefined') {
                    $('.selectpicker').selectpicker('refresh');
                }
            });

            if (typeof Livewire !== 'undefined') {
                Livewire.on('resetSelects', () => {
                    if (typeof $.fn.selectpicker !== 'undefined') {
                        $('.selectpicker').val('').selectpicker('refresh');
                    }
                });
            }

            setTimeout(function() {
                $('.alert-modern').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        </script>
    @endpush
</div>
