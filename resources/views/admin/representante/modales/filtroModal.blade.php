{{-- Panel de Filtros Offcanvas (Design System - Estándar Horarios) --}}
@php
    $activeChips = [];
    if (request()->filled('buscar')) {
        $activeChips[] = ['name' => 'buscar', 'label' => 'Búsqueda: ' . request('buscar')];
    }
    if (request()->filled('es_legal')) {
        $labelLegal = request('es_legal') === '1' ? 'Tipo: Representante Legal' : 'Tipo: Progenitor';
        $activeChips[] = ['name' => 'es_legal', 'label' => $labelLegal];
    }
    if (request()->filled('grado_id')) {
        $gradoSel = $grados->firstWhere('id', request('grado_id'));
        if ($gradoSel) {
            $activeChips[] = ['name' => 'grado_id', 'label' => 'Nivel: ' . $gradoSel->numero_grado . '° Nivel'];
        }
    }
    if (request()->filled('seccion_id')) {
        $activeChips[] = ['name' => 'seccion_id', 'label' => 'Sección: ' . request('seccion_id')];
    }
@endphp

<div class="offcanvas offcanvas-end filter-panel-saas" tabindex="-1" id="modalFiltros"
    aria-labelledby="modalFiltrosLabel" data-filter-panel>

    <!-- Encabezado Limpio con Ícono y Subtítulo en Gris -->
    <div class="offcanvas-header filter-panel-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="filter-panel-title-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="6" y1="12" x2="18" y2="12"></line>
                    <line x1="10" y1="18" x2="14" y2="18"></line>
                </svg>
            </span>
            <div>
                <h5 class="offcanvas-title mb-0 fw-bold" id="modalFiltrosLabel">Filtros de Búsqueda</h5>
                <p class="text-muted mb-0" style="font-size: 0.82rem;">Filtrar representantes en el listado</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <!-- Formulario del Panel -->
    <form id="modalFiltrosForm" action="{{ route('representante.index') }}" method="GET"
        class="filter-panel-form d-flex flex-column flex-grow-1 overflow-hidden m-0">

        <!-- Preservar búsqueda rápida si existe -->
        @if(request()->has('buscar') && filled(request('buscar')))
            <input type="hidden" name="buscar" value="{{ request('buscar') }}">
        @endif

        <div class="offcanvas-body filter-panel-body">

            <!-- Sección de Filtros Activos con Chips -->
            <div class="filter-chips-section mb-4" id="modalFiltrosChipsSection">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="filter-section-label">Filtros activos</span>
                    <button type="button" class="btn-clear-chips {{ count($activeChips) > 0 ? '' : 'd-none' }}" id="modalFiltrosClearChipsBtn">
                        Quitar todos
                    </button>
                </div>
                <div class="filter-chips-list" id="modalFiltrosChipsList"></div>
                <p class="filter-chips-empty mb-0" id="modalFiltrosChipsEmpty">
                    Selecciona una opción para añadir filtros.
                </p>
            </div>

            <!-- Grupo de Selectores con el diseño compacto de Horarios -->
            <div class="d-flex flex-column gap-3">

                <!-- 1. Tipo de Representante -->
                <div class="filter-field-group">
                    <label for="filtro_es_legal" class="filter-field-label">
                        <i class="fas fa-user-tag me-1"></i>
                        <span>Tipo de Representante</span>
                    </label>
                    <select id="filtro_es_legal" name="es_legal" class="form-select select-modern filter-select-saas"
                        data-filter-input data-filter-label="Tipo">
                        <option value="">Todos los tipos</option>
                        <option value="1" {{ request('es_legal') === '1' ? 'selected' : '' }}>Solo Representantes Legales</option>
                        <option value="0" {{ request('es_legal') === '0' ? 'selected' : '' }}>Solo Progenitores</option>
                    </select>
                </div>

                <!-- 2. Nivel Académico -->
                <div class="filter-field-group">
                    <label for="filtro_grado_id" class="filter-field-label">
                        <i class="fas fa-graduation-cap me-1"></i>
                        <span>Nivel Académico</span>
                    </label>
                    <select id="filtro_grado_id" name="grado_id" class="form-select select-modern filter-select-saas"
                        data-filter-input data-filter-label="Nivel">
                        <option value="">Todos los niveles</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                {{ $grado->numero_grado }}° Nivel
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Sección -->
                <div class="filter-field-group">
                    <label for="filtro_seccion_id" class="filter-field-label">
                        <i class="fas fa-layer-group me-1"></i>
                        <span>Sección</span>
                    </label>
                    <select id="filtro_seccion_id" name="seccion_id" class="form-select select-modern filter-select-saas"
                        data-filter-input data-filter-label="Sección">
                        <option value="">Todas las secciones</option>
                        @foreach ($secciones as $seccion)
                            <option value="{{ $seccion->nombre }}" {{ request('seccion_id') == $seccion->nombre ? 'selected' : '' }}>
                                Sección {{ $seccion->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

        </div>

        <!-- Footer con botones alineados a la derecha -->
        <div class="filter-panel-footer d-flex justify-content-end align-items-center gap-2">
            <button type="button" class="btn btn-secondary-modern" id="modalFiltrosLimpiarBtn">
                <i class="fas fa-undo me-1"></i> Limpiar
            </button>
            <button type="submit" class="btn btn-primary-modern" id="modalFiltrosAplicarBtn">
                <i class="fas fa-check me-1"></i> Aplicar Filtros
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('modalFiltros');
    if (!panel) return;

    const form = document.getElementById('modalFiltrosForm');
    const chipsList = document.getElementById('modalFiltrosChipsList');
    const chipsEmpty = document.getElementById('modalFiltrosChipsEmpty');
    const clearAllBtn = document.getElementById('modalFiltrosClearChipsBtn');
    const limpiarBtn = document.getElementById('modalFiltrosLimpiarBtn');

    function syncPanelChips() {
        if (!form || !chipsList) return;
        chipsList.innerHTML = '';
        const inputs = form.querySelectorAll('[data-filter-input]');
        let count = 0;

        inputs.forEach(input => {
            const val = (input.value || '').trim();
            if (!val) return;

            let displayVal = val;
            if (input.tagName === 'SELECT') {
                const opt = input.options[input.selectedIndex];
                displayVal = opt ? opt.text.trim() : val;
            }

            const label = input.dataset.filterLabel || input.name;
            const chip = document.createElement('span');
            chip.className = 'filter-chip-saas';

            const textSpan = document.createElement('span');
            textSpan.textContent = `${label}: ${displayVal}`;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'filter-chip-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.setAttribute('aria-label', `Quitar filtro ${label}`);
            removeBtn.addEventListener('click', function () {
                input.value = '';
                syncPanelChips();
            });

            chip.appendChild(textSpan);
            chip.appendChild(removeBtn);
            chipsList.appendChild(chip);
            count++;
        });

        if (chipsEmpty) chipsEmpty.classList.toggle('d-none', count > 0);
        if (clearAllBtn) clearAllBtn.classList.toggle('d-none', count === 0);
    }

    form.querySelectorAll('[data-filter-input]').forEach(input => {
        input.addEventListener('change', syncPanelChips);
    });

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            form.querySelectorAll('[data-filter-input]').forEach(input => input.value = '');
            syncPanelChips();
        });
    }

    if (limpiarBtn) {
        limpiarBtn.addEventListener('click', function () {
            form.querySelectorAll('[data-filter-input]').forEach(input => input.value = '');
            syncPanelChips();
            const currentUrl = new URL(form.action, window.location.origin);
            const searchVal = form.querySelector('input[name="buscar"]')?.value;
            if (searchVal) {
                currentUrl.searchParams.set('buscar', searchVal);
            }
            window.location.href = currentUrl.toString();
        });
    }

    syncPanelChips();
});
</script>