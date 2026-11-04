@php
    $anioModel = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActual = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if($anioModel)
    <li class="nav-item d-flex align-items-center mr-2">
        <div class="d-inline-flex align-items-center px-2 py-1 rounded {{ $esActual ? 'bg-light border' : 'bg-warning text-dark border border-warning font-weight-bold' }}" style="font-size: 0.85rem; line-height: 1.2;">
            <i class="fas fa-calendar-alt {{ $esActual ? 'text-primary' : 'text-dark' }} mr-1"></i>
            <span class="font-weight-bold mr-1">Año:</span>
            <span>
                {{ $anioModel->inicio_anio_escolar?->format('Y') }} - {{ $anioModel->cierre_anio_escolar?->format('Y') }}
            </span>
            @if(!$esActual)
                <span class="badge badge-danger text-uppercase ml-2" style="font-size: 0.65rem;" title="Operando en modo de solo lectura">
                    <i class="fas fa-lock mr-1"></i>Solo consulta
                </span>
            @else
                <span class="badge badge-success text-uppercase ml-2" style="font-size: 0.65rem;">
                    Activo
                </span>
            @endif

            <a href="{{ route('admin.anio-escolar.seleccionar') }}" class="btn btn-xs {{ $esActual ? 'btn-outline-primary' : 'btn-dark' }} ml-2 px-2" title="Cambiar año escolar">
                <i class="fas fa-sync-alt mr-1"></i>Cambiar
            </a>
        </div>
    </li>
@endif

