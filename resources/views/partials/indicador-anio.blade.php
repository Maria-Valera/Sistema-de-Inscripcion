@php
    $anioModel = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActual = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if($anioModel)
    <li class="nav-item d-flex align-items-center">
        <div class="anio-escolar-pill d-inline-flex align-items-center">
            <i class="far fa-calendar-alt anio-icon"></i>
            <span class="anio-label">Año:</span>
            <span class="anio-years">
                {{ $anioModel->inicio_anio_escolar?->format('Y') }} - {{ $anioModel->cierre_anio_escolar?->format('Y') }}
            </span>
            @if(!$esActual)
                <span class="badge-status-consultar d-inline-flex align-items-center text-uppercase"
                      title="Operando en modo de solo lectura">
                    <span class="status-dot dot-danger"></span>
                    Solo consulta
                </span>
            @else
                <span class="badge-status-activo d-inline-flex align-items-center text-uppercase">
                    <span class="status-dot dot-success"></span>
                    ACTIVO
                </span>
            @endif

            @if(auth()->check() && auth()->user()->can('anio_escolar.cambiar'))
                <span class="anio-divider"></span>
                <a href="{{ route('admin.anio-escolar.seleccionar') }}"
                   class="btn-cambiar-anio d-inline-flex align-items-center text-decoration-none"
                   title="Cambiar año escolar">
                    <i class="fas fa-sync-alt"></i>
                    <span>Cambiar</span>
                </a>
            @endif
        </div>
    </li>
@endif
