# Cambio de Usos del Año Escolar Seleccionado: Módulos de Horario y Docentes

## 1. Contexto y Objetivo
Migración de los módulos de **Horario** y **Docentes** para que utilicen de forma exclusiva y uniforme el servicio `App\Support\AnioEscolarSeleccionado` (`::id()`, `::modelo()`, `::esActual()`), respetando la sesión del usuario en lugar de resolver el año activo por cuenta propia.

---

## 2. Tabla de Modificaciones (archivo:línea → cambio realizado)

| Archivo | Líneas | Cambio Realizado | Justificación / Impacto |
| :--- | :---: | :--- | :--- |
| `app/Http/Controllers/BloqueHorarioController.php` | 6, 14-16 | Reemplazado `use App\Models\AnioEscolar;` por `use App\Support\AnioEscolarSeleccionado;`. Cambiado `$anio = AnioEscolar::activos()->first();` por `$anioId = AnioEscolarSeleccionado::id();`. | Los bloques horarios de la API ahora se consultan con el ID del año seleccionado en sesión. |
| `app/Http/Controllers/ConfiguracionHorarioController.php` | 6 | Eliminado import sin uso `use App\Models\AnioEscolar;`. | Limpieza de import huérfano. El controlador ya utilizaba `AnioEscolarSeleccionado::modelo()`. |
| `app/Http/Controllers/DocenteAreaGradoController.php` | 16-21, 63-64, 83 | Eliminado método privado `verificarAnioEscolar()`. Se añadieron `$anioEscolarActivo = AnioEscolarSeleccionado::esActual();` y `$mostrarAnio = AnioEscolarSeleccionado::modelo();` pasándolos en `compact(...)`. | El listado y cabecera de asignaciones de áreas reflejan el año escolar seleccionado. |
| `resources/views/admin/transacciones/docente_area_grado/index.blade.php` | 114-118 | Eliminado bloque `@php` que ejecutaba consultas directas a `AnioEscolar::activos()->first()` y `where('status', 'Extendido')`. La vista ahora consume directamente `$mostrarAnio`. | Cumplimiento de regla de vistas Blade limpias sin queries directas a Eloquent. |
| `app/Http/Controllers/DocenteController.php` | 15, 25-30, 200, 216-227, 253-255, 404-406, 494-503 | 1. Eliminados helpers locales `verificarAnioEscolar()` y `obtenerAnioEscolarActivo()`.<br>2. En `index()`, `$anioEscolarActivo = AnioEscolarSeleccionado::esActual()`.<br>3. En `store()` y `update()`, `$anioEscolar = AnioEscolarSeleccionado::modelo()` con validación controlada de error si es nulo.<br>4. En `disponibilidad()`, se usa `AnioEscolarSeleccionado::modelo()` y se enlaza a `$configService->actual(...)` y `$configService->bloques(...)`. | Las operaciones del CRUD de docentes y la matriz de disponibilidad quedan acopladas al año en sesión. |
| `app/Http/Controllers/DocenteNoDisponibilidadController.php` | 8, 31-34 | Eliminado import sin uso `use App\Models\AnioEscolar;`. En `index()`, se configuró fallback `$anioEscolarId = $request->input('anio_escolar', AnioEscolarSeleccionado::id());`. | Si la consulta API de indisponibilidad no envía parámetro explícito de año, filtra por el año seleccionado. |
| `app/Http/Controllers/GeneradorHorarioController.php` | 8, 27-30, 55, 79, 93-97, 111, 740 | 1. Eliminado helper `getAnioEscolarActivo()` e import `use App\Models\AnioEscolar;`.<br>2. En `generar()`, `validar()`, `obtenerAsignaciones()` y `guardarAsignaciones()`, el fallback de año escolar es `AnioEscolarSeleccionado::id()`.<br>3. En `getAnioEscolarActivoAPI()`, retorna `AnioEscolarSeleccionado::modelo()`. | El generador automático, validador de solapamientos y la interfaz web consumen el año en sesión. |
| `app/Http/Controllers/HorarioController.php` | 12 | Eliminado import sin uso `use App\Models\AnioEscolar;`. | Limpieza de import huérfano. Ya consumía `AnioEscolarSeleccionado::modelo()`. |
| `app/Http/Controllers/PosicionMateriaController.php` | 6 | Eliminado import sin uso `use App\Models\AnioEscolar;`. | Limpieza de import huérfano. Ya consumía `AnioEscolarSeleccionado::modelo()`. |
| `app/Http/Controllers/Seccion_AulaController.php` | 6, 13-18, 25 | Eliminado helper `verificarAnioEscolar()` e import `use App\Models\AnioEscolar;`. En `index()`, `$anioEscolarActivo = AnioEscolarSeleccionado::esActual();`. | Asignación de aula fija por sección vinculada al estado del año seleccionado. |

---

## 3. Excepciones Intencionales (Justificadas)

Los siguientes archivos fueron excluidos intencionalmente de la refactorización hacia la sesión web:

1. **Comandos de Consola Artisan (CLI sin sesión HTTP):**
   - `app/Console/Commands/PoblarCargasAcademicasCommand.php`: Comando CLI sin usuario. Admite parámetro `--anio` y fallback explícito de BD.
   - `app/Console/Commands/PrepararHorarioPruebasCommand.php`: Inicializador de fixtures para tests de desarrollo.
   - `app/Console/Commands/PruebaMasivaHorarioCommand.php`: Comando de prueba de estrés algorítmico del motor de horarios.

2. **Seeders de Base de Datos:**
   - `database/seeders/CargaAcademicaSeeder.php`: Crea registros iniciales para el año activo en DB.
   - `database/seeders/Docente50Seeder.php`: Genera 50 docentes de prueba en el año activo.
   - `database/seeders/HorarioPruebasSeeder.php`: Seeder de pruebas de horarios.
   - `database/seeders/HorarioSeeder.php`: Fixture inicial de horarios.

3. **Fixtures de Pruebas Automatizadas:**
   - Tests en `tests/Feature/` y `tests/Unit/` (`ConfiguracionHorarioControllerTest`, `HorarioCreateTest`, `DocenteRepresentanteTest`, `GeneradorHorarioServiceTest`, `ValidadorHorarioServiceTest`, etc.) mantienen la creación de años con `'status' => 'Activo'` como fixture necesario de base de datos.

---

## 4. Resultado de los Greps Finales de Verificación

Se ejecutó la búsqueda de variantes directas sobre los módulos del alcance (`BloqueHorarioController`, `ConfiguracionHorarioController`, `DocenteAreaGradoController`, `DocenteController`, `DocenteNoDisponibilidadController`, `GeneradorHorarioController`, `HorarioController`, `PosicionMateriaController`, `Seccion_AulaController`, y vistas de horario, docentes, bloque_horario, seccion_aula y docente_area_grado):

```bash
git grep -n -F "AnioEscolar::where('status'" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::where(\"status\"" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::firstWhere('status'" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::whereIn('status'" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::first()" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::latest()" -- [archivos_del_alcance] -> 0 coincidencias
git grep -n -F "AnioEscolar::activos()" -- [archivos_del_alcance] -> 0 coincidencias
```

**Resultado:** 0 coincidencias. No queda ninguna consulta directa de año escolar activo por cuenta propia en Horario ni Docentes.

---

## 5. Evidencia de Pruebas Automatizadas

1. **Suite de Horario:**
   - Comando: `php vendor/phpunit/phpunit/phpunit --filter=Horario`
   - Resultado: **OK (70 tests, 282 assertions)**

2. **Suite de Docentes:**
   - Comando: `php vendor/phpunit/phpunit/phpunit --filter=Docente`
   - Resultado: **OK (28 tests, 177 assertions)**

3. **Nueva suite de pruebas de integración con selector de año:**
   - Archivo: `tests/Feature/HorarioDocentesAnioSeleccionadoTest.php`
   - Pruebas cubiertas:
     - `test_con_anio_anterior_seleccionado_los_indices_cargan_correctamente` (Horario, Docentes, Cargas, Aulas retornan 200).
     - `test_con_anio_anterior_seleccionado_post_devuelve_403_por_middleware` (Mutaciones POST/PUT retornan 403).
     - `test_con_anio_actual_seleccionado_creacion_de_docente_asigna_el_anio_seleccionado` (Creación de docente asigna `anio_escolar_id` seleccionado y sigue flujo).
     - `test_api_anio_escolar_activo_retorna_el_anio_seleccionado_en_sesion` (API `/api/anio-escolar/activo` retorna el año escolar de la sesión).
   - Resultado: **OK (4 tests, 15 assertions)**

---

## 6. Ajustes de Configuración Global de Horarios y Población de Cargas (Feedback Usuaria)

### Tarea 1: Configuración de Horario Global y Estática
- **Migración:** `database/migrations/2026_11_09_000001_make_configuracion_horario_global.php` desvincula la columna `anio_escolar_id` y su índice único de `configuracion_horario`.
- **Modelo:** `ConfiguracionHorario` opera como registro institucional único.
- **Servicio:** `ConfiguracionHorarioService::actual()` recupera la configuración única o crea por defecto la jornada institucional (07:00 AM, 4 bloques de 90 min).
- **Controlador y Vistas:** `ConfiguracionHorarioController` y `resources/views/admin/horario/configuracion.blade.php` ya no dependen del año en sesión para consultar o actualizar los parámetros de la jornada.

### Tarea 2: Población de Cargas Académicas y Eliminación de Errores
- **Secciones:** Se incluyeron las secciones A, B, C, D, E de 1er año en `SeccionSeeder.php` para completar la oferta académica institucional.
- **Seeder de Cargas:** Se rediseñó `CargaAcademicaSeeder.php` para vincular docentes activos con títulos universitarios correspondientes (`EstudiosRealizado`, `AreaEstudioRealizado`, `DetalleDocenteEstudio`), aulas fijas de capacidad suficiente (`seccion_aula`), laboratorios y registros en `cargas_academicas` para todas las secciones activas.
- **Integración:** Incluido en `DatabaseSeeder.php` tras `SeccionAulaSeeder`.
- **Verificación:** Al correr `php artisan migrate:fresh --seed`, la validación de generación de horarios arroja **0 errores críticos**, permitiendo generar el horario completo.

