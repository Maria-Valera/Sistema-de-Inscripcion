@props([
    'id' => 'filterPanel',
    'title' => 'Filtros',
    'action' => url()->current(),
    'searchName' => '',
    'searchValue' => '',
    'searchPlaceholder' => 'Buscar...',
    'externalSearchSelector' => '',
    'filters' => [],
    'resultsTarget' => '',
])

<div class="offcanvas offcanvas-end filter-panel-saas" tabindex="-1" id="{{ $id }}"
    aria-labelledby="{{ $id }}Label" data-filter-panel data-results-target="{{ $resultsTarget }}"
    data-external-search-selector="{{ $externalSearchSelector }}">
    <div class="offcanvas-header filter-panel-header">
        <div class="d-flex align-items-center gap-2">
            <span class="filter-panel-title-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="6" y1="12" x2="18" y2="12"></line>
                    <line x1="10" y1="18" x2="14" y2="18"></line>
                </svg>
            </span>
            <h5 class="offcanvas-title mb-0" id="{{ $id }}Label">{{ $title }}</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <form id="{{ $id }}Form" action="{{ $action }}" method="GET"
        class="filter-panel-form d-flex flex-column flex-grow-1 overflow-hidden">
        <div class="offcanvas-body filter-panel-body">
            <!-- Chips de filtros seleccionados -->
            <div class="filter-chips-section mb-4" id="{{ $id }}ChipsSection">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="filter-section-label">Filtros activos</span>
                    <button type="button" class="btn-clear-chips d-none" id="{{ $id }}ClearChipsBtn">
                        Quitar todos
                    </button>
                </div>
                <div class="filter-chips-list" id="{{ $id }}ChipsList"></div>
                <p class="filter-chips-empty mb-0" id="{{ $id }}ChipsEmpty">
                    Selecciona una opción para añadir filtros.
                </p>
            </div>

            <!-- Buscador con lupa -->
            @if ($searchName)
                <div class="mb-4">
                    <label for="{{ $id }}_{{ $searchName }}" class="filter-field-label">
                        Búsqueda rápida
                    </label>
                    <div class="filter-search-wrapper">
                        <span class="filter-search-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text"
                            id="{{ $id }}_{{ $searchName }}"
                            name="{{ $searchName }}"
                            value="{{ $searchValue }}"
                            class="form-control filter-search-input"
                            data-filter-input
                            data-filter-label="Búsqueda"
                            placeholder="{{ $searchPlaceholder }}"
                            autocomplete="off">
                        <button type="button"
                            class="filter-search-clear {{ filled($searchValue) ? '' : 'd-none' }}"
                            data-clear-search="{{ $id }}_{{ $searchName }}"
                            aria-label="Limpiar búsqueda">
                            &times;
                        </button>
                    </div>
                </div>
            @endif

            <!-- Selectores dinámicos -->
            <div class="d-flex flex-column gap-3">
                @foreach ($filters as $filter)
                    @php
                        $fieldName = $filter['name'];
                        $fieldId = $id . '_' . $fieldName;
                        $fieldLabel = $filter['label'] ?? $fieldName;
                        $fieldIcon = $filter['icon'] ?? null;
                        $fieldPlaceholder = $filter['placeholder'] ?? 'Todos';
                        $fieldValue = (string) ($filter['value'] ?? '');
                        $fieldOptions = $filter['options'] ?? [];
                    @endphp
                    <div class="filter-field-group">
                        <label for="{{ $fieldId }}" class="filter-field-label">
                            @if ($fieldIcon)
                                <i class="{{ $fieldIcon }} me-1"></i>
                            @endif
                            <span>{{ $fieldLabel }}</span>
                        </label>
                        <select id="{{ $fieldId }}"
                            name="{{ $fieldName }}"
                            class="form-select filter-select-saas"
                            data-filter-input
                            data-filter-label="{{ $fieldLabel }}">
                            <option value="">{{ $fieldPlaceholder }}</option>
                            @foreach ($fieldOptions as $option)
                                @php
                                    $optValue = (string) ($option['value'] ?? '');
                                    $optLabel = $option['label'] ?? $optValue;
                                @endphp
                                <option value="{{ $optValue }}" {{ $fieldValue !== '' && $fieldValue === $optValue ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            {{ $slot }}
        </div>

        <div class="filter-panel-footer">
            <button type="button" class="btn-clear-filters" id="{{ $id }}LimpiarBtn">
                Limpiar
            </button>
            <button type="submit" class="btn-apply-filters" id="{{ $id }}AplicarBtn">
                Aplicar Filtros
            </button>
        </div>
    </form>
</div>

@once
    <style>
        .filter-panel-saas {
            width: 420px !important;
            max-width: 92vw !important;
            background-color: #ffffff !important;
            border-left: none !important;
            border-radius: 20px 0 0 20px;
            box-shadow: -12px 0 36px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .filter-panel-header {
            padding: 1.35rem 1.5rem 1.1rem;
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        .filter-panel-header .offcanvas-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .filter-panel-title-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background-color: #ebf0fa;
            color: #4A6FC1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .filter-panel-body {
            padding: 1.25rem 1.5rem;
            overflow-y: auto;
            background-color: #ffffff;
        }

        .filter-section-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .filter-chips-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.85rem 1rem;
        }

        .filter-chips-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-chips-empty {
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .filter-chip-saas {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background-color: #ebf0fa;
            color: #4A6FC1;
            border: 1px solid rgba(74, 111, 193, 0.18);
            border-radius: 9999px;
            padding: 0.32rem 0.55rem 0.32rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .filter-chip-remove {
            width: 18px;
            height: 18px;
            border: none;
            border-radius: 50%;
            background-color: rgba(74, 111, 193, 0.14);
            color: #4A6FC1;
            font-size: 0.9rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .filter-chip-remove:hover {
            background-color: #4A6FC1;
            color: #ffffff;
        }

        .btn-clear-chips {
            background: transparent;
            border: none;
            padding: 0;
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
        }

        .btn-clear-chips:hover {
            color: #0f172a;
            text-decoration: underline;
        }

        .filter-field-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .filter-field-label i {
            color: #4A6FC1;
        }

        .filter-search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .filter-search-icon {
            position: absolute;
            left: 0.9rem;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            pointer-events: none;
            transition: color 0.15s ease;
        }

        .filter-search-wrapper:focus-within .filter-search-icon {
            color: #4A6FC1;
        }

        .filter-search-input {
            width: 100%;
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            padding: 0.65rem 2.3rem 0.65rem 2.5rem !important;
            font-size: 0.9rem;
            color: #0f172a;
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .filter-search-input::placeholder {
            color: #94a3b8;
        }

        .filter-search-clear {
            position: absolute;
            right: 0.75rem;
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1.15rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.15rem 0.3rem;
        }

        .filter-search-clear:hover {
            color: #334155;
        }

        .filter-select-saas {
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            padding: 0.65rem 2.25rem 0.65rem 0.95rem !important;
            font-size: 0.9rem;
            color: #0f172a;
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .filter-search-input:focus,
        .filter-select-saas:focus {
            border-color: #4A6FC1 !important;
            box-shadow: 0 0 0 3px rgba(74, 111, 193, 0.18) !important;
            outline: none !important;
        }

        .filter-panel-footer {
            padding: 1rem 1.5rem 1.35rem;
            background-color: #ffffff;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .btn-clear-filters {
            background: transparent;
            border: none;
            color: #475569;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.7rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: color 0.15s ease;
        }

        .btn-clear-filters:hover {
            color: #0f172a;
        }

        .btn-apply-filters {
            flex: 1;
            background-color: #4A6FC1;
            border: none;
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(74, 111, 193, 0.25);
            transition: background-color 0.18s ease, box-shadow 0.18s ease, transform 0.15s ease;
        }

        .btn-apply-filters:hover {
            background-color: #3B5BA5;
            box-shadow: 0 6px 16px rgba(74, 111, 193, 0.32);
        }

        .btn-apply-filters:disabled {
            opacity: 0.7;
            cursor: wait;
        }
    </style>

    <script>
        (function () {
            if (window.__uiFilterPanelInitialized) return;
            window.__uiFilterPanelInitialized = true;

            function getExternalSearchInput(panel) {
                const selector = panel?.dataset?.externalSearchSelector;
                return selector ? document.querySelector(selector) : null;
            }

            function syncExternalClearButton(extInput) {
                if (!extInput || !extInput.id) return;
                document.querySelectorAll(`[data-clear-search="${extInput.id}"]`).forEach(btn => {
                    btn.classList.toggle('d-none', !(extInput.value || '').trim());
                });
            }

            function buildFilterUrl(form, panel) {
                const url = new URL(form.action, window.location.origin);
                const inputs = form.querySelectorAll('[data-filter-input]');
                inputs.forEach(input => {
                    const val = (input.value || '').trim();
                    if (val !== '') {
                        url.searchParams.set(input.name, val);
                    } else {
                        url.searchParams.delete(input.name);
                    }
                });

                const extInput = getExternalSearchInput(panel);
                if (extInput && extInput.name) {
                    const extVal = (extInput.value || '').trim();
                    if (extVal !== '') {
                        url.searchParams.set(extInput.name, extVal);
                    } else {
                        url.searchParams.delete(extInput.name);
                    }
                }

                url.searchParams.delete('page');
                return url.toString();
            }

            function syncChipsForPanel(panel) {
                const panelId = panel.id;
                const form = document.getElementById(panelId + 'Form');
                const chipsList = document.getElementById(panelId + 'ChipsList');
                const chipsEmpty = document.getElementById(panelId + 'ChipsEmpty');
                const clearAllBtn = document.getElementById(panelId + 'ClearChipsBtn');
                if (!form || !chipsList) return;

                chipsList.innerHTML = '';
                const inputs = form.querySelectorAll('[data-filter-input]');
                let count = 0;

                inputs.forEach(input => {
                    const val = (input.value || '').trim();
                    if (!val) return;

                    let displayVal = val;
                    if (input.tagName === 'SELECT') {
                        const selectedOption = input.options[input.selectedIndex];
                        displayVal = selectedOption ? selectedOption.text.trim() : val;
                    }

                    const label = input.dataset.filterLabel || input.name;
                    const chip = document.createElement('span');
                    chip.className = 'filter-chip-saas';

                    const textSpan = document.createElement('span');
                    textSpan.textContent = `${label}: ${displayVal}`;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'filter-chip-remove';
                    removeBtn.setAttribute('aria-label', `Quitar filtro ${label}`);
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', function () {
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    chip.appendChild(textSpan);
                    chip.appendChild(removeBtn);
                    chipsList.appendChild(chip);
                    count++;
                });

                if (chipsEmpty) {
                    chipsEmpty.classList.toggle('d-none', count > 0);
                }
                if (clearAllBtn) {
                    clearAllBtn.classList.toggle('d-none', count === 0);
                }

                // Actualizar visibilidad de botones de limpiar búsqueda dentro del panel
                form.querySelectorAll('[data-clear-search]').forEach(btn => {
                    const targetInput = document.getElementById(btn.dataset.clearSearch);
                    if (targetInput) {
                        btn.classList.toggle('d-none', !(targetInput.value || '').trim());
                    }
                });
            }

            function updateBarBadges(panelId, count) {
                document.querySelectorAll(`[data-filter-count-for="${panelId}"]`).forEach(badge => {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count <= 0);
                });
            }

            function countActiveInputs(form) {
                let count = 0;
                form.querySelectorAll('[data-filter-input]').forEach(input => {
                    if ((input.value || '').trim() !== '') count++;
                });
                return count;
            }

            async function applyFilters(panel, targetUrl) {
                const form = document.getElementById(panel.id + 'Form');
                const resultsSelector = panel.dataset.resultsTarget;
                const applyBtn = document.getElementById(panel.id + 'AplicarBtn');
                const resultsEl = resultsSelector ? document.querySelector(resultsSelector) : null;

                if (!resultsEl) {
                    window.location.href = targetUrl;
                    return;
                }

                const originalText = applyBtn ? applyBtn.textContent : '';
                if (applyBtn) {
                    applyBtn.disabled = true;
                    applyBtn.textContent = 'Aplicando...';
                }

                try {
                    const response = await fetch(targetUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('Error al consultar filtros');

                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextResults = doc.querySelector(resultsSelector);

                    if (!nextResults) {
                        window.location.href = targetUrl;
                        return;
                    }

                    resultsEl.innerHTML = nextResults.innerHTML;

                    document.querySelectorAll('[data-filter-sync-id]').forEach(targetEl => {
                        const syncId = targetEl.dataset.filterSyncId;
                        const nextSyncEl = doc.querySelector(`[data-filter-sync-id="${syncId}"]`);
                        if (nextSyncEl) {
                            targetEl.innerHTML = nextSyncEl.innerHTML;
                        }
                    });

                    window.history.pushState({}, '', targetUrl);

                    if (form) {
                        updateBarBadges(panel.id, countActiveInputs(form));
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                        const instance = bootstrap.Offcanvas.getInstance(panel);
                        if (instance) instance.hide();
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        resultsEl.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            new bootstrap.Tooltip(el);
                        });
                    }
                } catch (err) {
                    window.location.href = targetUrl;
                } finally {
                    if (applyBtn) {
                        applyBtn.disabled = false;
                        applyBtn.textContent = originalText;
                    }
                }
            }

            function initFilterPanels() {
                document.querySelectorAll('[data-filter-panel]').forEach(panel => {
                    const panelId = panel.id;
                    const form = document.getElementById(panelId + 'Form');
                    if (!form || panel.dataset.initialized === 'true') return;
                    panel.dataset.initialized = 'true';

                    syncChipsForPanel(panel);

                    form.querySelectorAll('[data-filter-input]').forEach(input => {
                        const evtName = input.tagName === 'SELECT' ? 'change' : 'input';
                        input.addEventListener(evtName, () => syncChipsForPanel(panel));
                    });

                    form.querySelectorAll('[data-clear-search]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const targetInput = document.getElementById(this.dataset.clearSearch);
                            if (targetInput) {
                                targetInput.value = '';
                                syncChipsForPanel(panel);
                                targetInput.focus();
                            }
                        });
                    });

                    const clearAll = () => {
                        form.querySelectorAll('[data-filter-input]').forEach(input => {
                            input.value = '';
                        });
                        syncChipsForPanel(panel);
                    };

                    const limpiarBtn = document.getElementById(panelId + 'LimpiarBtn');
                    const clearChipsBtn = document.getElementById(panelId + 'ClearChipsBtn');
                    if (limpiarBtn) limpiarBtn.addEventListener('click', clearAll);
                    if (clearChipsBtn) clearChipsBtn.addEventListener('click', clearAll);

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const targetUrl = buildFilterUrl(form, panel);
                        applyFilters(panel, targetUrl);
                    });

                    const extInput = getExternalSearchInput(panel);
                    if (extInput) {
                        let searchTimer = null;
                        syncExternalClearButton(extInput);

                        extInput.addEventListener('input', function () {
                            syncExternalClearButton(extInput);
                            clearTimeout(searchTimer);
                            searchTimer = setTimeout(() => {
                                applyFilters(panel, buildFilterUrl(form, panel));
                            }, 320);
                        });

                        extInput.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                clearTimeout(searchTimer);
                                applyFilters(panel, buildFilterUrl(form, panel));
                            }
                        });

                        if (extInput.id) {
                            document.querySelectorAll(`[data-clear-search="${extInput.id}"]`).forEach(btn => {
                                btn.addEventListener('click', function () {
                                    clearTimeout(searchTimer);
                                    extInput.value = '';
                                    syncExternalClearButton(extInput);
                                    applyFilters(panel, buildFilterUrl(form, panel));
                                    extInput.focus();
                                });
                            });
                        }
                    }

                    panel.addEventListener('shown.bs.offcanvas', function () {
                        const firstSelect = form.querySelector('.filter-select-saas, .filter-search-input');
                        if (firstSelect) firstSelect.focus();
                    });
                });

                // Permitir quitar un chip activo directamente desde la barra resumen de resultados
                document.addEventListener('click', function (e) {
                    const quickRemoveBtn = e.target.closest('[data-quick-remove-filter]');
                    if (!quickRemoveBtn) return;

                    const panelId = quickRemoveBtn.dataset.panelId;
                    const fieldName = quickRemoveBtn.dataset.quickRemoveFilter;
                    const panel = document.getElementById(panelId);
                    const form = document.getElementById(panelId + 'Form');
                    if (!panel || !form) return;

                    const extInput = getExternalSearchInput(panel);

                    if (fieldName === '__all__') {
                        form.querySelectorAll('[data-filter-input]').forEach(input => {
                            input.value = '';
                        });
                        if (extInput) {
                            extInput.value = '';
                            syncExternalClearButton(extInput);
                        }
                    } else if (extInput && extInput.name === fieldName) {
                        extInput.value = '';
                        syncExternalClearButton(extInput);
                    } else {
                        const input = form.querySelector(`[name="${fieldName}"]`);
                        if (input) input.value = '';
                    }

                    syncChipsForPanel(panel);
                    applyFilters(panel, buildFilterUrl(form, panel));
                });
            }

            window.addEventListener('ui:open-filter-panel', function (e) {
                const targetId = e.detail?.target;
                if (!targetId) return;
                const el = document.getElementById(targetId);
                if (el && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                    bootstrap.Offcanvas.getOrCreateInstance(el).show();
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFilterPanels);
            } else {
                initFilterPanels();
            }
        })();
    </script>
@endonce

