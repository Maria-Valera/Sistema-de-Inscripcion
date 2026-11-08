{{--
    Campos del formulario de M3. Lo usan el modal de crear y el de editar.
    Recibe: $config (ConfiguracionEvaluacion o null) y $prefijo (para que los id no se repitan).
--}}
@php
    $valor = fn ($campo, $porDefecto) => old($campo, $config?->{$campo} ?? $porDefecto);
    $reglaActual = old('regla_redondeo', $config?->regla_redondeo?->value ?? 'half_up');
@endphp

<div class="row">
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_nota_minima" class="form-label-modern">Nota mínima</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_nota_minima" name="nota_minima"
            min="0" max="99" value="{{ $valor('nota_minima', 1) }}" required>
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_nota_maxima" class="form-label-modern">Nota máxima</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_nota_maxima" name="nota_maxima"
            min="1" max="100" value="{{ $valor('nota_maxima', 20) }}" required>
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_nota_aprobatoria" class="form-label-modern">Aprueba con</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_nota_aprobatoria" name="nota_aprobatoria"
            min="1" max="99" value="{{ $valor('nota_aprobatoria', 10) }}" required>
    </div>
</div>

<div class="form-group-modern">
    <label for="{{ $prefijo }}_regla_redondeo" class="form-label-modern">Redondeo de la nota definitiva</label>
    <select class="form-control-modern" id="{{ $prefijo }}_regla_redondeo" name="regla_redondeo" required>
        @foreach (\App\Enums\ReglaRedondeo::cases() as $regla)
            <option value="{{ $regla->value }}" @selected($reglaActual === $regla->value)>
                {{ $regla->label() }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_num_lapsos" class="form-label-modern">Lapsos del año</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_num_lapsos" name="num_lapsos"
            min="2" max="4" value="{{ $valor('num_lapsos', 3) }}" required>
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_num_cortes" class="form-label-modern">Cortes por lapso</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_num_cortes" name="num_cortes"
            min="1" max="10" value="{{ $valor('num_cortes', 4) }}" required>
    </div>
    <div class="col-md-4 form-group-modern">
        <label for="{{ $prefijo }}_puntos_corte" class="form-label-modern">Puntos por corte</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_puntos_corte" name="puntos_corte"
            min="1" max="100" value="{{ $valor('puntos_corte', 20) }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group-modern">
        <label for="{{ $prefijo }}_momentos_quedada" class="form-label-modern">Momentos para áreas quedadas</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_momentos_quedada" name="momentos_quedada"
            min="1" max="6" value="{{ $valor('momentos_quedada', 4) }}" required>
    </div>
    <div class="col-md-6 form-group-modern">
        <label for="{{ $prefijo }}_max_areas_pendientes" class="form-label-modern">Máximo de áreas pendientes</label>
        <input type="number" class="form-control-modern" id="{{ $prefijo }}_max_areas_pendientes" name="max_areas_pendientes"
            min="0" max="5" value="{{ $valor('max_areas_pendientes', 2) }}" required>
    </div>
</div>
