<!-- Modal Ver Información de Prosecución - Histórico -->
<div class="modal fade" id="showModalProsecucion-{{ $datos->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow">
            <div class="modal-header modal-header-view">
                <div class="w-100 text-center">
                    <h5 class="modal-title-view mb-2">
                        <i class="fas fa-arrow-circle-up me-2"></i>
                        Inscripción por Prosecución
                    </h5>
                    <p class="mb-2" style="font-size: 0.95rem; opacity: 0.95;">
                        Calendario Escolar:
                        {{ \Carbon\Carbon::parse($datos->anioEscolar->inicio_anio_escolar)->format('Y') }}
                        -
                        {{ \Carbon\Carbon::parse($datos->anioEscolar->cierre_anio_escolar)->format('Y') }}
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge badge-status badge-{{ strtolower($datos->status) }}">
                            <i class="fas fa-circle-dot"></i>
                            {{ $datos->status }}
                        </span>
                        <span class="badge bg-white text-primary">
                            <i class="fas fa-graduation-cap"></i>
                            {{ $datos->grado->numero_grado ?? 'N/A' }}°
                        </span>
                        @if ($datos->seccion)
                            <span class="badge bg-white text-info">
                                <i class="fas fa-users"></i>
                                Sección {{ $datos->seccion->nombre }}
                            </span>
                        @endif
                        @if ($datos->repite_grado)
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-redo"></i> Repite Nivel Academico
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="fas fa-arrow-up"></i> Promovido
                            </span>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body modal-body-view">
                <div class="mb-4">
                    @php
                        $dato = $datos->inscripcion->alumno->persona;
                        $alumno = $datos->inscripcion->alumno;
                    @endphp
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        <span>Datos del Estudiante</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-4 ">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Cedula
                                    </span>
                                    <span class="detail-value">
                                        {{ $dato->tipoDocumento->nombre ?? 'N/A' }}-{{ $dato->numero_documento ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Fecha de Nacimiento
                                    </span>
                                    <span class="detail-value">
                                        {{ \Carbon\Carbon::parse($dato->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                        <small
                                            class="text-muted">({{ \Carbon\Carbon::parse($dato->fecha_nacimiento)->age }}
                                            años)</small>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Género
                                    </span>
                                    <span class="detail-value">
                                        {{ $dato->genero->genero ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Nombre Completo
                                    </span>
                                    <span class="detail-value fw-bold">
                                        {{ $dato->primer_nombre ?? '' }}
                                        {{ $dato->segundo_nombre ?? '' }}
                                        {{ $dato->stercer_nombre ?? '' }}
                                        {{ $dato->primer_apellido ?? '' }}
                                        {{ $dato->segundo_apellido ?? '' }}
                                    </span>
                                </div>
                            </div>
                            @if(!auth()->user()?->esSoloDocente())
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Lugar de nacimiento
                                    </span>
                                    <span class="detail-value fw-bold">
                                        @php
                                            $pais = $dato?->localidad?->municipio?->estado?->pais?->nameES;
                                        @endphp
                                        @if ($pais && strtolower($pais) !== 'venezuela')
                                            {{ $pais }} /
                                        @endif
                                        {{ $dato?->localidad?->municipio?->estado?->nombre_estado ?? 'N/A' }}
                                        /
                                        {{ $dato?->localidad?->municipio?->nombre_municipio ?? 'N/A' }}
                                        /
                                        {{ $dato?->localidad?->nombre_localidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Peso
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->peso ?? 'N/A' }} kg
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Estatura
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->estatura ?? 'N/A' }} m
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Lateralidad
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->lateralidad->lateralidad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Orden de Nacimiento
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->ordenNacimiento->orden_nacimiento ?? 'N/A' }}
                                    </span>
                                </div>

                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Zapato
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->talla_zapato ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Camisa
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->tallaCamisa->nombre ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Talla Pantalones
                                    </span>
                                    <span class="detail-value">
                                        {{ $alumno->tallaPantalon->nombre ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            @if ($datos->inscripcion->alumno->etniaIndigena)
                                <div class="col-md-4 ">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Etnia Indígena
                                        </span>

                                        @if ($datos->inscripcion->alumno->etniaIndigena->count() > 0)
                                            <div class="detail-value">
                                                {{ $datos->inscripcion->alumno->etniaIndigena->nombre }}
                                            </div>
                                        @else
                                            <span class="detail-value text-muted">
                                                Ninguna registrada
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @endif

                            @if ($datos->inscripcion->alumno->discapacidades)
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Discapacidades
                                        </span>

                                        @if ($datos->inscripcion->alumno->discapacidades->count() > 0)
                                            <div class="d-flex flex-wrap mt-1">
                                                @foreach ($datos->inscripcion->alumno->discapacidades as $discapacidad)
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

                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-arrow-circle-up text-success"></i>
                        <span>Información de Prosecución</span>
                    </div>

                    <div class="card mini-card shadow-sm border-0 p-2 mt-1">
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 ">
                                    <div class="card-body bg-blue">
                                        <div class="detail-item">
                                            <span class="detail-label">
                                                <i class="fas fa-layer-group"></i>Nivel Academico Anterior
                                            </span>
                                            <span class="detail-value fw-bold text-center">
                                                {{ $datos->inscripcionAnterior?->grado?->numero_grado ?? 'N/A' }}°
                                                
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body bg-success">
                                        <div class="detail-item">
                                            <span class="detail-label ">
                                                <i class="fas fa-arrow-right "></i> Nivel de Promoción
                                            </span>
                                            <span class="detail-value fw-bold text-center">
                                                {{ $datos->grado->numero_grado ?? 'N/A' }}°
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 ">
                                    <div class="card-body {{ $datos->repite_grado ? 'bg-warning' : 'bg-success' }}">
                                        <div class="detail-item">
                                            <span class="detail-label text-dark">
                                                <i
                                                    class="fas {{ $datos->repite_grado ? 'fa-redo' : 'fa-check-circle' }}"></i>
                                                Estado
                                            </span>
                                            <span class="detail-value fw-bold text-center">
                                                {{ $datos->repite_grado ? 'Repite Grado' : 'Promovido' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($datos->seccion)
                                <div class="col-md-3">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body bg-purple">
                                            <div class="detail-item">
                                                <span class="detail-label ">
                                                    <i class="fas fa-th-large"></i> Seccion Asignada
                                                </span>
                                                <span class="detail-value fw-bold text-center">
                                                    {{ $datos->seccion->nombre ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row mt-3 justify-content-center">
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            <i class="fas fa-calendar"></i> Fecha de Inscripción
                                        </span>
                                        <span class="detail-value">
                                            {{ \Carbon\Carbon::parse($datos->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            <i class="fas fa-file-contract"></i> Aceptó Normas
                                        </span>
                                        <span class="detail-value">
                                            @if ($datos->acepta_normas_contrato === 1)
                                                <span class="badge-sm badge-yes">Sí</span>
                                            @else
                                                <span class="badge-sm badge-no">No</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
                <div class="mb-4">


                    <div class="section-title">
                        <i class="fas fa-book "></i>
                        <span>Estado de Materias</span>
                    </div>

                    @php
                        $prosecucionAreas = $datos->prosecucionAreas ?? collect();

                        $materiasAprobadas = $datos->prosecucionAreas->where('status', 'aprobada');
                        $materiasPendientes = $datos->prosecucionAreas->where('status', 'pendiente');

                    @endphp

                    <div class="row g-1 mt-2 justify-content-center">
                        @if ($materiasAprobadas->count() > 0)
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Materias Aprobadas ({{ $materiasAprobadas->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($materiasAprobadas as $materia)
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="fas fa-book-open text-success me-2"></i>
                                                        {{ $materia->gradoAreaFormacion->area_formacion->nombre_area_formacion ?? 'N/A' }}
                                                    </span>
                                                    <span class="badge bg-success " style="margin-right: 0.5rem">
                                                        {{ $materia->gradoAreaFormacion->codigo ?? 'N/A' }}
                                                    </span>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($materiasPendientes->count() > 0)
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Materias Pendientes ({{ $materiasPendientes->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($materiasPendientes as $materia)
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>
                                                        <i class="fas fa-book text-warning me-2"></i>
                                                        {{ $materia->gradoAreaFormacion->area_formacion->nombre_area_formacion ?? 'N/A' }}
                                                    </span>
                                                    <span class="badge bg-warning text-dark"
                                                        style="margin-right: 0.5rem">
                                                        {{ $materia->gradoAreaFormacion->codigo ?? 'N/A' }}
                                                    </span>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($materiasAprobadas->count() == 0 && $materiasPendientes->count() == 0)
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay información de materias registradas
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-users"></i>
                        <span>Representantes</span>
                    </div>

                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">

                        <div class="row mt-2 mb-4">
                            @if ($datos->inscripcion->padre)
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100 ">
                                        <div class="card-header bg-padre text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-male me-2"></i>Padre
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Nombre</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->persona->primer_nombre ?? '' }}
                                                            {{ $datos->inscripcion->padre->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->inscripcion->padre->persona->primer_apellido ?? '' }}
                                                            {{ $datos->inscripcion->padre->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->inscripcion->padre->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->inscripcion->padre->persona->telefono_dos_completo)
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->padre->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->padre->persona->email)
                                                    <div class="col-md-12">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->padre->persona->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->inscripcion->padre->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->inscripcion->padre->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->padre->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->padre->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->padre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->inscripcion->padre->convivenciaestudiante_representante === 'si')
                                                                    <span class="badge-sm badge-yes">Sí</span>
                                                                @else
                                                                    <span class="badge-sm badge-no">No</span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($datos->inscripcion->madre)
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-madre text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-female me-2"></i>Madre
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Nombre</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->persona->primer_nombre ?? '' }}
                                                            {{ $datos->inscripcion->madre->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->inscripcion->madre->persona->primer_apellido ?? '' }}
                                                            {{ $datos->inscripcion->madre->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->inscripcion->madre->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->inscripcion->madre->persona->telefono_dos_completo)
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->madre->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->madre->persona->email)
                                                    <div class="col-md-12">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->madre->persona->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->inscripcion->madre->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->inscripcion->madre->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->madre->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->madre->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->madre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->inscripcion->madre->convivenciaestudiante_representante === 'si')
                                                                    <span class="badge-sm badge-yes">Sí</span>
                                                                @else
                                                                    <span class="badge-sm badge-no">No</span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            @if ($datos->inscripcion->representanteLegal)
                                <div class="col-md-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-representante-legal text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-user-tie me-2"></i>Representante Legal
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Nombre</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->primer_nombre ?? '' }}
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->primer_apellido ?? '' }}
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->inscripcion->representanteLegal->representante->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->inscripcion->representanteLegal->representante->persona->telefono_dos_completo)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->representante->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->inscripcion->representanteLegal->representante->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->representante->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->inscripcion->representanteLegal->representante->convivenciaestudiante_representante === 'si')
                                                                    <span class="badge-sm badge-yes">Sí</span>
                                                                @else
                                                                    <span class="badge-sm badge-no">No</span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <hr class="my-2" style="border-top: 2px dashed #e5e7eb;">
                                            <div class="row">
                                                <h6 class="mb-3">
                                                    Información Legal
                                                </h6>
                                                <hr class="mb-0">
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Banco</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->banco->codigo_banco ?? ' ' }}-{{ $datos->inscripcion->representanteLegal->banco->nombre_banco ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Tipo de cuenta</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->tipo_cuenta ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Parentesco</span>
                                                        <span class="detail-value">

                                                            @if ($datos->inscripcion->representanteLegal->parentesco === 'Padre')
                                                                <span class="badge-sm bg-info">
                                                                    {{ $datos->inscripcion->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @elseif ($datos->inscripcion->representanteLegal->parentesco === 'Madre')
                                                                <span class="badge-sm bg-danger">
                                                                    {{ $datos->inscripcion->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @else
                                                                <span class="badge-sm bg-grey">
                                                                    {{ $datos->inscripcion->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->inscripcion->representanteLegal->correo_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->correo_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->representanteLegal->pertenece_a_organizacion_representante === 1)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Organización</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->cual_organizacion_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->representanteLegal->serial_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Serial Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->serial_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->representanteLegal->codigo_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Código Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->codigo_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->inscripcion->representanteLegal->codigo_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Código Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->inscripcion->representanteLegal->codigo_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Dirección</span>
                                                        <span class="detail-value">
                                                            {{ $datos->inscripcion->representanteLegal->direccion_representante }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (!$datos->inscripcion->padre && !$datos->inscripcion->madre && !$datos->inscripcion->representanteLegal)
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No se han registrado representantes para este estudiante
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
                <div class="mb-3">
                    <div class="section-title">
                        <i class="fas fa-comment-dots text-secondary"></i>
                        <span>Observaciones</span>
                    </div>

                    <div class="card shadow-sm border-0 mt-2">
                        <div class="card-body bg-light">
                            @if ($datos->observaciones)
                                <p class="mb-0" style="white-space: pre-line;">{{ $datos->observaciones }}</p>
                            @else
                                <p class="text-muted mb-0 text-center">
                                    <i class="fas fa-info-circle me-2"></i>Sin observaciones registradas
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-view">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
