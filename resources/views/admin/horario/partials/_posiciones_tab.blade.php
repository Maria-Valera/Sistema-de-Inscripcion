<div class="posicion-card mb-4">
    <div class="posicion-card-header flex-wrap gap-3">
        <div>
            <h2 class="section-title mb-1">
                <i class="fas fa-th text-primary"></i>
                <span>Posiciones Pedagógicas por Materia</span>
            </h2>
            <p class="text-muted small mb-0">
                Define restricciones pedagógicas (preferencias y prohibiciones de horario) para cada área de formación.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary px-3 py-2" style="font-size: 0.8rem; border-radius: 999px;">
                <i class="fas fa-info-circle me-1"></i> Haz clic en las celdas para alternar estados
            </span>
            @if (\App\Support\AnioEscolarSeleccionado::esActual())
                <button type="button" id="btnGuardarPosiciones"
                    class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2"
                    style="border-radius: 10px; font-weight: 600;">
                    <i class="fas fa-save"></i>
                    <span>Guardar Reglas</span>
                </button>
            @endif
        </div>
    </div>
    <div class="posicion-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-modern" for="selectArea">
                    <i class="fas fa-book text-muted"></i>
                    <span>Área de Formación</span>
                </label>
                <select id="selectArea" class="form-select-modern">
                    @foreach ($areas as $a)
                        <option value="{{ $a->id }}" {{ $areaId == $a->id ? 'selected' : '' }}>
                            {{ $a->nombre_area_formacion }} ({{ $a->codigo_area ?? 'S/C' }} - {{ $a->bloques_semanales ?? '?' }} bloques/sem)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label-modern" for="selectGrado">
                    <i class="fas fa-layer-group text-muted"></i>
                    <span>Nivel / Grado</span>
                </label>
                <select id="selectGrado" class="form-select-modern">
                    <option value="0" {{ empty($gradoId) ? 'selected' : '' }}>
                        🌐 Todos los grados (Regla General)
                    </option>
                    @foreach ($grados as $g)
                        <option value="{{ $g->id }}" {{ $gradoId == $g->id ? 'selected' : '' }}>
                            {{ $g->numero_grado }}° Año
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                    * Una regla específica de grado tiene prioridad sobre la regla general.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Leyenda de Estados -->
<div class="legend-card mb-4 shadow-sm">
    <div class="d-flex align-items-center gap-1 text-muted fw-bold me-2" style="font-size: 0.85rem;">
        <i class="fas fa-traffic-light"></i>
        <span>Ciclo al hacer clic:</span>
    </div>
    <div class="legend-item text-secondary">
        <div class="legend-color-box estado-disponible">
            <i class="fas fa-check"></i>
        </div>
        <span>Disponible (Costo 0)</span>
    </div>
    <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
    <div class="legend-item" style="color: #854d0e;">
        <div class="legend-color-box estado-no_preferente">
            <i class="fas fa-exclamation"></i>
        </div>
        <span>No preferente (+1)</span>
    </div>
    <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
    <div class="legend-item" style="color: #9a3412;">
        <div class="legend-color-box estado-no_preferente_fuerte">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <span>No preferente fuerte (+3)</span>
    </div>
    <i class="fas fa-arrow-right text-muted" style="font-size: 0.75rem;"></i>
    <div class="legend-item text-danger">
        <div class="legend-color-box estado-prohibido">
            <i class="fas fa-ban"></i>
        </div>
        <span>Prohibido (Bloqueado)</span>
    </div>

    @if (\App\Support\AnioEscolarSeleccionado::esActual())
        <div class="ms-auto">
            <button type="button" id="btnLimpiarCuadricula" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                <i class="fas fa-eraser"></i>
                <span>Restablecer Todo a Disponible</span>
            </button>
        </div>
    @endif
</div>

<!-- Cuadrícula Días x Bloques -->
<div class="posicion-card">
    <div class="posicion-card-body p-3">
        <div class="table-responsive">
            <table class="tabla-posiciones" id="tablaPosiciones">
                <thead>
                    <tr>
                        <th style="width: 130px;">
                            <i class="far fa-clock me-1"></i> Bloque
                        </th>
                        @foreach ($dias as $dia)
                            <th>{{ $dia->label() }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for ($b = 1; $b <= $numeroBloques; $b++)
                        @php
                            $infoBloque = $bloques->get($b, ['inicio' => '--:--', 'fin' => '--:--']);
                        @endphp
                        <tr>
                            <td class="hora-col">
                                <div class="fw-bold">Bloque {{ $b }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $infoBloque['inicio'] }} - {{ $infoBloque['fin'] }}
                                </div>
                            </td>
                            @foreach ($dias as $dia)
                                @php
                                    $clave = $dia->value . '_' . $b;
                                    $estado = $posicionesMap[$clave] ?? 'disponible';
                                @endphp
                                <td class="celda-posicion estado-{{ $estado }}"
                                    data-dia="{{ $dia->value }}"
                                    data-bloque="{{ $b }}"
                                    data-tipo="{{ $estado }}"
                                    onclick="ciclarEstadoCelda(this)">
                                    <div class="contenido-celda">
                                        @if ($estado === 'disponible')
                                            <span class="pos-badge text-muted">
                                                <i class="fas fa-check"></i> Disponible
                                            </span>
                                        @elseif ($estado === 'no_preferente')
                                            <span class="pos-badge" style="background: rgba(234, 179, 8, 0.2); color: #854d0e;">
                                                <i class="fas fa-exclamation-circle"></i> No pref. (+1)
                                            </span>
                                        @elseif ($estado === 'no_preferente_fuerte')
                                            <span class="pos-badge" style="background: rgba(249, 115, 22, 0.2); color: #9a3412;">
                                                <i class="fas fa-exclamation-triangle"></i> No pref. ++ (+3)
                                            </span>
                                        @elseif ($estado === 'prohibido')
                                            <span class="pos-badge" style="background: rgba(239, 68, 68, 0.2); color: #991b1b;">
                                                <i class="fas fa-ban"></i> Prohibido
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

