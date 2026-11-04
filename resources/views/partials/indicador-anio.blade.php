@php
    $anioModel = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActual = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if($anioModel)
    <li class="nav-item d-flex align-items-center mr-2">
        <div class="d-inline-flex align-items-center px-2.5 py-1 rounded shadow-sm text-white" 
             style="background: linear-gradient(135deg, #4f46e5, #4338ca); font-size: 0.85rem; line-height: 1.2;">
            <i class="fas fa-calendar-alt text-white mr-1.5" style="opacity: 0.9;"></i>
            <span class="font-weight-bold mr-1 text-white">Año:</span>
            <span class="text-white font-weight-bold">
                {{ $anioModel->inicio_anio_escolar?->format('Y') }} - {{ $anioModel->cierre_anio_escolar?->format('Y') }}
            </span>
            @if(!$esActual)
                <span class="badge badge-danger text-uppercase ml-2 px-1.5 py-0.5 font-weight-bold" 
                      style="font-size: 0.65rem; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.2);" 
                      title="Operando en modo de solo lectura">
                    <i class="fas fa-lock mr-1"></i>Solo consulta
                </span>
            @else
                <span class="badge badge-light text-uppercase ml-2 px-1.5 py-0.5 font-weight-bold" 
                      style="font-size: 0.65rem; color: #4338ca !important;">
                    Activo
                </span>
            @endif

            <a href="{{ route('admin.anio-escolar.seleccionar') }}" 
               class="btn btn-xs btn-outline-light ml-2 px-2 font-weight-bold" 
               style="border-color: rgba(255, 255, 255, 0.7); line-height: 1.3;" 
               title="Cambiar año escolar">
                <i class="fas fa-sync-alt mr-1"></i>Cambiar
            </a>
        </div>
    </li>
@endif
