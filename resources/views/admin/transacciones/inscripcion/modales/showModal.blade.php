<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@php
    $esPrimerGrado = ((int) ($datos->grado->numero_grado ?? 0)) === 1;
@endphp
<div class="modal fade" id="viewModal{{ $datos->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $datos->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content modal-modern shadow">
            <div class="modal-header modal-header-view">
                <div class="w-100 text-center">
                    <h5 class="modal-title-view mb-2">Información de Inscripción por Nuevo ingreso</h5>
                    {{ \Carbon\Carbon::parse($datos->anioEscolar->inicio_anio_escolar)->year }}
                    -
                    {{ \Carbon\Carbon::parse($datos->anioEscolar->cierre_anio_escolar)->year }}
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge badge-status badge-{{ strtolower($datos->status) }}">
                            {{ $datos->status }}
                        </span>
                        <span class="badge bg-white text-primary">
                            {{ $datos->grado->numero_grado ?? 'N/A' }}° Nivel Académico
                        </span>
                        @if ($datos->seccion)
                            <span class="badge bg-white text-primary">
                                Sección {{ $datos->seccion->nombre }}
                            </span>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body modal-body-view">
                <div class="mb-4">
                    @php
                        $dato = $datos->alumno->persona;
                        $alumno = $datos->alumno;
                    @endphp
                    <div class="section-title">
                        <i class="fas fa-user-graduate"></i>
                        <span>Datos del Estudiante</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row">
                            <div class="col-md-4 mb-4">
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
                            <div class="col-md-6 mb-4">
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
                            @if ($alumno->etniaIndigena)
                                <div class="col-md-4 ">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Etnia Indígena
                                        </span>
                                        @if ($alumno->etniaIndigena->count() > 0)
                                            <div class="detail-value">
                                                {{ $alumno->etniaIndigena->nombre }}
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
                            @if ($alumno->discapacidades)
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            Discapacidades
                                        </span>
                                        @if ($alumno->discapacidades->count() > 0)
                                            <div class="d-flex flex-wrap mt-1">
                                                @foreach ($alumno->discapacidades as $discapacidad)
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
                        <i class="fas fa-users"></i>
                        <span>Representantes</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row mt-2 mb-4">
                            @if ($datos->padre)
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
                                                            {{ $datos->padre->persona->primer_nombre ?? '' }}
                                                            {{ $datos->padre->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->padre->persona->primer_apellido ?? '' }}
                                                            {{ $datos->padre->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->padre->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->padre->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->padre->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->padre->persona->telefono_dos_completo)
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->padre->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->padre->persona->email)
                                                    <div class="col-md-12">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->padre->persona->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->padre->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->padre->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->padre->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->padre->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->padre->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->padre->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->padre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el
                                                            estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->padre->convive_estudiante === 'Si')
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
                            @if ($datos->madre)
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
                                                            {{ $datos->madre->persona->primer_nombre ?? '' }}
                                                            {{ $datos->madre->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->madre->persona->primer_apellido ?? '' }}
                                                            {{ $datos->madre->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->madre->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->madre->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->madre->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->madre->persona->telefono_dos_completo)
                                                    <div class="col-md-6">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->madre->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->madre->persona->email)
                                                    <div class="col-md-12">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->madre->persona->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->madre->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->madre->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->madre->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->madre->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->madre->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->madre->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->madre->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el
                                                            estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->madre->convive_estudiante === 'Si')
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
                            @if ($datos->representanteLegal)
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
                                                            {{ $datos->representanteLegal->representante->persona->primer_nombre ?? '' }}
                                                            {{ $datos->representanteLegal->representante->persona->segundo_nombre ?? '' }}
                                                            {{ $datos->representanteLegal->representante->persona->primer_apellido ?? '' }}
                                                            {{ $datos->representanteLegal->representante->persona->segundo_apellido ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Cédula</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->representante->persona->tipoDocumento->nombre ?? '' }}-{{ $datos->representanteLegal->representante->persona->numero_documento ?? '' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Telefono</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->representante->persona->telefono_completo }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->representanteLegal->representante->persona->telefono_dos_completo)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Teléfono Secundario</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->representante->persona->telefono_dos_completo }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Género</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->representante->persona->genero->genero ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha de nacimiento</span>
                                                        <span class="detail-value">
                                                            {{ \Carbon\Carbon::parse($datos->representanteLegal->representante->persona?->fecha_nacimiento)->format('d/m/Y') ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(!auth()->user()?->esSoloDocente())
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Lugar de nacimiento</span>
                                                        <span class="detail-value fw-bold">
                                                            {{ $datos->representanteLegal->representante->persona->localidad->municipio->estado->nombre_estado ?? 'N/A' }}
                                                            /
                                                            {{ $datos->representanteLegal->representante->persona->localidad->municipio->nombre_municipio ?? 'N/A' }}
                                                            /
                                                            {{ $datos->representanteLegal->representante->persona->localidad->nombre_localidad ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Direccion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->representante->persona->direccion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Ocupacion</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->representante->ocupacion->nombre_ocupacion ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">¿Convive con el
                                                            estudiante?</span>
                                                        <span class="detail-value">
                                                            <span>
                                                                @if ($datos->representanteLegal->representante->convive_estudiante === 'Si')
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
                                                            {{ $datos->representanteLegal->banco->codigo_banco ?? ' ' }}-{{ $datos->representanteLegal->banco->nombre_banco ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Tipo de cuenta</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->tipo_cuenta ?? 'No especificado' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="detail-item mb-2">
                                                        <span class="detail-label">Parentesco</span>
                                                        <span class="detail-value">
                                                            @if ($datos->representanteLegal->parentesco === 'Padre')
                                                                <span class="badge-sm bg-info">
                                                                    {{ $datos->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @elseif ($datos->representanteLegal->parentesco === 'Madre')
                                                                <span class="badge-sm bg-danger">
                                                                    {{ $datos->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @else
                                                                <span class="badge-sm bg-grey">
                                                                    {{ $datos->representanteLegal->parentesco ?? 'No especificado' }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($datos->representanteLegal->correo_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Correo Electrónico</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->correo_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->representanteLegal->pertenece_a_organizacion_representante === 1)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Organización</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->cual_organizacion_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->representanteLegal->serial_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Serial Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->serial_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->representanteLegal->codigo_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Código Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->codigo_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($datos->representanteLegal->codigo_carnet_patria_representante)
                                                    <div class="col-md-4">
                                                        <div class="detail-item">
                                                            <span class="detail-label">Código Carnet Patria</span>
                                                            <span class="detail-value">
                                                                {{ $datos->representanteLegal->codigo_carnet_patria_representante }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-4">
                                                    <div class="detail-item">
                                                        <span class="detail-label">Dirección</span>
                                                        <span class="detail-value">
                                                            {{ $datos->representanteLegal->direccion_representante }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (!$datos->padre && !$datos->madre && !$datos->representanteLegal)
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
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-file-signature"></i>
                        <span>Datos de Inscripción</span>
                    </div>
                    <div class="card mini-card shadow-sm border-0 p-3 mt-2">
                        <div class="row justify-content-center">
                            @if ($esPrimerGrado)
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            N° Zonificación
                                        </span>
                                        <span
                                            class="detail-value">{{ $datos->nuevoIngreso->numero_zonificacion ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Fecha Inscripción
                                    </span>
                                    <span class="detail-value">
                                        {{ \Carbon\Carbon::parse($datos->fecha_inscripcion)->format('d/m/Y') ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Año Egreso
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->nuevoIngreso->anio_egreso ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Institución Procedencia
                                    </span>
                                    <span class="detail-value">
                                        @if (!$dato->localidad->municipio->estado->pais->nameES === 'Venezuela')
                                            {{ $dato->localidad->municipio->estado->pais->nameES }} /
                                        @endif
                                        {{ $datos->nuevoIngreso->institucionProcedencia->nombre_institucion ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Expresión Literaria
                                    </span>
                                    <span class="detail-value">
                                        {{ $datos->nuevoIngreso->expresionLiteraria->letra_expresion_literaria ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        Aceptó Normas
                                    </span>
                                    <span class="detail-value">
                                        @if ($datos->acepta_normas_contrato)
                                            <span class="badge-sm badge-yes">
                                                <i class="fas fa-check"></i> Sí
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> No
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4" style="border-top: 2px dashed #e5e7eb;">
                <div class="mb-4">
                    <div class="section-title">
                        <i class="fas fa-folder-open"></i>
                        <span>Documentos</span>
                        <span
                            class="badge ms-auto 
                            {{ $datos->estado_documentos === 'Completos' ? 'bg-success' : 'bg-warning' }}">
                            {{ $datos->estado_documentos ?? 'Sin información' }}
                        </span>
                    </div>
                    @php
                        $todosDocumentos = [
                            'partida_nacimiento' => [
                                'label' => 'Partida de Nacimiento',
                                'icon' => 'fa-file-alt',
                                'obligatorio' => true,
                            ],

                            'constancia_aprobacion_primaria' => [
                                'label' => 'Constancia Aprobación Primaria',
                                'icon' => 'fa-stamp',
                                'obligatorio' => true,
                            ],

                            'certificado_calificaciones' => [
                                'label' => 'Certificado de Calificaciones',
                                'icon' => 'fa-certificate',
                                'obligatorio' => true,
                            ],

                            'boletin_6to_grado' => [
                                'label' => 'Boletín 6to Grado',
                                'icon' => 'fa-file-invoice',
                                'obligatorio' => true,
                            ],

                            'notas_certificadas' => [
                                'label' => 'Notas Certificadas',
                                'icon' => 'fa-file-alt',
                                'obligatorio' => !$esPrimerGrado,
                            ],
                            'liberacion_cupo' => [
                                'label' => 'Liberación de Cupo',
                                'icon' => 'fa-file-signature',
                                'obligatorio' => !$esPrimerGrado,
                            ],

                            'copia_cedula_representante' => [
                                'label' => 'Copia Cédula Representante',
                                'icon' => 'fa-id-card',
                                'obligatorio' => false,
                            ],

                            'copia_cedula_estudiante' => [
                                'label' => 'Copia Cédula Estudiante',
                                'icon' => 'fa-id-card',
                                'obligatorio' => false,
                            ],

                            'foto_estudiante' => [
                                'label' => 'Fotografía Estudiante',
                                'icon' => 'fa-camera',
                                'obligatorio' => false,
                            ],

                            'foto_representante' => [
                                'label' => 'Fotografía Representante',
                                'icon' => 'fa-camera',
                                'obligatorio' => false,
                            ],
                            'carnet_vacunacion' => [
                                'label' => 'Carnet de Vacunación',
                                'icon' => 'fa-syringe',
                                'obligatorio' => false,
                            ],
                            'autorizacion_tercero' => [
                                'label' => 'Autorización Tercero',
                                'icon' => 'fa-file-signature',
                                'obligatorio' => false,
                            ],
                        ];
                        $documentosEntregados = is_array($datos->documentos)
                            ? $datos->documentos
                            : (json_decode($datos->documentos, true) ?:
                            []);
                    @endphp
                    @php
                        if ($esPrimerGrado) {
                            unset($todosDocumentos['notas_certificadas'], $todosDocumentos['liberacion_cupo']);
                        }
                    @endphp
                    <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
                        @foreach ($todosDocumentos as $key => $info)
                            @php
                                $entregado = in_array($key, $documentosEntregados);
                            @endphp
                            <div class="documento-wrapper">
                                <div
                                    class="documento-item {{ $entregado ? 'documento-entregado' : 'documento-faltante' }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="documento-icon">
                                            <i class="fas {{ $info['icon'] }}"></i>
                                        </div>
                                        <div class="flex-grow-1 text-start">
                                            <div class="documento-label">
                                                {{ $info['label'] }}
                                            </div>
                                        </div>
                                        <div class="documento-status">
                                            @if ($entregado)
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
