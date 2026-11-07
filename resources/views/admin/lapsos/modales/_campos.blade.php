{{--
    Campos del formulario de M4. Lo usan el modal de crear y el de editar.
    Recibe: $lapso (Lapso o null), $idModal (para reabrir el modal si el servidor rechaza los datos)
    y $numeroPorDefecto (al crear, el primer número libre).
--}}
@php
    // Los datos rechazados (old) solo se usan en el modal que los envió.
    $usarOld = old('_modal') === $idModal;
    $fecha = fn ($f) => $f?->format('Y-m-d');
    $valor = fn ($campo, $actual) => $usarOld ? old($campo, $actual) : $actual;
    $estadoActual = $valor('estado', $lapso?->estado?->value ?? 'planificado');
    $error = fn ($campo) => $usarOld ? $errors->first($campo) : null;
@endphp

<input type="hidden" name="_modal" value="{{ $idModal }}">

<div class="row">
    <div class="col-md-3 form-group-modern">
        <label for="{{ $idModal }}_numero" class="form-label-modern">Número</label>
        <input type="number" class="form-control-modern" id="{{ $idModal }}_numero" name="numero" min="1"
            value="{{ $valor('numero', $lapso?->numero ?? $numeroPorDefecto) }}" required>
        @if ($error('numero')) <small class="mensaje-error">{{ $error('numero') }}</small> @endif
    </div>
    <div class="col-md-9 form-group-modern">
        <label for="{{ $idModal }}_nombre" class="form-label-modern">Nombre</label>
        <input type="text" class="form-control-modern" id="{{ $idModal }}_nombre" name="nombre" maxlength="50"
            value="{{ $valor('nombre', $lapso?->nombre ?? ($numeroPorDefecto ? "Lapso {$numeroPorDefecto}" : '')) }}" required>
        @if ($error('nombre')) <small class="mensaje-error">{{ $error('nombre') }}</small> @endif
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_inicio" class="form-label-modern">Desde</label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_inicio" name="fecha_inicio"
            value="{{ $valor('fecha_inicio', $fecha($lapso?->fecha_inicio)) }}" required>
        @if ($error('fecha_inicio')) <small class="mensaje-error">{{ $error('fecha_inicio') }}</small> @endif
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_fin" class="form-label-modern">Hasta</label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_fin" name="fecha_fin"
            value="{{ $valor('fecha_fin', $fecha($lapso?->fecha_fin)) }}" required>
        @if ($error('fecha_fin')) <small class="mensaje-error">{{ $error('fecha_fin') }}</small> @endif
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_limite_carga" class="form-label-modern">Límite para cargar notas</label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_limite_carga" name="fecha_limite_carga"
            value="{{ $valor('fecha_limite_carga', $fecha($lapso?->fecha_limite_carga)) }}" required>
        @if ($error('fecha_limite_carga')) <small class="mensaje-error">{{ $error('fecha_limite_carga') }}</small> @endif
    </div>
</div>

<div class="form-group-modern">
    <label for="{{ $idModal }}_estado" class="form-label-modern">Estado</label>
    <select class="form-control-modern" id="{{ $idModal }}_estado" name="estado" required>
        @foreach (\App\Enums\EstadoLapso::cases() as $estado)
            <option value="{{ $estado->value }}" @selected($estadoActual === $estado->value)>{{ $estado->label() }}</option>
        @endforeach
    </select>
    @if ($error('estado')) <small class="mensaje-error">{{ $error('estado') }}</small> @endif
    <small class="text-muted d-block mt-1">Para abrir un lapso, sus cortes deben sumar la nota máxima.</small>
</div>
