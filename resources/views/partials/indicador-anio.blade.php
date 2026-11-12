@php
    $anioModel = \App\Support\AnioEscolarSeleccionado::modelo();
    $esActual = \App\Support\AnioEscolarSeleccionado::esActual();
@endphp

@if($anioModel)
    <li class="nav-item d-flex align-items-center mr-2">
        <div class="d-inline-flex align-items-center px-3 py-1"
             style="background-color: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 999px; font-size: 0.84rem; line-height: 1.25; color: #0F172A; gap: 0.5rem;">
            <i class="far fa-calendar-alt" style="color: #64748B; font-size: 0.85rem;"></i>
            <span style="color: #64748B; font-weight: 500;">Año:</span>
            <span style="color: #0F172A; font-weight: 600;">
                {{ $anioModel->inicio_anio_escolar?->format('Y') }} - {{ $anioModel->cierre_anio_escolar?->format('Y') }}
            </span>
            @if(!$esActual)
                <span class="d-inline-flex align-items-center px-2 py-0.5"
                      style="background-color: #FEE2E2; color: #991B1B; border-radius: 999px; font-size: 0.72rem; font-weight: 600; gap: 0.35rem;"
                      title="Operando en modo de solo lectura">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #EF4444; display: inline-block;"></span>
                    Solo consulta
                </span>
            @else
                <span class="d-inline-flex align-items-center px-2 py-0.5"
                      style="background-color: #DCFCE7; color: #166534; border-radius: 999px; font-size: 0.72rem; font-weight: 600; gap: 0.35rem;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10B981; display: inline-block;"></span>
                    Activo
                </span>
            @endif

            @if(auth()->check() && auth()->user()->can('anio_escolar.cambiar'))
                <span style="width: 1px; height: 14px; background-color: #CBD5E1; display: inline-block;"></span>
                <a href="{{ route('admin.anio-escolar.seleccionar') }}"
                   class="d-inline-flex align-items-center text-decoration-none"
                   style="color: #4A6FC1; font-size: 0.8rem; font-weight: 600; gap: 0.3rem;"
                   title="Cambiar año escolar">
                    <i class="fas fa-exchange-alt" style="font-size: 0.72rem;"></i>
                    <span>Cambiar</span>
                </a>
            @endif
        </div>
    </li>
@endif
