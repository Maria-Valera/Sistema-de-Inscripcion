{{--
    Campos del formulario de M5. Lo usan el modal de crear y el de editar.
    Recibe: $corte (Corte o null), $idModal y, al crear, $numeroPorDefecto y $pesoPorDefecto.
--}}
@php
    $usarOld = old('_modal') === $idModal;
    $fecha = fn ($f) => $f?->format('Y-m-d');
    $valor = fn ($campo, $actual) => $usarOld ? old($campo, $actual) : $actual;
    $estadoActual = $valor('estado', $corte?->estado?->value ?? 'planificado');
    $error = fn ($campo) => $usarOld ? $errors->first($campo) : null;
    $peso = $corte ? rtrim(rtrim(number_format((float) $corte->peso_lapso, 2, '.', ''), '0'), '.') : $pesoPorDefecto;
@endphp

<input type="hidden" name="_modal" value="{{ $idModal }}">

<div class="row">
    <div class="col-md-3 form-group-modern">
        <label for="{{ $idModal }}_numero" class="form-label-modern">Número</label>
        <input type="number" class="form-control-modern" id="{{ $idModal }}_numero" name="numero" min="1"
            value="{{ $valor('numero', $corte?->numero ?? $numeroPorDefecto) }}" required>
        @if ($error('numero')) <small class="mensaje-error">{{ $error('numero') }}</small> @endif
    </div>
    <div class="col-md-6 form-group-modern">
        <label for="{{ $idModal }}_nombre" class="form-label-modern">Nombre</label>
        <input type="text" class="form-control-modern" id="{{ $idModal }}_nombre" name="nombre" maxlength="50"
            value="{{ $valor('nombre', $corte?->nombre ?? ($numeroPorDefecto ? "Corte {$numeroPorDefecto}" : '')) }}" required>
        @if ($error('nombre')) <small class="mensaje-error">{{ $error('nombre') }}</small> @endif
    </div>
    <div class="col-md-3 form-group-modern">
        <label for="{{ $idModal }}_peso_lapso" class="form-label-modern">Vale (puntos)</label>
        <input type="number" class="form-control-modern" id="{{ $idModal }}_peso_lapso" name="peso_lapso" min="0.01" step="0.01"
            value="{{ $valor('peso_lapso', $peso) }}" required>
        @if ($error('peso_lapso')) <small class="mensaje-error">{{ $error('peso_lapso') }}</small> @endif
    </div>
</div>

<div class="row">
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_inicio" class="form-label-modern">Desde <span class="text-muted">(opcional)</span></label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_inicio" name="fecha_inicio"
            value="{{ $valor('fecha_inicio', $fecha($corte?->fecha_inicio)) }}">
        @if ($error('fecha_inicio')) <small class="mensaje-error">{{ $error('fecha_inicio') }}</small> @endif
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_fin" class="form-label-modern">Hasta <span class="text-muted">(opcional)</span></label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_fin" name="fecha_fin"
            value="{{ $valor('fecha_fin', $fecha($corte?->fecha_fin)) }}">
        @if ($error('fecha_fin')) <small class="mensaje-error">{{ $error('fecha_fin') }}</small> @endif
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $idModal }}_fecha_limite_carga" class="form-label-modern">Límite para cargar notas</label>
        <input type="date" class="form-control-modern" id="{{ $idModal }}_fecha_limite_carga" name="fecha_limite_carga"
            value="{{ $valor('fecha_limite_carga', $fecha($corte?->fecha_limite_carga)) }}" required>
        @if ($error('fecha_limite_carga')) <small class="mensaje-error">{{ $error('fecha_limite_carga') }}</small> @endif
    </div>
</div>

<div class="form-group-modern">
    <label for="{{ $idModal }}_estado" class="form-label-modern">Estado</label>
    <select class="form-control-modern" id="{{ $idModal }}_estado" name="estado" required>
        @foreach (\App\Enums\EstadoCorte::cases() as $estado)
            <option value="{{ $estado->value }}" @selected($estadoActual === $estado->value)>{{ $estado->label() }}</option>
        @endforeach
    </select>
    @if ($error('estado')) <small class="mensaje-error">{{ $error('estado') }}</small> @endif
    <small class="text-muted d-block mt-1">Abierto: los docentes pueden cargar notas de este corte. Cerrado: las notas quedan congeladas.</small>
</div>
