{{-- Indicador de pasos del asistente. Recibe $pasoActual (2 = lapsos, 3 = calendario). --}}
@php
    $pasos = [1 => 'Año escolar', 2 => 'Lapsos', 3 => 'Calendario académico'];
@endphp
<div class="pasos">
    @foreach ($pasos as $numero => $texto)
        <div class="paso {{ $numero < $pasoActual ? 'hecho' : ($numero === $pasoActual ? 'actual' : '') }}">
            <span class="circulo">
                @if ($numero < $pasoActual) <i class="fas fa-check"></i> @else {{ $numero }} @endif
            </span>
            {{ $texto }}
        </div>
    @endforeach
</div>
