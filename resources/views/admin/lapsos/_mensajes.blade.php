{{-- Mensajes de éxito o error de M4 y M5 (también lo usa admin/cortes/index) --}}
@foreach (['success' => ['alert-success', 'fa-check-circle', 'Éxito'], 'error' => ['alert-danger', 'fa-exclamation-circle', 'Error']] as $clave => [$clase, $icono, $titulo])
    @if (session($clave))
        <div class="alerts-container">
            <div class="alert-modern {{ $clase }} alert alert-dismissible fade show" role="alert">
                <div class="alert-icon"><i class="fas {{ $icono }}"></i></div>
                <div class="alert-content">
                    <h4>{{ $titulo }}</h4>
                    <p>{{ session($clave) }}</p>
                </div>
                <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
@endforeach

{{-- Errores que no son de un campo del modal (por ejemplo, el año sin configuración) --}}
@if ($errors->any() && ! old('_modal'))
    <div class="alerts-container">
        <div class="alert-modern alert-danger alert alert-dismissible fade show" role="alert">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-content">
                <h4>Revisa los datos</h4>
                <p>{{ $errors->first() }}</p>
            </div>
            <button type="button" class="alert-close btn-close" data-bs-dismiss="alert" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif
