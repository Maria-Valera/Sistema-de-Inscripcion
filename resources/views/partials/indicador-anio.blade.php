@php
    $anioModel = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActual = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if($anioModel)
    <li class="nav-item d-flex align-items-center">
        <div class="d-inline-flex align-items-center"
             style="background-color: #1E3A8A !important; border-radius: 10px !important; padding: 10px 18px !important; font-size: 16px !important; line-height: 1.25; color: #FFFFFF !important; gap: 0.65rem; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);">
            <i class="far fa-calendar-alt" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 16px !important;"></i>
            <span style="color: rgba(255, 255, 255, 0.88) !important; font-weight: 500; font-size: 16px !important;">Año:</span>
            <span style="color: #FFFFFF !important; font-weight: 600; font-size: 16px !important;">
                {{ $anioModel->inicio_anio_escolar?->format('Y') }} - {{ $anioModel->cierre_anio_escolar?->format('Y') }}
            </span>
            @if(!$esActual)
                <span class="d-inline-flex align-items-center text-uppercase"
                      style="background-color: #FEE2E2 !important; color: #991B1B !important; border-radius: 999px; padding: 3px 10px !important; font-size: 13px !important; font-weight: 700; letter-spacing: 0.03em; gap: 0.4rem;"
                      title="Operando en modo de solo lectura">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #EF4444; display: inline-block;"></span>
                    Solo consulta
                </span>
            @else
                <span class="d-inline-flex align-items-center text-uppercase"
                      style="background-color: #DCFCE7 !important; color: #166534 !important; border-radius: 999px; padding: 3px 10px !important; font-size: 13px !important; font-weight: 700; letter-spacing: 0.03em; gap: 0.4rem;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #16A34A; display: inline-block;"></span>
                    ACTIVO
                </span>
            @endif

            @if(auth()->check() && auth()->user()->can('anio_escolar.cambiar'))
                <span style="width: 1px; height: 16px; background-color: rgba(255, 255, 255, 0.25); display: inline-block;"></span>
                <a href="{{ route('admin.anio-escolar.seleccionar') }}"
                   class="d-inline-flex align-items-center text-decoration-none"
                   style="color: #DBEAFE !important; background: transparent !important; border: none !important; text-decoration: none !important; font-size: 15px !important; font-weight: 600; gap: 0.4rem; padding: 0 !important;"
                   title="Cambiar año escolar">
                    <i class="fas fa-sync-alt" style="font-size: 14px !important; color: #DBEAFE !important;"></i>
                    <span>Cambiar</span>
                </a>
            @endif
        </div>
    </li>
@endif
