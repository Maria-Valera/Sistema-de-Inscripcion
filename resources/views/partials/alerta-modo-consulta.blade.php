@php
    $anioModelAlerta = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActualAlerta = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if (!$anioModelAlerta)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Atención: No hay Calendario Escolar activo</h5>
                <p class="mb-0">
                    Debe seleccionar o registrar un Calendario Escolar activo para poder utilizar las funciones del sistema.
                    <a href="{{ route('admin.anio_escolar.index') }}" class="alert-link">Ir a Calendario Escolar</a>
                </p>
            </div>
        </div>
    </div>
@elseif (!$esActualAlerta)
    <div class="alert alert-info alert-dismissible fade show mb-4 shadow-sm" role="alert" style="background-color: #f0f9ff; border-left: 4px solid #0284c7; color: #0c4a6e;">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
            <div>
                <h5 class="alert-heading mb-1 fw-bold text-info">Modo Solo Consulta</h5>
                <p class="mb-0">
                    Estás viendo un año escolar anterior en modo Solo Consulta. No puedes crear, editar ni eliminar registros.
                </p>
            </div>
        </div>
    </div>
@endif

