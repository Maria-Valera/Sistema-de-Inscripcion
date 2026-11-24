@props([
    'target' => null,
    'id' => null,
    'title' => 'Filtros',
    'headerTitle' => 'Filtros de Búsqueda',
    'formId' => null,
    'showButton' => false,
    'action' => null,
    'searchName' => '',
    'searchValue' => '',
    'searchPlaceholder' => 'Buscar...',
    'externalSearchSelector' => '',
    'filters' => [],
    'resultsTarget' => '',
])

@php
    $targetId = $target ?? $id ?? 'filterPanel';
    $resolvedFormId = $formId ?? ($targetId . '-form');
    $isHorariosSaaS = !empty($filters) || !empty($resultsTarget) || !empty($externalSearchSelector);
    $shouldRenderButton = $showButton && !$isHorariosSaaS;
@endphp

@if ($shouldRenderButton)
    <!-- Botón para abrir el panel -->
    <button class="btn btn-filter-global" type="button" data-bs-toggle="offcanvas" data-bs-target="#{{ $targetId }}" aria-controls="{{ $targetId }}">
        <i class="fas fa-sliders-h me-2"></i> {{ $title }}
    </button>
@endif

<!-- Panel Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $targetId }}" aria-labelledby="{{ $targetId }}Label"
    @if($isHorariosSaaS)
        data-filter-panel
        data-results-target="{{ $resultsTarget }}"
        data-external-search-selector="{{ $externalSearchSelector }}"
    @endif>
    <div class="offcanvas-header border-bottom bg-white">
        <h5 class="offcanvas-title fw-bold text-dark mb-0" id="{{ $targetId }}Label">
            <i class="fas fa-sliders-h me-2 text-primary" style="color: #4A6FC1 !important;"></i> {{ $headerTitle }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    @if($isHorariosSaaS && $action)
        <form id="{{ $targetId }}Form" action="{{ $action }}" method="GET" class="filter-panel-form d-flex flex-column flex-grow-1 overflow-hidden">
            <div class="offcanvas-body bg-light">
                <!-- Chips de filtros seleccionados -->
                <div class="filter-chips-section mb-4" id="{{ $targetId }}ChipsSection">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="filter-section-label">Filtros activos</span>
                        <button type="button" class="btn-clear-chips d-none" id="{{ $targetId }}ClearChipsBtn">Quitar todos</button>
                    </div>
                    <div class="filter-chips-list" id="{{ $targetId }}ChipsList"></div>
                    <p class="filter-chips-empty mb-0" id="{{ $targetId }}ChipsEmpty">Selecciona una opción para añadir filtros.</p>
                </div>

                @if ($searchName)
                    <div class="mb-4">
                        <label for="{{ $targetId }}_{{ $searchName }}" class="filter-field-label">Búsqueda rápida</label>
                        <div class="filter-search-wrapper">
                            <span class="filter-search-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input type="text" id="{{ $targetId }}_{{ $searchName }}" name="{{ $searchName }}" value="{{ $searchValue }}" class="form-control filter-search-input" data-filter-input data-filter-label="Búsqueda" placeholder="{{ $searchPlaceholder }}" autocomplete="off">
                            <button type="button" class="filter-search-clear {{ filled($searchValue) ? '' : 'd-none' }}" data-clear-search="{{ $targetId }}_{{ $searchName }}" aria-label="Limpiar búsqueda">&times;</button>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-column gap-3">
                    @foreach ($filters as $filter)
                        @php
                            $fieldName = $filter['name'];
                            $fieldId = $targetId . '_' . $fieldName;
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
                            <select id="{{ $fieldId }}" name="{{ $fieldName }}" class="form-select filter-select-saas" data-filter-input data-filter-label="{{ $fieldLabel }}">
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
            <div class="offcanvas-footer p-3 border-top bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-secondary-modern" id="{{ $targetId }}LimpiarBtn">Limpiar</button>
                <button type="submit" class="btn btn-primary-modern" id="{{ $targetId }}AplicarBtn">Aplicar Filtros</button>
            </div>
        </form>
    @else
        <div class="offcanvas-body bg-light">
            {{ $slot }} <!-- Aquí los módulos inyectan sus selects -->
        </div>
        <div class="offcanvas-footer p-3 border-top bg-white d-flex justify-content-between">
            <button type="button" class="btn btn-secondary-modern" onclick="const f = this.closest('.offcanvas').querySelector('form'); if(f) { f.reset(); if(window.$) $(f).find('select').trigger('change'); }">Limpiar</button>
            <button type="submit" form="{{ $resolvedFormId }}" class="btn btn-primary-modern" onclick="if(!document.getElementById('{{ $resolvedFormId }}')) { const f = this.closest('.offcanvas').querySelector('form'); if(f) f.submit(); }">Aplicar Filtros</button>
        </div>
    @endif
</div>

@once
    <script>
        (function () {
            if (window.__uiFilterPanelGlobalInitialized) return;
            window.__uiFilterPanelGlobalInitialized = true;

            function cerrarOffcanvas(panel) {
                if (!panel) panel = document.querySelector('.offcanvas.show');
                if (panel) {
                    panel.classList.remove('show');
                    setTimeout(() => {
                        if (!panel.classList.contains('show')) {
                            panel.style.visibility = 'hidden';
                        }
                    }, 300);
                }
                document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            function abrirOffcanvas(panel) {
                if (!panel) return;
                panel.style.visibility = 'visible';
                panel.classList.add('show');
                
                let backdrop = document.querySelector('.offcanvas-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'offcanvas-backdrop fade show';
                    document.body.appendChild(backdrop);
                    backdrop.addEventListener('click', function () {
                        cerrarOffcanvas(panel);
                    });
                }
                document.body.style.overflow = 'hidden';
            }

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-bs-toggle="offcanvas"]');
                if (trigger) {
                    e.preventDefault();
                    const selector = trigger.getAttribute('data-bs-target') || trigger.getAttribute('href');
                    if (selector) {
                        const panel = document.querySelector(selector);
                        if (panel) {
                            if (window.bootstrap && window.bootstrap.Offcanvas) {
                                window.bootstrap.Offcanvas.getOrCreateInstance(panel).show();
                            } else {
                                abrirOffcanvas(panel);
                            }
                        }
                    }
                    return;
                }

                const dismiss = e.target.closest('[data-bs-dismiss="offcanvas"]');
                if (dismiss) {
                    e.preventDefault();
                    const panel = dismiss.closest('.offcanvas');
                    if (panel) {
                        if (window.bootstrap && window.bootstrap.Offcanvas) {
                            window.bootstrap.Offcanvas.getOrCreateInstance(panel).hide();
                        } else {
                            cerrarOffcanvas(panel);
                        }
                    }
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const openPanel = document.querySelector('.offcanvas.show');
                    if (openPanel) {
                        if (window.bootstrap && window.bootstrap.Offcanvas) {
                            window.bootstrap.Offcanvas.getOrCreateInstance(openPanel).hide();
                        } else {
                            cerrarOffcanvas(openPanel);
                        }
                    }
                }
            });

            window.addEventListener('ui:open-filter-panel', function (e) {
                const targetId = e.detail?.target;
                if (!targetId) return;
                const el = document.getElementById(targetId);
                if (el) {
                    if (window.bootstrap && window.bootstrap.Offcanvas) {
                        window.bootstrap.Offcanvas.getOrCreateInstance(el).show();
                    } else {
                        abrirOffcanvas(el);
                    }
                }
            });

            // Lógica para paneles con características avanzadas / SaaS (Horarios)
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

                if (chipsEmpty) chipsEmpty.classList.toggle('d-none', count > 0);
                if (clearAllBtn) clearAllBtn.classList.toggle('d-none', count === 0);

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

                    cerrarOffcanvas(panel);

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
                });

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

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFilterPanels);
            } else {
                initFilterPanels();
            }
        })();
    </script>
@endonce
