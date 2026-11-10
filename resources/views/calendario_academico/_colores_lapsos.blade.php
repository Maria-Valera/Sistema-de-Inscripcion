{{-- Color de cada lapso (franja de fondo en el calendario y tarjetas del asistente). Lo usan calendario_academico/show y asistente_anio/calendario. --}}
<style>
    .lapso-color-1, .celda-dia.lapso-color-1 { background-color: #eef2ff; border-color: #c7d2fe !important; }
    .lapso-color-2, .celda-dia.lapso-color-2 { background-color: #ecfdf5; border-color: #a7f3d0 !important; }
    .lapso-color-3, .celda-dia.lapso-color-3 { background-color: #fff7ed; border-color: #fed7aa !important; }
    .lapso-color-4, .celda-dia.lapso-color-4 { background-color: #fdf2f8; border-color: #fbcfe8 !important; }
    .lapso-etiqueta { display: inline-block; font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: 6px; margin-bottom: 2px; color: #374151; background: rgba(255, 255, 255, 0.75); }
    .leyenda-lapso { display: inline-flex; align-items: center; gap: 0.4rem; margin-right: 1rem; font-size: 0.85rem; }
    .leyenda-lapso .muestra { width: 18px; height: 14px; border-radius: 4px; border: 1px solid; display: inline-block; }
</style>
