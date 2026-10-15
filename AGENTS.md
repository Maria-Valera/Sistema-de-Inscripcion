# AGENTS.md - Sistema de notas (Liceo Juan Guillermo Iribarren)
## Proyecto
Laravel 12, PHP 8.2, MySQL 8, Livewire 4 + Flux, AdminLTE 3 (vistas
admin), Bootstrap 5.3, spatie/laravel-permission, barryvdh/laravel-dompdf.
Idioma de la interfaz, mensajes y comentarios: español de Venezuela.
## Fuente de verdad
docs/notas/modulo_notas.dbml (tablas, columnas, relaciones).
Si el codigo y el DBML difieren, avisa; no decidas por tu cuenta.
## Estructura
- Modelos: app/Models/Notas - Enums: app/Enums/Notas
- Modulos: app/Modules/Notas/{Modulo}/ (Http/Livewire, Services,
Actions, Policies, Requests, routes.php, menu.php)
- Vistas: resources/views/notas/{slug}/
- Pruebas: tests/Feature/Notas/{Modulo}/
- Contratos, eventos y Support compartidos: app/Modules/Notas/Contracts,
Events y Support
## Reglas de trabajo
1. Trabaja SOLO en la carpeta del modulo indicado en el prompt.
Nunca edites otro modulo ni los archivos congelados (migraciones,
modelos, enums, factories, permisos, NotasServiceProvider,
DatabaseSeeder).
2. Nunca importes clases de otro modulo. Comunicate mediante contratos,
eventos y modelos.
3. Escribe SIEMPRE las notas con Eloquent (save/update). Prohibido
upsert o update masivo: los Observers de auditoria deben dispararse.
4. Operaciones de varias tablas dentro de DB::transaction.
5. Nunca borres fisicamente calificaciones, notas ni historiales;
anula con estado. Docentes con notas: desactiva con status, no borres.
6. Autorizacion con Policies y permisos notas.{modulo}.{accion}. No
compruebes roles por nombre en el codigo.
7. Validacion con Form Requests o reglas de Livewire, mensajes en
español.
8. Cada tarea termina con pruebas pasando (usa el framework del
proyecto).
9. No instales paquetes nuevos sin pedir permiso.
10. Al terminar resume: archivos creados, decisiones y dudas.
## Dominio (resumen)
Escala 1-20, aprobatoria 10 (configurable por año). El año tiene
lapsos; cada lapso tiene cortes (plazos de carga). El docente planifica
evaluaciones ponderadas (suman 100 %) y carga calificaciones por
inscripcion. La nota del lapso se congela al cerrar. Mas de 2 areas
pendientes = repite (configurable).