@props([
    'target' => 'filterPanel',
    'label' => 'Filtrar',
    'activeCount' => 0,
])

<button type="button"
    {{ $attributes->merge([
        'class' => 'btn-filter-saas d-inline-flex align-items-center gap-2',
        'aria-controls' => $target,
        'data-filter-bar-target' => $target,
    ]) }}
    onclick="window.dispatchEvent(new CustomEvent('ui:open-filter-panel', { detail: { target: @js($target) } }))">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="filter-bar-icon">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="6" y1="12" x2="18" y2="12"></line>
        <line x1="10" y1="18" x2="14" y2="18"></line>
    </svg>
    <span>{{ $label }}</span>
    <span class="filter-bar-badge {{ $activeCount > 0 ? '' : 'd-none' }}"
        data-filter-count-for="{{ $target }}">{{ $activeCount }}</span>
</button>

@once
    <style>
        .btn-filter-saas,
        .btn-saas-secondary {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0.625rem 1.05rem;
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.25;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
            transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, color 0.18s ease;
        }

        .btn-filter-saas:hover,
        .btn-saas-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .btn-filter-saas:focus-visible,
        .btn-saas-secondary:focus-visible {
            outline: none;
            border-color: #4A6FC1;
            box-shadow: 0 0 0 3px rgba(74, 111, 193, 0.22);
        }

        .btn-filter-saas .filter-bar-icon,
        .btn-saas-secondary i {
            color: #475569;
            flex-shrink: 0;
        }

        .btn-saas-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #4A6FC1;
            color: #ffffff;
            border: 1px solid #4A6FC1;
            border-radius: 10px;
            padding: 0.625rem 1.15rem;
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.25;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(74, 111, 193, 0.24);
            transition: background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .btn-saas-primary:hover {
            background-color: #3B5BA5;
            border-color: #3B5BA5;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(74, 111, 193, 0.28);
        }

        .header-divider-saas {
            width: 1px;
            height: 24px;
            background-color: #E2E8F0;
            margin: 0 0.25rem;
            flex-shrink: 0;
        }

        .btn-filter-saas .filter-bar-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 0.45rem;
            border-radius: 9999px;
            background-color: #ebf0fa;
            color: #4A6FC1;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
        }
    </style>
@endonce
