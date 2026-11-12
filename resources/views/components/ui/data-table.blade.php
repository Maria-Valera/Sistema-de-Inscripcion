@props([
    'title' => '',
    'subtitle' => '',
    'syncCountId' => '',
    'icon' => '',
    'searchId' => '',
    'searchName' => 'buscar',
    'searchValue' => '',
    'searchPlaceholder' => 'Buscar...',
    'resultsWrapperId' => '',
    'tableClass' => '',
])

<div {{ $attributes->merge(['class' => 'card-saas']) }}>
    @if ($title || $searchId || isset($headerActions))
        <div class="card-saas-header">
            <div class="card-saas-title-group">
                @if ($icon)
                    <div class="card-saas-icon">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    @if ($title)
                        <h3 class="card-saas-title mb-0">{{ $title }}</h3>
                    @endif
                    @if ($subtitle !== '')
                        <p class="card-saas-subtitle mb-0"
                            @if ($syncCountId) data-filter-sync-id="{{ $syncCountId }}" @endif>
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="card-saas-tools">
                @if (isset($headerActions))
                    {{ $headerActions }}
                @endif

                @if ($searchId)
                    <div class="search-pill-saas">
                        <span class="search-pill-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text"
                            id="{{ $searchId }}"
                            name="{{ $searchName }}"
                            value="{{ $searchValue }}"
                            class="form-control search-pill-input"
                            placeholder="{{ $searchPlaceholder }}"
                            aria-label="{{ $searchPlaceholder }}"
                            autocomplete="off">
                        <button type="button"
                            class="search-pill-clear {{ filled($searchValue) ? '' : 'd-none' }}"
                            data-clear-search="{{ $searchId }}"
                            aria-label="Limpiar búsqueda">
                            &times;
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div @if ($resultsWrapperId) id="{{ $resultsWrapperId }}" @endif>
        <div class="table-responsive">
            <table class="table-saas {{ $tableClass }}">
                @isset($thead)
                    <thead>
                        {{ $thead }}
                    </thead>
                @endisset
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>

@once
    <style>
        /* =========================================================
           DATATABLE SAAS REUTILIZABLE (ui.data-table)
           ========================================================= */
        .card-saas {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 6px 16px rgba(15, 23, 42, 0.03);
            overflow: visible;
        }

        @media (min-width: 768px) {
            .card-saas .table-responsive {
                overflow: visible !important;
            }
        }

        .card-saas-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 16px 16px 0 0;
        }

        .card-saas-title-group {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .card-saas-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background-color: #ebf0fa;
            color: #4A6FC1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .card-saas-title {
            font-size: 1.12rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .card-saas-subtitle {
            font-size: 0.82rem;
            color: #64748b;
            margin-top: 0.12rem;
        }

        .card-saas-tools {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-left: auto;
            flex: 0 1 auto;
            justify-content: flex-end;
        }

        /* Buscador redondeado estilo SaaS */
        .search-pill-saas {
            position: relative;
            display: flex;
            align-items: center;
            width: 280px;
            max-width: 100%;
            background-color: #f1f3f5;
            border: 1px solid transparent;
            border-radius: 50px;
            padding: 0.15rem 0.5rem;
            transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .search-pill-saas:focus-within {
            background-color: #ffffff;
            border-color: #4A6FC1;
            box-shadow: 0 0 0 3px rgba(74, 111, 193, 0.16);
        }

        .search-pill-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            padding-left: 0.55rem;
            pointer-events: none;
            transition: color 0.18s ease;
        }

        .search-pill-saas:focus-within .search-pill-icon {
            color: #4A6FC1;
        }

        .search-pill-input,
        .search-pill-input:focus {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
            font-size: 0.88rem;
            color: #0f172a;
            padding: 0.5rem 2rem 0.5rem 0.55rem;
            width: 100%;
            height: auto;
        }

        .search-pill-input::placeholder {
            color: #94a3b8;
        }

        .search-pill-clear {
            position: absolute;
            right: 0.75rem;
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.15rem 0.25rem;
        }

        .search-pill-clear:hover {
            color: #4A6FC1;
        }

        /* Tabla limpia estilo SaaS */
        .table-saas {
            width: 100%;
            min-width: 680px;
            background-color: #ffffff;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .table-saas thead tr {
            background-color: #F1F5F9;
            border-bottom: 1px solid #E2E8F0;
        }

        .table-saas thead th {
            background-color: #F1F5F9;
            color: #334155;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.055em;
            padding: 0.9rem 1.25rem;
            border: none;
            border-left: none;
            border-right: none;
            white-space: nowrap;
        }

        .table-saas tbody tr {
            background-color: #FFFFFF;
            border-bottom: 1px solid #F1F5F9;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .table-saas tbody tr:nth-child(even) {
            background-color: #F8FAFC;
        }

        .table-saas tbody tr:last-child {
            border-bottom: none;
        }

        .table-saas tbody tr:hover,
        .table-saas tbody tr:nth-child(even):hover {
            background-color: #EFF6FF;
        }

        .table-saas tbody tr.row-empty:hover {
            background-color: #FFFFFF;
            cursor: default;
        }

        .table-saas tbody td {
            padding: 0.95rem 1.25rem;
            vertical-align: middle;
            font-size: 0.89rem;
            color: #1e293b;
            border: none;
            border-left: none;
            border-right: none;
        }

        /* Estado vacío moderno */
        .empty-state-saas {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3.5rem 1.5rem;
            gap: 0.65rem;
        }

        .empty-state-saas-title {
            font-size: 1.08rem;
            font-weight: 600;
            color: #0F172A;
            margin: 0.35rem 0 0;
        }

        .empty-state-saas-text {
            font-size: 0.88rem;
            color: #64748B;
            max-width: 420px;
            margin: 0 0 0.75rem;
            line-height: 1.5;
        }

        /* Pastillas / Pills suaves sin borde */
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.32rem 0.78rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            line-height: 1.2;
            white-space: nowrap;
        }

        .pill-badge .pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: currentColor;
            flex-shrink: 0;
        }

        .pill-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .pill-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .pill-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .pill-primary {
            background-color: #ebf0fa;
            color: #2b4c8c;
        }

        .pill-solid-primary {
            background-color: #4A6FC1;
            color: #FFFFFF;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.34rem 0.88rem;
        }

        .pill-neutral {
            background-color: #f1f5f9;
            color: #334155;
        }

        .metric-inline-saas {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Botones sutiles de acción en la tabla */
        .action-btn-saas {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            background-color: transparent;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .action-btn-saas:hover,
        .action-btn-saas:focus,
        .action-btn-saas[aria-expanded="true"] {
            background-color: rgba(74, 111, 193, 0.12);
            color: #4A6FC1;
            outline: none;
        }

        .table-saas .dropdown-menu,
        .dropdown-menu-saas {
            z-index: 1080 !important;
            max-height: 250px;
            overflow-y: auto;
            min-width: 185px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.4rem;
            background-color: #ffffff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
        }

        .dropdown-menu-saas .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.86rem;
            font-weight: 500;
            color: #334155;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .dropdown-menu-saas .dropdown-item:hover {
            background-color: #ebf0fa;
            color: #4A6FC1;
        }

        .dropdown-menu-saas .dropdown-item.text-danger:hover {
            background-color: #fee2e2;
            color: #991b1b !important;
        }
    </style>

    <script>
        (function () {
            if (window.__uiDataTableDropdownsInitialized) return;
            window.__uiDataTableDropdownsInitialized = true;

            const activePortaled = new Map();

            function positionPortaledMenu(toggle, menu) {
                const btnRect = toggle.getBoundingClientRect();
                const menuWidth = Math.max(menu.offsetWidth || 190, 185);
                const naturalHeight = Math.min(menu.scrollHeight || 200, 250);

                const spaceBelow = window.innerHeight - btnRect.bottom - 10;
                const spaceAbove = btnRect.top - 10;
                const openUpward = spaceBelow < naturalHeight && spaceAbove > spaceBelow;

                const availableVertical = openUpward ? spaceAbove : spaceBelow;
                const maxHeight = Math.min(250, Math.max(120, availableVertical));
                const renderedHeight = Math.min(naturalHeight, maxHeight);

                const top = openUpward
                    ? Math.max(8, btnRect.top - renderedHeight - 6)
                    : Math.min(window.innerHeight - renderedHeight - 8, btnRect.bottom + 6);

                const left = Math.min(
                    window.innerWidth - menuWidth - 8,
                    Math.max(8, btnRect.right - menuWidth)
                );

                menu.style.position = 'fixed';
                menu.style.top = `${top}px`;
                menu.style.left = `${left}px`;
                menu.style.right = 'auto';
                menu.style.bottom = 'auto';
                menu.style.transform = 'none';
                menu.style.margin = '0';
                menu.style.maxHeight = `${maxHeight}px`;
                menu.style.overflowY = 'auto';
                menu.style.zIndex = '1080';
            }

            function restorePortaledMenu(menu) {
                if (!menu || !menu._saasOriginalParent) return;

                if (menu.parentNode === document.body) {
                    const parent = menu._saasOriginalParent;
                    const next = menu._saasOriginalNextSibling;
                    if (next && next.parentNode === parent) {
                        parent.insertBefore(menu, next);
                    } else {
                        parent.appendChild(menu);
                    }
                }

                menu.style.position = '';
                menu.style.top = '';
                menu.style.left = '';
                menu.style.right = '';
                menu.style.bottom = '';
                menu.style.transform = '';
                menu.style.margin = '';
                menu.style.maxHeight = '';
                menu.style.overflowY = '';
                menu.style.zIndex = '';
            }

            function enhanceTableDropdowns(root = document) {
                if (typeof bootstrap === 'undefined' || !bootstrap.Dropdown) return;

                root.querySelectorAll('.table-saas [data-bs-toggle="dropdown"]').forEach(btn => {
                    if (btn.dataset.saasDropdownInit === 'true') return;
                    btn.dataset.saasDropdownInit = 'true';
                    btn.setAttribute('data-bs-display', 'static');

                    const existing = bootstrap.Dropdown.getInstance(btn);
                    if (existing) existing.dispose();

                    new bootstrap.Dropdown(btn, {
                        display: 'static',
                    });
                });
            }

            document.addEventListener('shown.bs.dropdown', function (e) {
                const toggle = e.target.closest?.('.table-saas [data-bs-toggle="dropdown"]')
                    || e.target.querySelector?.('.table-saas [data-bs-toggle="dropdown"]');
                if (!toggle) return;

                const dropdown = toggle.closest('.dropdown');
                const menu = toggle._saasMenu || dropdown?.querySelector('.dropdown-menu');
                if (!dropdown || !menu) return;

                toggle._saasMenu = menu;
                menu._saasOriginalParent = dropdown;
                menu._saasOriginalNextSibling = menu.nextSibling;

                document.body.appendChild(menu);
                positionPortaledMenu(toggle, menu);
                activePortaled.set(toggle, menu);
            });

            document.addEventListener('hidden.bs.dropdown', function (e) {
                const toggle = e.target.closest?.('[data-bs-toggle="dropdown"]') || e.target;
                const menu = toggle?._saasMenu;
                if (!menu) return;

                restorePortaledMenu(menu);
                activePortaled.delete(toggle);
            });

            function repositionAll() {
                activePortaled.forEach((menu, toggle) => {
                    if (!document.body.contains(toggle) || !menu.classList.contains('show')) {
                        restorePortaledMenu(menu);
                        activePortaled.delete(toggle);
                        return;
                    }
                    positionPortaledMenu(toggle, menu);
                });
            }

            window.addEventListener('scroll', repositionAll, true);
            window.addEventListener('resize', repositionAll);

            function init() {
                enhanceTableDropdowns(document);

                if (typeof MutationObserver !== 'undefined') {
                    const observer = new MutationObserver(mutations => {
                        mutations.forEach(m => {
                            m.addedNodes.forEach(node => {
                                if (node.nodeType === 1) {
                                    enhanceTableDropdowns(node);
                                }
                            });
                        });
                    });

                    document.querySelectorAll('.card-saas').forEach(card => {
                        observer.observe(card, { childList: true, subtree: true });
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endonce

