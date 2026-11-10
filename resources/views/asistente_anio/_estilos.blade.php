{{-- Estilos del asistente de año nuevo: sin barra lateral (todavía no se "entra" al sistema), como la pantalla de seleccionar año --}}
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<style>
    .main-sidebar, .sidebar, [class*="sidebar-dark-"] { display: none !important; }
    .content-wrapper, body:not(.sidebar-mini-md):not(.sidebar-mini-xs):not(.layout-top-nav) .content-wrapper,
    .main-header, .main-footer { margin-left: 0 !important; }
    .nav-item [data-widget="pushmenu"] { display: none !important; }

    .asistente { max-width: 980px; margin: 1.5rem auto 3rem; padding: 0 1rem; }
    .asistente-tarjeta { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); overflow: hidden; }
    .asistente-cabecera { padding: 1.5rem 2rem; background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; }
    .asistente-cabecera h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
    .asistente-cabecera p { margin: 0.25rem 0 0; opacity: 0.9; }
    .asistente-cuerpo { padding: 1.75rem 2rem; }

    .pasos { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
    .paso { flex: 1; display: flex; align-items: center; gap: 0.6rem; padding: 0.7rem 1rem; border-radius: 12px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.9rem; }
    .paso .circulo { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #d1d5db; color: #fff; font-weight: 800; flex-shrink: 0; }
    .paso.hecho { background: #ecfdf5; color: #065f46; }
    .paso.hecho .circulo { background: #10b981; }
    .paso.actual { background: #eef2ff; color: #3730a3; box-shadow: inset 0 0 0 2px #6366f1; }
    .paso.actual .circulo { background: #4f46e5; }

    .nota-info { background: #f0f9ff; border: 1px solid #bae6fd; color: #075985; border-radius: 12px; padding: 0.9rem 1.1rem; margin-bottom: 1.25rem; font-size: 0.92rem; }
    .mensaje-error { display: block; color: #dc3545; font-size: 0.8rem; margin-top: 4px; }
    .form-control-modern.campo-invalido { border-color: #dc3545; }
    .acciones { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #e5e7eb; }
    .btn-asistente { background: #4f46e5; color: #fff; border: none; border-radius: 10px; padding: 0.75rem 1.5rem; font-weight: 700; }
    .btn-asistente:hover { background: #4338ca; color: #fff; }

    @media (max-width: 768px) {
        .pasos { flex-direction: column; }
        .asistente-cuerpo, .asistente-cabecera { padding: 1.25rem; }
    }
</style>
