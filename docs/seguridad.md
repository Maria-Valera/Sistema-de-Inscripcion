# Manual Oficial de Seguridad y Control de Accesos

Este documento constituye el manual oficial de arquitectura, operación y mantenimiento del **Módulo de Seguridad y Control de Acceso Basado en Roles (RBAC)** del Sistema de Inscripción Escolar.

---

## 1. Roles del Sistema y Módulos por Defecto

El sistema implementa un modelo de control de acceso jerárquico y granular basado en **Spatie Laravel-Permission**, mapeado de forma declarativa en los archivos [config/modulos.php](file:///c:/laragon/www/Sistema-de-Inscripcion/config/modulos.php) y [config/roles_modulos.php](file:///c:/laragon/www/Sistema-de-Inscripcion/config/roles_modulos.php).

### Resumen de Roles

| Rol | Ámbito / Perfil | Dashboard / Destino Post-Login | Capacidades y Alcance por Defecto |
| :--- | :--- | :--- | :--- |
| **Admin** | Director / Administrador General | `/home` (Panel Completo) | **Acceso total (God Mode)**. Gestión integral de usuarios, asignación de roles y módulos extra, gestión de roles y permisos, auditoría inmutable, configuración institucional, años escolares, grados, secciones, aulas, horarios, personal docente, inscripciones y reportes. |
| **Subdirectora** | Personal Directivo Académico | `/admin/asistencia` | Gestión completa de asistencias, justificaciones, reposos y reportes de inasistencia. Configuración y gestión de aulas y calendarios escolares. Visualización de personal, horarios y asignación docente. **Restricción**: No puede gestionar alumnos, no puede eliminar horarios ni acceder a auditorías o usuarios. |
| **Docente** | Personal Docente | `/admin/portal-docente/carga-academica` | Portal docente: visualización y generación de carnets de docentes, horarios individuales y consulta de su carga académica asignada. **Restricción**: Ocultamiento estricto de datos fisonómicos (peso, talla, calzado) de alumnos y bloqueo en calificaciones ante conflicto de intereses (RNF-20). |
| **Representante** | Padres / Representantes Legales | `/portal-representante` | Portal de autoservicio: registro y formalización de inscripción por prosecución, visualización y generación de planillas, carnets estudiantiles, horarios de sus representados y constancias de estudio. **Restricción (IDOR)**: Solo puede acceder a expedientes de sus propios hijos representados. |
| **Editor** | Auxiliar Operativo | Redirige a `/sin-acceso` | Perfil reservado para funciones específicas de apoyo mediante asignación manual de módulos extra. Sin dashboard administrativo por defecto. |
| **User** | Usuario Autenticado Base | Redirige a `/sin-acceso` | Perfil base sin permisos asignados. Acceso exclusivo a vistas informativas públicas y cambio de credenciales personales. |

> [!NOTE]
> **God Mode del Rol Admin**: El rol `Admin` tiene implementado un interceptor global en `AppServiceProvider` mediante `Gate::before(fn($user) => $user->hasRole('Admin') ? true : null)`. Esto garantiza que los administradores puedan acceder a todos los módulos y ejecutar cualquier habilidad sin bloqueos accidentales.

---

## 2. Creación y Administración de Usuarios

La administración de cuentas se realiza desde la interfaz centralizada en el módulo **Gestión de Usuarios y Accesos** (`/admin/usuarios` - componente [GestionarUsuarios](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Livewire/Admin/Usuarios/GestionarUsuarios.php)).

### 2.1 Flujo de Creación de Usuarios
1. **Búsqueda y Detección Automática por Cédula**:
   - Al abrir el modal de creación e ingresar el tipo y número de documento, el sistema evalúa asíncronamente si la persona ya existe en la base de datos (`personas`).
   - Si la persona existe, los campos de nombre, apellido y correo se autocompletan automáticamente y se bloquea su edición accidental.
   - Si no existe, los campos permanecen habilitados para el registro simultáneo de la ficha personal y la cuenta de usuario.
2. **Generación de Contraseña Temporal**:
   - Para toda nueva cuenta creada desde el panel de administración, el sistema genera de forma segura una contraseña temporal robusta de 10 caracteres alfanuméricos.
   - El atributo `debe_cambiar_password` se establece en `true`.
3. **Notificación por Correo Electrónico**:
   - El sistema despacha automáticamente un correo con las credenciales temporales de acceso y el enlace al sistema mediante el Mailable `CredencialesUsuarioMailable`.
4. **Doble Perfil Docente-Representante**:
   - Una misma persona puede poseer simultáneamente los roles **Docente** y **Representante**.
   - El administrador puede marcar ambos roles en la creación o edición y definir cuál actuará como su `rol_principal` (determinando la interfaz post-login).
5. **Redirección Contextual a Expedientes**:
   - Si se crea un usuario con rol **Docente** cuya ficha docente aún no ha sido registrada, el sistema notifica y ofrece redirigir a `/admin/docente/create?persona_id=X`.
   - Si se crea con rol **Representante** y no tiene expediente familiar, redirige a la edición de representante para completar los datos de habitabilidad y parentesco.

### 2.2 Control Granular de Permisos y Módulos Extra
- Cada usuario hereda los permisos por defecto de sus roles asignados.
- Desde la columna **Módulos Extra** en la tabla de usuarios, el administrador puede conceder o revocar módulos adicionales mediante un modal con acordeón de checkboxes individuales por cada acción (`ver`, `crear`, `editar`, `eliminar`).
- **Inviolabilidad de permisos base**: Intentar retirar un permiso que proviene del rol asignado no surte efecto; el sistema preserva la integridad del rol base y solo gestiona los permisos directos específicos (`direct permissions`).

---

## 3. Guía: Cómo Agregar un Nuevo Rol o Módulo al Sistema

Para incorporar una nueva funcionalidad o perfil sin romper la consistencia del control de accesos, siga este procedimiento estándar:

### Paso 1: Registrar el Módulo en `config/modulos.php`
Agregue la definición del módulo en el arreglo de módulos con la siguiente estructura:

```php
'mi_modulo' => [
    'nombre' => 'Nombre Descriptivo',
    'icono'  => 'fas fa-shield-alt', // Icono FontAwesome
    'ruta'   => 'admin.mi_modulo.index', // Nombre de ruta en routes/web.php
    'grupo'  => 'academico', // Grupo de menú: academico, configuracion, asistencia, portal, etc.
    'permisos' => [
        'mi_modulo.ver',
        'mi_modulo.crear',
        'mi_modulo.editar',
        'mi_modulo.eliminar',
    ],
],
```

### Paso 2: Asociar Permisos a los Roles en `config/roles_modulos.php`
Defina qué roles tendrán acceso a este módulo y qué acciones podrán ejecutar:

```php
'Admin' => [
    // ...
    'mi_modulo' => '*', // Todos los permisos del módulo
],
'Subdirectora' => [
    // ...
    'mi_modulo' => ['ver', 'crear'], // Solo permisos específicos
],
```

### Paso 3: Proteger la Ruta en `routes/web.php`
Aplique los middlewares de autorización correspondientes:

```php
Route::middleware(['auth', 'verified', 'forzar.cambio.password', 'permission:mi_modulo.ver'])
    ->get('/admin/mi-modulo', [MiModuloController::class, 'index'])
    ->name('admin.mi_modulo.index');
```

En componentes **Livewire**, agregue la validación en el método `mount()` o en las acciones:
```php
public function mount()
{
    Gate::authorize('mi_modulo.ver');
}
```

### Paso 4: Ejecutar los Seeders de Permisos
Para sincronizar los nuevos permisos y roles en la base de datos sin duplicados (operación 100% idempotente):

```bash
php artisan db:seed --class=PermisosSeeder
php artisan db:seed --class=RoleSeeder
```

---

## 4. Variables de Entorno Clave (`.env`)

Configure las siguientes variables en su archivo de entorno `.env` en servidores de producción:

```ini
# Configuración Básica
APP_NAME="Sistema de Inscripción"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inscripciones.colegio.edu.ve

# Seguridad de Sesiones sobre HTTPS
SESSION_SECURE_COOKIE=true
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Algoritmo de Hashing de Contraseñas
BCRYPT_ROUNDS=12

# Conexión a Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_inscripcion
DB_USERNAME=usuario_app
DB_PASSWORD="PasswordSeguroMySQL!"

# Servicio de Correo Electrónico (Notificaciones y Verificación)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@tudominio.com
MAIL_PASSWORD=tu_password_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@colegio.edu.ve"
MAIL_FROM_NAME="Sistema de Inscripción"

# Credenciales del Administrador Inicial (Solo para Seeding inicial fuera de local)
ADMIN_INICIAL_EMAIL=admin@colegio.edu.ve
ADMIN_INICIAL_PASSWORD="ClaveMuySegura2026!"
```

> [!WARNING]
> En entornos de producción (`APP_ENV=production`), si las variables `ADMIN_INICIAL_EMAIL` o `ADMIN_INICIAL_PASSWORD` están vacías, `SeguridadSeeder` abortará la ejecución por motivos de seguridad para prevenir la inserción de contraseñas de desarrollo por defecto.

---

## 5. Inmutabilidad y Trazabilidad de Auditorías

El sistema garantiza que cada registro insertado en la tabla `auditorias` sea inmutable de por vida para fines periciales y de cumplimiento normativo:

1. **Capa ORM (Eloquent)**:
   - El modelo [Auditoria](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/Auditoria.php) cancela y lanza una excepción de tipo `LogicException` ante cualquier intento de ejecución de `$auditoria->update()` o `$auditoria->delete()`.
2. **Capa Motor de Datos (MySQL)**:
   - Se encuentran instalados los triggers de base de datos `trg_auditorias_no_update` y `trg_auditorias_no_delete` que abortan con señal SQL `45000` ante cualquier sentencia directa de `UPDATE` o `DELETE`.
3. **Sanitización de Datos Personales (PII)**:
   - Las contraseñas, tokens de recordatorio y query strings con cédulas o correos son automáticamente eliminados antes de persistir el registro de auditoría.
4. **Visor de Solo Lectura**:
   - La pantalla de visualización (`/admin/auditoria`) es estrictamente de solo lectura, no expone ningún botón destructivo y cuenta con paginación optimizada y badges pastel accesibles.

---

## 6. Políticas Institucionales Específicas

### 6.1 Política de Corrección y Cambio de Correo Electrónico
- **Principio Fundamental**: Solo el personal directivo institucional con rol **Admin** tiene la potestad de corregir o modificar direcciones de correo electrónico registradas.
- **Protocolo de Validación de Identidad**:
  - Toda solicitud de cambio o corrección de correo electrónico debe realizarse de forma presencial.
  - El solicitante (representante, docente o estudiante) debe presentar su **Cédula de Identidad física y original**.
  - El personal administrativo verifica la identidad cotejando el documento físico con los datos registrados en el expediente del sistema.
  - Una vez validada la identidad, el Administrador efectúa la corrección desde el módulo de Gestión de Usuarios (`admin.usuarios.index` / [CorregirCorreoAction](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Actions/Usuarios/CorregirCorreoAction.php)).
  - La corrección sincroniza automáticamente el email tanto en la ficha de la Persona como en la cuenta de Usuario asociada y resetea `email_verified_at = null` para exigir confirmación del nuevo buzón.
  - La acción genera un registro inmutable en la tabla de `auditorias` indicando el email previo, el nuevo email, la dirección IP y el Administrador responsable.

### 6.2 Decisión de Auditoría sobre `email_verified_at`
- **Criterio Técnico**: No se audita el timestamp crudo (`email_verified_at`) como mutación genérica de atributo por las siguientes razones:
  1. El timestamp exacto es un metadato interno del protocolo de verificación de Laravel.
  2. Registrar cada variación microscópica de fechas de verificación genera ruido analítico innecesario en los logs de auditoría.
- **Acción Auditada**: En su lugar, el sistema audita exclusivamente el **evento de verificación exitosa** (`email_verificado`), capturando la acción de alto nivel junto al usuario y la fecha/hora en que confirmó su cuenta.

---

## 7. Comandos Útiles de Administración y Despliegue

### Limpieza y Regeneración de Caché
Ejecute este comando cada vez que modifique archivos de configuración, rutas o vistas:
```bash
php artisan optimize:clear
```

### Ejecución de Seeders de Seguridad
Para poblar o actualizar roles, permisos y módulos de forma idempotente:
```bash
php artisan db:seed --class=PermisosSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=SeguridadSeeder
```

### Ejecución de la Suite Completa de Pruebas
Para validar la integridad de todas las características de seguridad y control de acceso:
```bash
php artisan test
```

Para ejecutar únicamente las pruebas vinculadas a seguridad:
```bash
php artisan test --filter=ControlAccesoTest
php artisan test --filter=AuditoriaTest
php artisan test --filter=AuthTest
php artisan test --filter=ScopingPoliciesTest
php artisan test --filter=GestionUsuariosTest
php artisan test --filter=SeguridadSeederTest
```
