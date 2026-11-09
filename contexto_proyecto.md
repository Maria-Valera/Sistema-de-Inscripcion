# Análisis Integral del Sistema de Inscripción y Gestión Escolar
**Institución:** Liceo General "Juan Guillermo Iribarren"  
**Archivo:** `contexto_proyecto.md`  
**Fecha de Elaboración:** Octubre 2026  

---

## 1. ¿De qué trata el proyecto?

El proyecto es un **Sistema Web Integral de Gestión e Inscripción Escolar** diseñado para la educación media general y básica venezolana, contextualizado en el **Liceo General "Juan Guillermo Iribarren"**. Su propósito es automatizar, optimizar y auditar todos los procesos académicos y administrativos que rigen la vida institucional de la escuela.

### Objetivos y Alcance Principal:
1. **Gestión Integral de Inscripciones:**
   - **Nuevo Ingreso:** Registro de aspirantes (primer año y traslados), verificación de cupos, captura de datos socioeconómicos, de salud, tallas de uniforme, procedencia y asignación de representante legal.
   - **Prosecución Escolar:** Reinscripción de estudiantes regulares, control de historial académico, estatus de promoción (promovido o repitiente) y seguimiento detallado de materias pendientes o reprobadas.
2. **Algoritmo Inteligente de Distribución por Percentil:**
   - Balanceo equitativo y técnico de estudiantes en las secciones de cada grado académico basándose en índices antropométricos (edad, peso, estatura) y paridad de género, respetando capacidades mínimas y máximas por aula/sección.
3. **Extracción y Procesamiento Inteligente de Calendario Académico:**
   - Módulo automatizado capaz de leer y parsear en tiempo de ejecución los documentos oficiales en formato **PDF** emitidos por el Ministerio del Poder Popular para la Educación (MPPE), extrayendo por NLP/palabras clave efemérides, días hábiles, feriados y consejos docentes para poblar el calendario institucional con validación manual.
4. **Planificación y Asignación Académica:**
   - Control de la plantilla docente, perfil profesional y títulos obtenidos (`EstudiosRealizados`), vinculación de carga horaria docente por materia (`AreaFormacion`), nivel (`Grado`) y `Seccion`.
   - Programación de horarios escolares, control de bloques pedagógicos, días lectivos y asignación de aulas físicas.
5. **Asistencia y Control Institucional:**
   - Registro de reposos y permisos, gestión y justificación de inasistencias estudiantiles, y formalización de actas de entrevistas a representantes.
6. **Portales y Roles de Usuario:**
   - **Portal Administrativo / Subdirectora:** Configuración global, administración de cupos, asignaciones y aprobaciones.
   - **Portal Docente:** Consulta de carga académica y listas de alumnos asignados por sección.
   - **Portal del Representante:** Módulo de autogestión para registrar la prosecución de sus representados y emisión/descarga del **Carnet Estudiantil**.
   - **Página Web Pública (Home / Vida Estudiantil):** Portal informativo para la comunidad escolar con valores institucionales y proyectos socio-comunitarios.

---

## 2. Tecnologías Utilizadas

El sistema está construido bajo una arquitectura moderna sobre el ecosistema PHP/Laravel y JavaScript, complementado con librerías para reactividad, procesamiento de documentos y diseño responsivo.

### 2.1. Backend
- **Lenguaje:** `PHP ^8.2`
- **Framework Principal:** `Laravel 12.x` (Arquitectura MVC, Eloquent ORM, Service Layer, Repositories y Actions).
- **Motor de Base de Datos:** `MySQL 8.x` (gestionado con migraciones, índices y seeders normalizados).
- **Librerías y Paquetes Clave de Composer:**
  - `spatie/laravel-permission (^6.24)`: Control de acceso basado en roles y permisos (RBAC: Admin, Subdirectora, Docente, Representante, Editor, User).
  - `barryvdh/laravel-dompdf (^3.1)`: Renderizado y generación de documentos y reportes oficiales en formato PDF.
  - `smalot/pdfparser (^2.12)`: Extracción de texto y estructura desde archivos PDF para el análisis inteligente del calendario del MPPE.
  - `diglactic/laravel-breadcrumbs (^10.0)`: Gestión y renderizado de rutas de navegación (migas de pan).
  - `laravel/ui (^4.6)`: Andamiaje de autenticación y sesiones.
  - `laravel/tinker (^2.10)` y `laravel/pail (^1.2)`: Herramientas de depuración interactiva y monitoreo de logs en tiempo real.

### 2.2. Frontend
- **Framework Reactivo Full-Stack:** `Livewire ^4.3` (permite interfaces interactivas dinámicas sin escribir JavaScript redundante, sincronizando estado directamente con el backend).
- **Componentes UI:** `Livewire Flux (^2.9)` (librería de componentes modernos de interfaz).
- **Motor de Plantillas:** `Blade` (layouts modulares, directivas `@extends`, `@section`, `@include` y componentes).
- **Reactividad Cliente:** `Alpine.js (^3.15)` (gestión de modales, estados interactivos y manipulación ligera del DOM).
- **Cliente HTTP:** `Axios (^1.11)` (peticiones asíncronas AJAX para validaciones en vivo como verificación de cédulas, aulas o instituciones existentes).
- **Caruseles e Interactividad:** `Swiper (^12.2)` (slider táctil y responsivo utilizado en la vitrina pública de vida estudiantil).
- **Selects Avanzados:** `Select2 (^4.1)` y `bootstrap-select (^1.13)` (selects dinámicos con búsqueda y filtros).
- **Empaquetador y Build Tool:** `Vite (^7.0)` junto con `laravel-vite-plugin (^2.0)`.

### 2.3. Diseño y Estilos (UI/UX)
- **Panel Administrativo:** `AdminLTE ^3.15` (`jeroennoten/laravel-adminlte`), proveyendo un panel de control corporativo con sidebar colapsable, cards métricas, breadcrumbs y temas oscuro/claro.
- **Frameworks CSS:**
  - `Bootstrap 5.3` (compilado vía Sass en `resources/sass/app.scss` para vistas públicas, portal y autenticación).
  - `Bootstrap 4` (heredado e integrado para soporte del ecosistema AdminLTE 3).
  - `Tailwind CSS ^4.1` (integrado vía `@tailwindcss/vite` y consumido por los componentes Livewire Flux).
- **Preprocesador CSS:** `Sass / SCSS (^1.56)`.
- **Tipografía e Iconografía:**
  - `FontAwesome 6/7` (`@fortawesome/fontawesome-free` con paquetes Solid, Regular y Brands).
  - `Bootstrap Icons` (utilizados en encabezados y landing page).
  - Fuentes web: `Instrument Sans`, `Inter`, `Nunito`.
- **Diseño para Impresión:** Estilos optimizados para documentos PDF tamaño Carta/A4 (vertical y apaisado) y formato credencial tipo carnet escolar.

---

## 3. Identificación de Maestros (Datos Maestros y Catálogos)

Los maestros representan los catálogos y entidades base del sistema que dan sustento a la parametrización de la institución y sus procesos:

### 3.1. Maestros Académicos e Institucionales
1. **Año Escolar (`AnioEscolar`):** Períodos académicos oficiales (ej. 2024-2025, 2025-2026), fechas de inicio/cierre y estados (*Activo*, *Inactivo*, *Extendido*). Controla la vigencia de operaciones.
2. **Grado / Nivel Académico (`Grado`):** Grados cursados (1er año a 5to año), con definición de capacidad máxima y mínima requerida por sección.
3. **Sección (`Seccion`):** Identificadores de sección (A, B, C, D...) vinculados a cada grado escolar.
4. **Aula (`Aula`):** Salones e infraestructuras físicas de la institución con capacidad de estudiantes y estatus activo/inactivo.
5. **Área de Formación (`AreaFormacion`):** Asignaturas del plan curricular nacional (Castellano, Matemáticas, Ciencias Naturales, Geografía, etc.).
6. **Grupo Estable (`GrupoEstable`):** Áreas de formación complementarias y actividades socio-productivas o culturales.
7. **Bloque Horario (`BloqueHorario`):** Franjas horarias pedagógicas definidas por hora de inicio, hora de culminación y orden.
8. **Días de la Semana (`Dias_semana`):** Catálogo de días lectivos (Lunes a Viernes).
9. **Institución de Procedencia (`InstitucionProcedencia`):** Catálogo de planteles educativos de origen de los nuevos estudiantes.
10. **Estudios Realizados (`EstudiosRealizado`):** Grados académicos y títulos de especialización docente (Licenciado, Profesor, TSU, Magister, Doctor, etc.).
11. **Expresión Literaria (`ExpresionLiteraria`):** Habilidades o expresiones culturales y extracurriculares de los alumnos.

### 3.2. Maestros de Personas, Identificación y Demografía
12. **Persona (`Persona`):** Entidad central polimórfica que almacena cédula/documento, nombres, apellidos, fecha y lugar de nacimiento, teléfonos, correos y dirección de residencia.
13. **Tipo de Documento (`TipoDocumento`):** Clasificación de documentos de identidad (V: Venezolano, E: Extranjero, Cédula Escolar, Pasaporte).
14. **Género (`Genero`):** Género biológico (Masculino, Femenino) utilizado para el balanceo de secciones.
15. **Alumno (`Alumno`):** Ficha maestra del estudiante con vínculos a sus tallas, lateralidad, orden de nacimiento y salud.
16. **Docente (`Docente`):** Ficha maestra del docente con código administrativo, fecha de ingreso y vinculación a persona.
17. **Representante (`Representante` y `RepresentanteLegal`):** Registro de padres, madres y representantes legales autorizados.
18. **Talla (`Talla`):** Tallas comerciales e institucionales para camisa, pantalón y calzado.
19. **Lateralidad (`Lateralidad`):** Lateralidad motora del estudiante (Diestro, Zurdo, Ambidiestro).
20. **Orden de Nacimiento (`OrdenNacimiento`):** Posición del estudiante dentro del núcleo fraterno (Primero, Segundo, etc.).
21. **Discapacidad (`Discapacidad`):** Tipos de condiciones o discapacidades físicas, sensoriales o cognitivas.
22. **Etnia Indígena (`EtniaIndigena`):** Catálogo de comunidades y etnias originarias reconocidas.
23. **Ocupación (`Ocupacion`):** Oficios y profesiones desempeñados por los representantes.
24. **Banco (`Banco`):** Entidades bancarias del sistema financiero nacional.
25. **Prefijo Telefónico (`PrefijoTelefono`):** Códigos de área y operadoras telefónicas móviles/fijas (0414, 0424, 0412, 0416, 0251, etc.).

### 3.3. Maestros Geográficos
26. **País (`Pais`):** Catálogo de países (con códigos ISO y nombres en español).
27. **Estado (`Estado`):** División político-territorial de primer nivel.
28. **Municipio (`Municipio`):** Municipios dependientes de cada estado.
29. **Localidad (`Localidad`):** Parroquias, ciudades o sectores residenciales.

### 3.4. Maestros de Salud, Configuración Algorítmica y NLP
30. **Reposos y Permisos (`Reposos_Permisos`):** Tipos de reposos justificados (médicos, duelo, personales) y número de días establecidos.
31. **Índices Antropométricos (`IndiceEdad`, `IndicePeso`, `IndiceEstatura`):** Tablas de referencia estadística para cálculo de rangos percentiles.
32. **Palabras Clave de Calendario (`CalendarioPalabraClave`, `CategoriaPalabraClave`, `CategoriaDia`, `ColorEvento`):** Diccionario léxico para categorizar eventos del PDF (efemérides, días festivos, consejos, feriados nacionales).

### 3.5. Maestros de Seguridad y Usuarios
33. **Usuario (`User`):** Cuentas de acceso con correo y contraseña.
34. **Roles y Permisos (`Role`, `Permission`):** Definición de perfiles de acceso mediante `Spatie`.

---

## 4. Identificación de Transacciones (Operaciones y Procesos)

Las transacciones representan las operaciones dinámicas donde interactúan dos o más entidades maestras para concretar flujos de trabajo de la institución:

1. **Transacción de Inscripción de Nuevo Ingreso (`InscripcionController` / `InscripcionNuevoIngreso`):**
   - Captura y validación del nuevo estudiante con sus datos de filiación, procedencia escolar, salud, tallas y representante.
   - Creación de la tupla transaccional en `inscripcions` y `inscripcion_nuevo_ingresos`.
   - Asignación de cupo inicial en estado *Activo* o *Pendiente*.
2. **Transacción de Inscripción por Prosecución (`InscripcionProsecucionController` / `InscripcionProsecucion`):**
   - Continuidad académica de estudiantes de años anteriores.
   - Registro de la condición académica: estudiante promovido o repitiente.
   - Registro detallado de materias pendientes o reprobadas mediante `prosecucion_areas`.
   - Flujo transaccional habilitado tanto para el personal administrativo como vía web desde el **Portal del Representante**.
3. **Ejecución y Distribución Automatizada por Percentil (`EntradasPercentilController` / `SectionDistributorService`):**
   - Proceso por lotes que toma a todos los inscritos de un grado lectivo.
   - Calcula el índice compuesto antropométrico y de edad de cada alumno.
   - Aplica reglas de equilibrio de género (50/50 o proporcional) y divide a los estudiantes en secciones creadas dinámicamente (`seccions`), actualizando `entradas_percentils` y asignando la sección definitiva en la inscripción.
4. **Extracción y Procesamiento Inteligente de Calendario Académico (`CalendarioAcademicoController` / `CalendarioPdfExtractorService`):**
   - Carga del PDF ministerial oficial.
   - Parseo y categorización de texto por página y mes.
   - Creación de registros candidatos en `calendario_dias` asignando niveles de confianza (*Alta*, *Dudosa*).
   - Revisión interactiva de la subdirectora, confirmación de días y eventos, o adición manual de fechas institucionales.
5. **Asignación Académica Docente - Grado - Área (`DocenteAreaGradoController` / `DocenteAreaGrado`):**
   - Vinculación del profesor con sus áreas de especialidad (`DetalleDocenteEstudio`).
   - Carga transaccional que asigna formalmente a un docente una o más materias en un grado y sección específicos (`docente_area_grados`).
6. **Mapeo Curricular y Especialidad (`GradoAreaFormacion` y `AreaEstudioRealizado`):**
   - Configuración de la malla curricular: qué asignaturas corresponden a qué grado (`grado_area_formacions`).
   - Relación entre la titulación del docente y las materias que tiene competencia para impartir (`area_estudio_realizados`).
7. **Planificación y Ensamblaje de Horarios Escolares (`HorarioController` / `Horario`):**
   - Cruce multidimensional entre: docente asignado + materia + grado + sección + aula física asignada + bloque horario + día de la semana (`horario__bloque_hora`, `horario__dias_semana`, `horario__docente__area__formacion`, `horario__seccion`).
8. **Justificación de Inasistencias y Reposos (`InasistenciaController` y `Permisos_RepososController`):**
   - Registro y validación de permisos de docentes y justificación de faltas estudiantiles con soporte de los días de reposo autorizados.
9. **Formalización de Actas de Entrevistas (`ActaEntrevistaController`):**
   - Apertura y asentamiento formal de acuerdos, citaciones y entrevistas pedagógicas sostenidas entre la directiva o docentes y los representantes.
10. **Apertura, Extensión y Cierre de Año Escolar (`AnioEscolarController` / `AnioEscolarService`):**
    - Activación de períodos lectivos, prórrogas y congelamiento de operaciones para dar paso al nuevo ciclo lectivo.
11. **Consulta y Trazabilidad Histórica (`HistoricoController`):**
    - Consulta de auditoría y archivo histórico de inscripciones (nuevo ingreso, prosecución) y asignaciones docentes filtradas por años escolares pasados, grados y secciones.

---

## 5. Identificación de Reportes y Documentos de Salida

El sistema genera diversos reportes y documentos impresos o digitales (la mayoría implementados con `Barryvdh\DomPDF` y motor Blade), indispensables para la gestión escolar venezolana:

### 5.1. Reportes de Alumnos e Inscripción
1. **Ficha Oficial de Inscripción de Nuevo Ingreso:**
   - *Ruta / Vista:* `admin/transacciones/inscripcion/reporte/ficha_inscripcion.blade.php`
   - *Detalle:* Expediente oficial de inscripción con datos de identificación civil, ubicación geográfica, datos biométricos y de salud, información completa de padre, madre y representante legal, escuela de procedencia y firma de aceptación de normas del plantel.
2. **Reporte General de Inscripciones de Nuevo Ingreso:**
   - *Ruta / Vista:* `admin/transacciones/inscripcion/reporte/reporte_general_nuevo_ingreso.blade.php`
   - *Detalle:* Listado tabular en formato horizontal (A4 landscape) de todos los inscritos en nuevo ingreso, ordenado alfabéticamente y filtrable por año escolar.
3. **Ficha de Inscripción por Prosecución:**
   - *Ruta / Vista:* `admin/transacciones/inscripcion_prosecucion/reportes/ficha_inscripcion.blade.php`
   - *Detalle:* Constancia oficial de renovación de matrícula indicando grado anterior, grado cursante, sección, estatus de promoción y el desglose de asignaturas aprobadas y pendientes.
4. **Reporte General de Inscripciones por Prosecución:**
   - *Ruta / Vista:* `admin/transacciones/inscripcion_prosecucion/reportes/reporte_general_prosecucion.blade.php`
   - *Detalle:* Nómina en orientación apaisada con filtros por grado, sección, condición de materias pendientes y estatus activo.
5. **Ficha / Reporte Individual del Alumno:**
   - *Ruta / Vista:* `admin/alumnos/reportes/general_est.blade.php`
   - *Detalle:* Hoja de vida del estudiante con su expediente demográfico, tallas, lateralidad y etnia.
6. **Reporte General de Alumnos:**
   - *Ruta / Vista:* `admin/alumnos/reportes/Reporte_General.blade.php`
   - *Detalle:* Padrón consolidado de estudiantes matriculados filtrable por género, estatus y tipo de documento.

### 5.2. Reportes del Personal Docente y Carga Académica
7. **Ficha Individual del Docente:**
   - *Ruta / Vista:* `admin/docente/reportes/individual_PDF.blade.php`
   - *Detalle:* Ficha curricular del docente, datos de contacto, títulos registrados en el sistema y carga académica.
8. **Reporte General de Docentes:**
   - *Ruta / Vista:* `admin/docente/reportes/general_pdf.blade.php`
   - *Detalle:* Listado institucional de todo el profesorado activo en orientación horizontal, ordenado alfabéticamente.
9. **Reporte de Carga Académica Docente por Materia y Sección con Nómina de Estudiantes:**
   - *Ruta / Vista:* `admin/transacciones/docente_area_grado/reportes/docente_materia_pdf.blade.php`
   - *Detalle:* Documento detallado donde cada profesor visualiza las asignaturas que imparte agrupadas por grado y sección, incluyendo la lista nominal completa de los estudiantes asignados a su aula.
10. **Reporte General de Asignación Docente - Área - Grado:**
    - *Ruta / Vista:* `admin/transacciones/docente_area_grado/reportes/general_pdf.blade.php`
    - *Detalle:* Matriz curricular institucional que resume la cobertura de materias y secciones por profesor.

### 5.3. Reportes de Representantes y Credenciales Escolares
11. **Reporte General de Representantes:**
    - *Ruta / Vista:* `admin/representante/reportes/general_pdf.blade.php`
    - *Detalle:* Registro general de padres y representantes legales con documento de identidad, dirección, teléfonos de contacto, ocupación y representados a su cargo.
12. **Carnet Estudiantil Digital e Impreso:**
    - *Ruta / Vista:* `representante/carnet/imprimir.blade.php` y `representante/carnet/index.blade.php`
    - *Detalle:* Credencial escolar oficial que incluye fotografía, código único de carnet, nombres y apellidos del estudiante, cédula/cédula escolar, grado, sección y año escolar vigente.

### 5.4. Salidas y Resúmenes Estadísticos del Sistema
13. **Resumen y Distribución de Secciones por Percentil:**
    - *Ruta / Vista:* `admin/transacciones/percentil/index.blade.php`
    - *Detalle:* Tablero analítico que presenta la distribución de estudiantes procesados por el algoritmo, totales por sección, promedios biométricos y balance de capacidad de cupos.
14. **Documento de Acta de Entrevista:**
    - *Ruta / Vista:* `acta_entrevista/create.blade.php`
    - *Detalle:* Plantilla para documentar formalmente las reuniones, compromisos y acuerdos disciplinarios o pedagógicos con representantes.

---

## 6. Arquitectura y Organización del Proyecto

La estructura del código demuestra un diseño modular y desacoplado:
```
Sistema-de-Inscripcion/
├── app/
│   ├── Actions/         # Lógica atómica de negocio (ej. CrearAlumnoAction)
│   ├── DTOs/            # Data Transfer Objects
│   ├── Enums/           # Enumeraciones (EstadoCalendario, CategoriaDia, ConfianzaDia, ColorEvento)
│   ├── Http/
│   │   ├── Controllers/ # Controladores para cada maestro y transacción
│   │   └── Middleware/  # Verificaciones (ej. verificar.anio.escolar)
│   ├── Livewire/        # Componentes Livewire reactivos (Alumnos, Docentes, Inscripciones, Modales)
│   ├── Models/          # Modelos Eloquent con relaciones y scopes definidos
│   ├── Repositories/    # Acceso a datos (InscripcionRepository, RepresentanteRepository)
│   └── Services/        # Lógica compleja de negocio:
│                        #  - SectionDistributorService (Algoritmo de balanceo de secciones)
│                        #  - PercentilService (Cálculo de percentiles biométricos)
│                        #  - CalendarioPdfExtractorService (Parser inteligente de PDF de calendario)
│                        #  - AnioEscolarService & DocumentoService
├── config/              # Configuraciones de paquetes (adminlte.php, permission.php, dompdf.php)
├── database/
│   ├── migrations/      # Más de 50 migraciones que definen la base de datos relacional
│   └── seeders/         # Sembradores para roles, permisos, catálogos geográficos y maestros
├── resources/
│   ├── css/ & sass/     # Estilos Tailwind CSS, SCSS Bootstrap y Flux UI
│   ├── js/              # Alpine.js, Swiper, scripts de interactividad
│   └── views/           # Vistas Blade organizadas por módulos (admin, representante, HomePage, reportes)
└── routes/
    ├── web.php          # Definición de rutas del sistema organizadas por prefijos y roles
    └── breadcrumbs.php  # Mapeo de rutas de migas de pan para AdminLTE
```
