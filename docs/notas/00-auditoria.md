# Auditoría Técnica del Repositorio - Módulo de Notas
**Liceo General "Juan Guillermo Iribarren"**  
**Fecha:** 13 de octubre de 2026  
**Documento objetivo:** `docs/notas/00-auditoria.md`  
**Entorno:** Laravel 12.0, PHP 8.2, MySQL 8, Livewire 4, Flux 2, AdminLTE 3, PHPUnit 11.

---

## 1. Vinculación de Usuario (`users`) con Persona (`personas`) y Docente (`docentes`)

### 1.1. Análisis de Migraciones Base vs. Migraciones Posteriores
* **Migraciones base (`users` y `personas`):**
  * `database/migrations/0001_01_01_000000_create_users_table.php` (Líneas 14-22):  
    La tabla base `users` se creó únicamente con los campos: `id`, `name`, `email` (con índice `unique`), `email_verified_at`, `password`, `remember_token` y `timestamps`. **No contenía ningún campo de vinculación personal ni clave foránea (`user_id` ni `persona_id`)**.
  * `database/migrations/2025_11_16_170307_create_personas_table.php` (Líneas 11-32):  
    La tabla base `personas` define la identidad de los sujetos escolares con `primer_nombre`, `primer_apellido`, `numero_documento` (único, L18) y `email` (L21: `$table->string('email')->nullable();`). **No tiene `user_id` ni vínculo hacia `users`**.
  * `database/migrations/2025_11_16_175528_create_docentes_table.php` (Líneas 11-23):  
    La tabla `docentes` se vincula exclusivamente con `personas` mediante `persona_id` (L21: `$table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();`) y posee un `anio_escolar_id` nullable (L13-15). **No tiene ni nunca tuvo `user_id`**.
  * **Conclusión de migraciones base:** En el diseño base original **no existía ningún vínculo relacional** entre las cuentas de autenticación (`users`) y las entidades físicas (`personas` / `docentes`).

* **Migración posterior que incorpora `persona_id` a `users`:**
  * `database/migrations/2026_06_23_030003_add_persona_id_to_users_table.php` (Líneas 14-16):  
    Agregó la clave foránea a `users`:
    ```php
    $table->foreignId('persona_id')->nullable()->after('email')->constrained('personas')->nullOnDelete();
    ```
  * **Hallazgo clave:** La columna física `persona_id` **ya existe** en `users` en la base de datos actual, pero es `nullable` y **no tiene restricción de unicidad (`unique`)** en la base de datos.
  * Ninguna migración en todo el repositorio añade `user_id` a la tabla `docentes` ni a `personas`.

### 1.2. Modelos Eloquent (`User`, `Persona`, `Docente`)
* **Modelo `User` (`app/Models/User.php`):**
  * Línea 26: `'persona_id'` está registrado en `$fillable`.
  * Líneas 52-55: Define la relación `belongsTo`:
    ```php
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    ```
  * Líneas 57-65: Helpers de rol/tipo basados en relaciones anidadas:
    * `isDocente()`: evalúa `$this->persona && $this->persona->docente`.
    * `isRepresentante()`: evalúa `$this->persona && $this->persona->representante`.
* **Modelo `Persona` (`app/Models/Persona.php`):**
  * Líneas 85-88: `public function docente() { return $this->hasOne(Docente::class, 'persona_id', 'id'); }`.
  * Líneas 95-98: `public function user() { return $this->hasOne(User::class, 'persona_id'); }`.
* **Modelo `Docente` (`app/Models/Docente.php`):**
  * Línea 23: `'persona_id'` en `$fillable`. No posee `user_id`.
  * Líneas 114-117: `public function persona() { return $this->belongsTo(Persona::class, 'persona_id', 'id'); }`.
  * **No existe método de relación directa hacia `User`**. La navegación debe ser `docente -> persona -> user`.

### 1.3. Controlador y Vistas del Portal Docente
* **Controlador del Portal Docente (`app/Http/Controllers/HomeController.php`):**
  * Líneas 93-110 (`public function docente()`):
    El método de dashboard docente solo consulta métricas agregadas globales (`Grado::where('status', true)->count()`, `AreaFormacion::where('status', true)->count()`) y retorna la vista `dashboard.docente.index`. **No resuelve al docente autenticado ni consulta `auth()->user()`**.
* **Controlador administrativo `DocenteController` (`app/Http/Controllers/DocenteController.php`):**
  * No interactúa con usuarios autenticados ni maneja credenciales de acceso (es un módulo administrativo CRUD).
* **Vista del Dashboard Docente (`resources/views/dashboard/docente/index.blade.php`):**
  * Líneas 326-330: Evidencia textual de la vinculación tentativa y temporal:
    ```php
    @php
        // TEMPORAL: mientras no tengas un usuario docente real para probar,
        // usamos un id de prueba para que la tarjeta siempre se vea.
        // Cuando tengas un usuario docente real, esto vuelve a calcularse solo.
        $docenteId = Auth::user()->persona->docente->id ?? 1;
    @endphp
    ```

### 1.4. Fragilidad del Vínculo por Correo Electrónico
* En partes anteriores del sistema (como `PortalRepresentanteController.php` L24-26 y `InscripcionProsecucion.php` L126-140) y en el seeder maestro (`DatabaseSeeder.php` L96-110), los usuarios se vinculaban buscando representantes por correo (`where('email', $userEmail)`).
* **Problema:** En `personas` (`database/migrations/2025_11_16_170307_create_personas_table.php`, Línea 21), la columna `email` es `$table->string('email')->nullable();`.  
  **`personas.email` NO es obligatorio y NO es único**. Por tanto, enlazar docentes o usuarios mediante correo electrónico es inseguro y genera fallas de colisión o valores nulos.
* **Estado actual de la ruta:** La ruta canónica física y relacional debe apoyarse en la clave foránea `users.persona_id`:
  $$\text{User} \xrightarrow{\text{users.persona\_id}} \text{Persona} \xleftarrow{\text{docentes.persona\_id}} \text{Docente}$$
  Cualquier intento de asociar docente con usuario debe pasar por `Auth::user()->persona->docente`.

---

## 2. Verificación de `anio_escolar_id` en `docente_area_grados`

* **Migración:** `database/migrations/2025_11_28_012622_docente_area_grados.php` (Líneas 11-56).
* **Columnas reales existentes:**
  1. `id`
  2. `docente_estudio_realizado_id` (FK a `detalle_docente_estudios`)
  3. `area_estudio_realizado_id` (nullable, FK a `area_estudio_realizados`)
  4. `grado_id` (nullable, FK a `grados`)
  5. `seccion_id` (nullable, FK a `seccions`)
  6. `grupo_estable_id` (nullable, FK a `grupo_estables`)
  7. `grado_grupo_estable_id` (nullable, FK a `grados`)
  8. `tipo_asignacion` (enum: `'area'`, `'grupo_estable'`, default: `'area'`)
  9. `status` (boolean, default: `true`)
  10. `created_at`, `updated_at`
* **Modelo:** `app/Models/DocenteAreaGrado.php` (Líneas 12-21, `$fillable`).
* **Resultado:** **NO TIENE `anio_escolar_id`**.
* **Impacto crítico:** Además de no tener `anio_escolar_id`, la tabla no apunta directamente a `docentes.id` (pasa por `detalle_docente_estudios`) ni a `area_formacions.id` (pasa por `area_estudio_realizados`). En el año lectivo, la asignación académica depende del año escolar del docente (`docentes.anio_escolar_id`) o del año activo del sistema, lo cual diverge del modelo conceptual simplificado en DBML.

---

## 3. Verificación de `anio_escolar_id` en `seccions`

* **Migración:** `database/migrations/2025_11_27_015522_create_seccions_table.php` (Líneas 14-22).
* **Columnas reales existentes:**
  1. `id`
  2. `nombre` (string, ej. "A", "B")
  3. `cantidad_actual` (integer)
  4. `grado_id` (foreignId -> `grados`, cascadeOnDelete)
  5. `ejecucion_percentil_id` (nullable, foreignId -> `ejecuciones_percentils`, cascadeOnDelete)
  6. `status` (boolean, default: `true`)
  7. `created_at`, `updated_at`
* **Modelo:** `app/Models/Seccion.php` (Líneas 14-20, `$fillable`).
* **Resultado:** **NO TIENE `anio_escolar_id`**.
* **Observación:** El año escolar se encuentra de forma indirecta en `ejecuciones_percentils.anio_escolar_id` (`database/migrations/2025_11_27_015147_create_ejecuciones_percentils_table.php`, L16-18) o a través de las inscripciones cursadas en dicha sección.

---

## 4. Columnas Reales de Tablas Clave

### 4.1. `inscripcions`
* **Migración:** `database/migrations/2025_11_30_234735_create_inscripcions_table.php` (Líneas 11-59).
* **Modelo:** `app/Models/Inscripcion.php` (Líneas 18-36).
* **Columnas reales:**
  1. `id` (bigint unsigned, auto-incremental)
  2. `anio_escolar_id` (bigint unsigned, nullable, FK a `anio_escolars`, nullOnDelete)
  3. `alumno_id` (bigint unsigned, not null, FK a `alumnos`, cascade)
  4. `grado_id` (bigint unsigned, not null, FK a `grados`, cascade)
  5. `seccion_id` (bigint unsigned, nullable, FK a `seccions`, nullOnDelete)
  6. `padre_id` (bigint unsigned, nullable, FK a `representantes`, nullOnDelete)
  7. `madre_id` (bigint unsigned, nullable, FK a `representantes`, nullOnDelete)
  8. `representante_legal_id` (bigint unsigned, nullable, FK a `representante_legal`, nullOnDelete)
  9. `tipo_inscripcion` (enum: `'nuevo_ingreso'`, `'prosecucion'`)
  10. `documentos` (json, nullable)
  11. `estado_documentos` (string, default: `'Pendiente'`)
  12. `observaciones` (text, nullable)
  13. `acepta_normas_contrato` (boolean, default: `false`)
  14. `status` (string, default: `'Activo'`)
  15. `created_at`, `updated_at` (timestamps)
  16. `deleted_at` (softDeletes)

### 4.2. `inasistencias`
* **Estado:** **no encontrado**.
* **Evidencia:** No existe ninguna migración en `database/migrations/` ni ningún modelo en `app/Models/` con el nombre `inasistencias` o `Inasistencia`.
* **Componentes existentes asociados:**
  * Tabla `reposos_permisos` (`database/migrations/2026_07_16_151259_create_reposos_permisos_table.php`, L14-20): define tipos de justificativos de personal (`id`, `nombre_reposo`, `tipo`, `dias_reposo`, `timestamps`).
  * Controlador `InasistenciaController` (`app/Http/Controllers/InasistenciaController.php`, L7-18): únicamente retorna vistas estáticas (`admin.inasistencia.index`, `admin.inasistencia.inasistencia_justificacion`).

### 4.3. `prosecucion_areas`
* **Migración:** `database/migrations/2025_12_19_190908_create_prosecucion_areas_table.php` (Líneas 14-20).
* **Modelo:** `app/Models/ProsecucionArea.php` (Líneas 14-18).
* **Columnas reales:**
  1. `id` (bigint unsigned, auto-incremental)
  2. `inscripcion_prosecucion_id` (bigint unsigned, not null, FK a `inscripcion_prosecucions`, cascadeOnDelete)
  3. `grado_area_formacion_id` (bigint unsigned, not null, FK a `grado_area_formacions`, cascadeOnDelete)
  4. `status` (string, con definición enum: `'Aprobado'`, `'Pendiente'`, `'Reprobado'`)
  5. `created_at`, `updated_at` (timestamps)
* **Diferencia clave con DBML:** En el DBML se asumió que apuntaba directamente a `inscripcions.id` y `area_formacions.id`. En el código real se vincula mediante `inscripcion_prosecucions` y la malla `grado_area_formacions`.

### 4.4. `grado_area_formacions`
* **Migración:** `database/migrations/2025_11_09_025346_create_grado_area_formacions_table.php` (Líneas 11-31).
* **Modelo:** `app/Models/GradoAreaFormacion.php` (Líneas 12-17).
* **Columnas reales:**
  1. `id` (bigint unsigned, auto-incremental)
  2. `codigo` (string, autogenerado por observer/booted)
  3. `grado_id` (bigint unsigned, not null, FK a `grados`, cascade)
  4. `area_formacion_id` (bigint unsigned, not null, FK a `area_formacions`, cascade)
  5. `status` (boolean, default: `true`)
  6. `created_at`, `updated_at` (timestamps)
  * Restricción de unicidad: `$table->unique(['grado_id', 'area_formacion_id']);`.
* **Diferencia clave con DBML:** Las columnas propuestas en DBML (`pensum_id`, `horas_semanales`, `nota_aprobatoria`) **no existen todavía** en la base de datos.

---

## 5. Relación entre Representantes y Alumnos

* **Tabla responsable de la relación:** **`inscripcions`**.
* **Evidencia en base de datos:** `database/migrations/2025_11_30_234735_create_inscripcions_table.php` (Líneas 19-45).
* **Estructura del vínculo:**
  No existe una tabla intermedia dedicada llamada `alumno_representante`. El vínculo entre un estudiante (`alumnos`) y sus representantes es estrictamente pericial y contextual a cada año lectivo mediante la tabla `inscripcions`, que almacena:
  * `alumno_id` $\rightarrow$ Clave foránea a `alumnos.id`.
  * `padre_id` $\rightarrow$ Clave foránea a `representantes.id` (nullable).
  * `madre_id` $\rightarrow$ Clave foránea a `representantes.id` (nullable).
  * `representante_legal_id` $\rightarrow$ Clave foránea a `representante_legal.id` (nullable). A su vez, `representante_legal` se vincula con `representantes.id` (`database/migrations/2025_11_26_123154_create_representante_legal_table.php`, L16-17).
* **Evidencia en modelos:**
  * `app/Models/Inscripcion.php` (Líneas 109-122 y 129-151): Métodos `padre()`, `madre()`, `representanteLegal()` y accessor `representantes()`.
  * `app/Models/Representante.php` (Líneas 70-76):
    ```php
    public function inscripciones()
    {
        return Inscripcion::where(function($query) {
            $query->where('padre_id', $this->id)
                  ->orWhere('madre_id', $this->id)
                  ->orWhere('representante_legal_id', $this->id);
        });
    }
    ```

---

## 6. Roles y Permisos de Spatie Existentes

### 6.1. Roles Existentes
* **Archivo de evidencia:** `database/seeders/RoleSeeder.php` (Líneas 15-22).
* **Nombres reales de roles registrados (guard `web`):**
  1. `Admin`
  2. `Editor`
  3. `User`
  4. `Representante`
  5. `Subdirectora`
  6. `Docente`
* **Observación sobre roles:** El rol propuesto en DBML `Control de Estudios` **no existe** en el seeder ni en la base de datos. En `DatabaseSeeder.php` (L108-109) se asigna activamente el rol `Representante`.

### 6.2. Permisos Existentes
* **Estado:** **no encontrado**.
* **Evidencia:**
  * La estructura de tablas de Spatie fue creada en `database/migrations/2025_11_10_160912_create_permission_tables.php` (Líneas 23-45).
  * No existe **ningún seeder ni migración** que inserte filas en la tabla `permissions`.
  * En `RoleController.php` (Líneas 37-126) únicamente se realizan operaciones CRUD sobre la tabla `roles`, sin asignación de permisos individuales ni invocación a `Permission::create()`.
  * No existen permisos preexistentes del tipo `notas.*` ni de ningún otro módulo institucional.

---

## 7. Versiones de Componentes y Framework de Pruebas

* **Livewire:**
  * Versión configurada: **`^4.3`** (Livewire v4).
  * Evidencia: `composer.json` (Línea 17: `"livewire/livewire": "^4.3"`).
* **Flux:**
  * Versión configurada: **`^2.9`** (Flux v2).
  * Evidencia: `composer.json` (Línea 16: `"livewire/flux": "^2.9"`).
* **AdminLTE:**
  * Paquete Laravel: **`^3.15`** (`jeroennoten/laravel-adminlte`).
  * Asset subyacente: **`v3.2.0`** (`almasaeed2010/adminlte`).
  * Evidencia: `composer.json` (Línea 12: `"jeroennoten/laravel-adminlte": "^3.15"`) y `composer.lock` (Líneas 10-11: `"version": "v3.2.0"`).
* **Framework de Pruebas:**
  * **PHPUnit 11** (PHPUnit, **NO Pest**).
  * Evidencia:
    * `composer.json` (Línea 28): `"phpunit/phpunit": "^11.5.3"`. No existe `pestphp/pest` ni en `require` ni en `require-dev`.
    * `phpunit.xml` (Líneas 1-36): configuración estándar XML de PHPUnit para suits `Unit` y `Feature`.
    * `tests/TestCase.php` (Líneas 5-10): hereda directamente de `Illuminate\Foundation\Testing\TestCase`.
    * `tests/Feature/ExampleTest.php` (Líneas 8-19): implementa tests orientados a clases bajo `Tests\TestCase`.

---

## 8. Tablas del Calendario Académico y del Horario

### 8.1. Módulo de Calendario Académico
El módulo consta de **3 tablas**:

1. **`calendario_academico`**
   * **Migración:** `database/migrations/2026_08_12_012234_create_calendario_academico_table.php` (Líneas 15-40).
   * **Columnas:**
     * `id` (bigint unsigned, PK)
     * `anio_escolar_id` (bigint unsigned, unique, FK a `anio_escolars`, cascadeOnDelete)
     * `origen` (enum: `'pdf'`, `'manual'`)
     * `pdf_original` (string, nullable)
     * `estado` (enum: `'pendiente_revision'`, `'confirmado'`, default: `'pendiente_revision'`)
     * `fecha_confirmacion` (date, nullable)
     * `created_at`, `updated_at` (timestamps)

2. **`calendario_palabras_clave`**
   * **Migración:** `database/migrations/2026_08_12_012244_create_calendario_palabras_clave_table.php` (Líneas 12-35).
   * **Columnas:**
     * `id` (bigint unsigned, PK)
     * `palabra` (string(100))
     * `categoria` (enum: `'no_laborable'`, `'efemeride'`)
     * `activa` (boolean, default: `true`)
     * `agregada_por` (bigint unsigned, nullable, FK a `personas`, nullOnDelete)
     * `created_at`, `updated_at` (timestamps)
     * Restricción de unicidad: `$table->unique(['palabra', 'categoria']);`

3. **`calendario_dias`**
   * **Migraciones:**
     * Creación base: `database/migrations/2026_08_12_012253_create_calendario_dias_table.php` (Líneas 12-56).
     * Modificación de campos: `database/migrations/2026_09_08_183233_add_manual_fields_to_calendario_dias_table.php` (Líneas 15-27).
   * **Columnas:**
     * `id` (bigint unsigned, PK)
     * `calendario_id` (bigint unsigned, not null, FK a `calendario_academico`, cascadeOnDelete)
     * `texto_extraido` (string(500))
     * `mes_pagina` (string(20), nullable)
     * `dia_extraido` (string(30), nullable)
     * `fecha` (date, nullable)
     * `categoria` (enum: `'no_laborable'`, `'dudoso'`, `'laborable'`, modificado por sentencia ALTER en migración posterior)
     * `es_efemeride` (boolean, default: `false`, agregado en migración de 2026_09_08)
     * `color` (string(30), nullable, agregado en migración de 2026_09_08)
     * `confianza` (enum: `'alta'`, `'dudosa'`, `'manual'`)
     * `es_mes_completo` (boolean, default: `false`)
     * `confirmado` (boolean, default: `false`)
     * `aplica_personal` (boolean, default: `true`)
     * `aplica_estudiantes` (boolean, default: `true`)
     * `created_at`, `updated_at` (timestamps)
     * Restricción de unicidad: `$table->unique(['calendario_id', 'fecha', 'texto_extraido'], 'calendario_dias_evitar_duplicados');`

### 8.2. Módulo de Horario
El módulo consta de **8 tablas** (incluyendo aulas y tablas pivote):

1. **`bloque_horarios`**
   * **Migración:** `database/migrations/2026_06_20_013826_create_bloque_horarios_table.php` (Líneas 14-20).
   * **Columnas:** `id`, `hora_inicio` (time), `hora_fin` (time), `status` (boolean, default: `true`), `created_at`, `updated_at`.

2. **`dias_semana`**
   * **Migración:** `database/migrations/2026_07_23_183711_create_dias_semana_table.php` (Líneas 14-19).
   * **Columnas:** `id`, `nombre_dia` (string(20)), `status` (boolean), `created_at`, `updated_at`.

3. **`aulas`**
   * **Migraciones:** `database/migrations/2026_06_14_003456_create_aulas_table.php` (Líneas 14-18) y `database/migrations/2026_06_15_013247_add_status_to_aulas_table.php` (Líneas 13-17).
   * **Columnas:** `id_aula` (bigint unsigned, PK), `nombre_aula` (string), `status` (boolean, default: `true`), `created_at`, `updated_at`.

4. **`horario`**
   * **Migración:** `database/migrations/2026_07_25_175912_create_horario_table.php` (Líneas 14-22).
   * **Columnas:** `id`, `aula_id` (FK a `aulas.id_aula`), `anio_escolar_id` (FK a `anio_escolars.id`), `status` (boolean, default: `true`), `created_at`, `updated_at`.

5. **`horario__bloque_hora`**
   * **Migración:** `database/migrations/2026_07_25_185349_create_horario__bloque_hora_table.php` (Líneas 14-22).
   * **Columnas:** `id_horario_bloque_hora` (bigint unsigned, PK), `horario_id` (FK a `horario.id`), `bloque_hora_id` (FK a `bloque_horarios.id`), `status` (boolean, default: `true`), `created_at`, `updated_at`.

6. **`horario__dias_semana`**
   * **Migración:** `database/migrations/2026_07_25_185518_create_horario__dias_semana_table.php` (Líneas 14-22).
   * **Columnas:** `id_horario_dias_semana` (bigint unsigned, PK), `horario_id` (FK a `horario.id`), `dias_semana_id` (FK a `dias_semana.id`), `status` (boolean, default: `true`), `created_at`, `updated_at`.

7. **`horario__docente__area__formacion`**
   * **Migración:** `database/migrations/2026_07_25_190335_create_horario__docente__area__formacion_table.php` (Líneas 14-21).
   * **Columnas:** `id`, `horario_id` (FK a `horario.id`), `docente_area_grado_id` (FK a `docente_area_grados.id`), `created_at`, `updated_at`.

8. **`horario__seccion`**
   * **Migración:** `database/migrations/2026_07_27_184445_create_horario__seccion_table.php` (Líneas 14-22).
   * **Columnas:** `id`, `horario_id` (FK a `horario.id`), `seccion_id` (FK a `seccions.id`), `status` (boolean, default: `true`), `created_at`, `updated_at`.

---

## 9. Diferencias con `modulo_notas.dbml`

A continuación se resume la comparación entre lo especificado en el diagrama de base de datos (`docs/notas/modulo_notas.dbml`) y el estado real del repositorio analizado:

| Elemento / Tabla en DBML | Definición en `modulo_notas.dbml` | Estado Real en Código / BD | Discrepancia Detectada | Archivo y Línea de Evidencia |
| :--- | :--- | :--- | :--- | :--- |
| **`users.persona_id`** | Columna nueva propuesta (`persona_id unique, ref: - personas.id`) | **Ya implementada** pero nullable y sin restricción unique | La columna ya existe físicamente, pero no es `unique`. Permite nulos y potencialmente colisiones | `database/migrations/2026_06_23_030003_add_persona_id_to_users_table.php:15` |
| **`anio_escolars`** | Columnas: `nombre varchar(20)`, `estado varchar(15)` | Columnas reales: `inicio_anio_escolar`, `cierre_anio_escolar`, `extencion_anio_escolar`, `status enum` | **No existe columna `nombre` ni `estado`**. El nombre se deriva de un cálculo/accessor de fechas en el modelo Eloquent, y el estado se llama `status` | `database/migrations/2025_11_06_105413_create_anio_escolars_table.php:13-16` |
| **`grados`** | Columnas: `id`, `nombre varchar(30)` | Columnas reales: `id`, `numero_grado`, `capacidad_max`, `min_seccion`, `max_seccion`, `status` | **No existe columna `nombre`**. La columna real es `numero_grado` (string) | `database/migrations/2025_11_08_141508_create_grados_table.php:16` |
| **`area_formacions`** | Columnas: `id`, `nombre varchar(100)` | Columnas reales: `id`, `nombre_area_formacion`, `codigo_area`, `siglas`, `status` | **No existe columna `nombre`**. La columna real es `nombre_area_formacion` | `database/migrations/2025_11_08_193903_create_area_formacions_table.php:16` |
| **`seccions.anio_escolar_id`** | Marcado con nota "VERIFICAR si ya incluye anio_escolar_id" | **No existe** `anio_escolar_id` en `seccions` | Se confirma que la tabla `seccions` no posee clave foránea a años escolares | `database/migrations/2025_11_27_015522_create_seccions_table.php:14-22` |
| **`docente_area_grados.anio_escolar_id`** | Columnas asumidas: `docente_id`, `area_formacion_id`, `grado_id`, `seccion_id`, `anio_escolar_id` | Columnas reales: `docente_estudio_realizado_id`, `area_estudio_realizado_id`, `grado_id`, `seccion_id`, `grupo_estable_id`, `tipo_asignacion`, `status` | **NO tiene `anio_escolar_id`**. Además, no se relaciona directamente con `docente_id` ni `area_formacion_id`, sino a través de tablas intermedias de estudios (`detalle_docente_estudios` y `area_estudio_realizados`) | `database/migrations/2025_11_28_012622_docente_area_grados.php:16-51` |
| **`inasistencias`** | Marcada en DBML como "EXISTENTE" (grupo gris) | **no encontrado** en la base de datos | No existe tabla `inasistencias` ni modelo `Inasistencia`. Solo existen tipos de justificación laboral en `reposos_permisos` | `database/migrations/` (búsqueda nula) |
| **`prosecucion_areas`** | Columnas en DBML: `inscripcion_id`, `area_formacion_id`, `resultado_area_id` (propuesta) | Columnas reales: `inscripcion_prosecucion_id`, `grado_area_formacion_id`, `status` | No apunta a `inscripcions` sino a `inscripcion_prosecucions`. No apunta a `area_formacions` sino a la malla `grado_area_formacions`. No tiene `resultado_area_id` | `database/migrations/2025_12_19_190908_create_prosecucion_areas_table.php:16-18` |
| **`grado_area_formacions`** | Columnas propuestas en DBML: `pensum_id`, `horas_semanales`, `nota_aprobatoria` | Columnas reales: `id`, `codigo`, `grado_id`, `area_formacion_id`, `status`, `timestamps` | **No implementadas**. Las columnas `pensum_id`, `horas_semanales` y `nota_aprobatoria` aún no existen físicamente en la migración | `database/migrations/2025_11_09_025346_create_grado_area_formacions_table.php:12-27` |
| **`grupo_intereses`** | Tabla nueva propuesta bajo etiqueta `[PENDIENTE-5]` (nota de posible duplicado) | Ya existe la tabla **`grupo_estables`** | El sistema ya maneja actividades de este tipo en `grupo_estables` (`nombre_grupo_estable`, `status`) y se vinculan en `docente_area_grados` | `database/migrations/2025_11_08_221908_create_grupo_estables_table.php:14-19` |
| **Rol `Control de Estudios`** | Propuesto en nota de tabla `users` | **no encontrado** en roles existentes | Los roles reales sembrados son: `Admin`, `Editor`, `User`, `Representante`, `Subdirectora`, `Docente` | `database/seeders/RoleSeeder.php:15-22` |
| **Permisos Spatie (`permissions`)** | Reglas de negocio exigen autorizaciones `notas.{modulo}.{accion}` | **no encontrado** (0 permisos sembrados) | Existen las tablas del paquete Spatie, pero no hay ningún registro en la tabla `permissions` ni seeder que los cree | `database/seeders/RoleSeeder.php` y `database/migrations/2025_11_10_160912_create_permission_tables.php` |
| **Framework de pruebas** | Mencionado en requerimientos de pruebas (Pest o PHPUnit) | **PHPUnit 11** instalado exclusivamente | Pest no está instalado ni configurado en el proyecto; las pruebas deben escribirse para PHPUnit (`tests/TestCase.php`) | `composer.json:28` y `phpunit.xml:1-36` |
