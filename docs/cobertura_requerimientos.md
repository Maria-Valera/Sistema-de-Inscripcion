# Matriz de Cobertura de Requerimientos de Seguridad

Este documento detalla la trazabilidad y cobertura de verificación de los Requerimientos Funcionales (RF) y Requerimientos No Funcionales (RNF) de seguridad del sistema, asociando cada especificación con su implementación y la suite de pruebas automatizadas que valida su estricto cumplimiento.

---

## 1. Tabla Resumen de Cobertura

| Requerimiento | Tipo | Nombre / Descripción Resumida | Estado | Archivo de Prueba | Métodos de Prueba Principales |
| :--- | :--- | :--- | :---: | :--- | :--- |
| **RF-37** | Funcional | Redirección post-login según rol principal (`rutaDashboard`) y prevención de bucles en `/sin-acceso` | **CUBIERTO** | [ControlAccesoTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ControlAccesoTest.php)<br>[AuditoriaTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuditoriaTest.php)<br>[RegistroUsuarioTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/RegistroUsuarioTest.php) | `test_home_admin_accede_y_otros_roles_son_redirigidos`<br>`test_usuarios_sin_dashboard_llegan_a_sin_acceso_sin_bucle`<br>`test_rol_principal_efectivo_*`<br>`test_registro_exitoso_*` |
| **RF-38** | Funcional | Gestión de usuarios, asignación de roles múltiples, rol principal y control granular de módulos extra | **CUBIERTO** | [GestionUsuariosTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/GestionUsuariosTest.php)<br>[GestionRolesTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/GestionRolesTest.php)<br>[DocenteRepresentanteTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/DocenteRepresentanteTest.php) | `test_admin_puede_ver_lista_de_usuarios`<br>`test_asignar_modulo_extra_y_verificar_que_usuario_lo_adquiere`<br>`test_retirar_modulo_extra_y_usuario_lo_pierde_si_no_lo_tenia_por_rol`<br>`test_admin_puede_crear_usuario_con_multiples_roles_y_rol_principal`<br>`test_admin_puede_crear_rol_con_permisos_especificos` |
| **RNF-2** | No Funcional | Principio de mínimo privilegio y control de acceso RBAC estricto en rutas, APIs y vistas | **CUBIERTO** | [ControlAccesoTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ControlAccesoTest.php)<br>[PermisosSeederTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/PermisosSeederTest.php)<br>[docs/mysql_privileges.md](file:///c:/laragon/www/Sistema-de-Inscripcion/docs/mysql_privileges.md) | `test_cobertura_de_rutas_protegidas`<br>`test_matriz_roles_acceso`<br>`test_rutas_api_eliminadas_y_protegidas`<br>`test_roles_tienen_permisos_del_mapeo`<br>`test_representante_docente_sin_permisos_administrativos` |
| **RNF-6** | No Funcional | Almacenamiento seguro de contraseñas (Bcrypt) y sanitización de credenciales en auditorías y respuestas | **CUBIERTO** | [SeguridadSeederTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/SeguridadSeederTest.php)<br>[AuthTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuthTest.php)<br>[AuditoriaTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuditoriaTest.php) | `test_password_nunca_esta_en_texto_plano`<br>`test_contrasena_no_aparece_en_auditorias`<br>`test_crear_user_genera_auditoria_sin_contrasena`<br>`test_modificar_user_genera_auditoria_sin_contrasena` |
| **RNF-7** | No Funcional | Políticas de contraseñas robustas (mínimo 8 caracteres, alfanumérico) y cambio forzado de credenciales temporales | **CUBIERTO** | [AuthTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuthTest.php)<br>[RegistroUsuarioTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/RegistroUsuarioTest.php)<br>[SeguridadSeederTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/SeguridadSeederTest.php) | `test_contrasena_temporal_fuerza_cambio`<br>`test_contrasena_debil_rechazada`<br>`test_contrasena_sin_letras_rechazada`<br>`test_contrasena_sin_numeros_rechazada`<br>`test_fuera_de_local_con_credenciales_iniciales` |
| **RNF-8** | No Funcional | Protección contra fuerza bruta mediante limitación de tasa de peticiones (Rate Limiting) | **CUBIERTO** | [AuthTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuthTest.php) | `test_limite_intentos_bloquea` |
| **RNF-9** | No Funcional | Verificación obligatoria de correo electrónico (`MustVerifyEmail`) post-registro y tras correcciones administrativas | **CUBIERTO** | [RegistroUsuarioTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/RegistroUsuarioTest.php)<br>[GestionUsuariosTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/GestionUsuariosTest.php)<br>[SeguridadSeederTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/SeguridadSeederTest.php) | `test_se_envia_correo_de_verificacion_al_registrarse`<br>`test_usuario_sin_verificar_es_redirigido_a_email_verify_al_intentar_acceder_al_portal`<br>`test_admin_puede_corregir_correo_y_resetea_email_verified_at`<br>`test_usuarios_de_seeders_pasan_middleware_verified` |
| **RNF-10** | No Funcional | Protección contra IDOR (Insecure Direct Object Reference) y Scoping de datos sensibles fisonómicos | **CUBIERTO** | [ScopingPoliciesTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ScopingPoliciesTest.php) | `test_seguridad_por_url_y_livewire_representantes`<br>`test_datos_sensibles_ocultos_para_docente` |
| **RNF-11** | No Funcional | Trazabilidad y auditoría inmutable de eventos críticos (bloqueo de DELETE/UPDATE por Eloquent y SQL) | **CUBIERTO** | [AuditoriaTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/AuditoriaTest.php)<br>[ControlAccesoTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ControlAccesoTest.php)<br>[GestionUsuariosTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/GestionUsuariosTest.php) | `test_update_auditoria_falla_por_eloquent`<br>`test_delete_auditoria_falla_por_eloquent`<br>`test_update_auditoria_falla_por_sql_directo`<br>`test_delete_auditoria_falla_por_sql_directo`<br>`test_acceso_denegado_registra_auditoria`<br>`test_auditoria_de_403_en_livewire`<br>`test_acciones_dejan_registro_en_tabla_auditorias` |
| **RNF-12** | No Funcional | Sanitización y exclusión de datos personales (PII) en logs, query strings y URLs de auditoría | **CUBIERTO** | [ControlAccesoTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ControlAccesoTest.php) | `test_auditoria_no_guarda_query_string_con_datos_personales` |
| **RNF-20** | No Funcional | Prevención de conflicto de intereses en calificaciones y evaluaciones de familiares directos | **CUBIERTO** | [ScopingPoliciesTest.php](file:///c:/laragon/www/Sistema-de-Inscripcion/tests/Feature/ScopingPoliciesTest.php) | `test_conflicto_de_intereses_rnf20` |

---

## 2. Detalle y Evidencia por Requerimiento

### RF-37: Redirección Post-Login y Prevención de Bucles
- **Descripción**: Al autenticarse, el usuario es redirigido automáticamente a la ruta correspondiente a su `rol_principal` efectivo mediante el método [User::rutaDashboard()](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/User.php). Aquellos usuarios con roles que no disponen de panel de inicio propio (o usuarios sin roles asignados) son redirigidos a la vista pública de advertencia `/sin-acceso` sin entrar en bucles infinitos de redirección.
- **Implementación**:
  - [app/Http/Controllers/Auth/LoginController.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Http/Controllers/Auth/LoginController.php#L112-L115) (`redirectTo()`).
  - [app/Http/Controllers/HomeController.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Http/Controllers/HomeController.php) (`index()`).
- **Pruebas Automatizadas**:
  - `ControlAccesoTest::test_home_admin_accede_y_otros_roles_son_redirigidos`: Valida que el Admin ingresa a `/home`, mientras que Docente, Subdirectora y Representante son redirigidos a su respectivo portal.
  - `ControlAccesoTest::test_usuarios_sin_dashboard_llegan_a_sin_acceso_sin_bucle`: Valida que los roles `Editor`, `User` o usuarios sin roles aterrizan en `/sin-acceso` con código 200 tras máximo una redirección.
  - `AuditoriaTest::test_rol_principal_efectivo_*`: Valida la resolución determinística del rol prioritario.

### RF-38: Gestión Administrativa de Usuarios y Módulos Extra
- **Descripción**: Facilita al Administrador la creación y modificación de usuarios, gestión de roles múltiples, designación del rol principal y concesión granular de módulos extra (o permisos específicos dentro de un módulo) sin alterar la plantilla de permisos asignada al rol base.
- **Implementación**:
  - [app/Livewire/Admin/Usuarios/GestionarUsuarios.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Livewire/Admin/Usuarios/GestionarUsuarios.php)
  - [app/Actions/Usuarios/GestionarModulosUsuarioAction.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Actions/Usuarios/GestionarModulosUsuarioAction.php)
  - [app/Actions/Usuarios/CrearUsuarioParaPersonaAction.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Actions/Usuarios/CrearUsuarioParaPersonaAction.php)
  - [app/Livewire/Admin/Roles/GestionarRoles.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Livewire/Admin/Roles/GestionarRoles.php)
- **Pruebas Automatizadas**:
  - `GestionUsuariosTest::test_asignar_modulo_extra_y_verificar_que_usuario_lo_adquiere`: Valida la persistencia de permisos directos y su reflejo en `modulosEfectivos()`.
  - `GestionUsuariosTest::test_retirar_modulo_extra_y_usuario_lo_pierde_si_no_lo_tenia_por_rol`: Valida la revocación de módulos extra.
  - `GestionUsuariosTest::test_intentar_retirar_modulo_que_viene_del_rol_lo_conserva`: Garantiza que los permisos heredados del rol permanezcan protegidos.
  - `GestionUsuariosTest::test_admin_puede_crear_usuario_con_multiples_roles_y_rol_principal`: Comprueba la asignación simultánea de Docente y Representante.
  - `GestionRolesTest::test_admin_puede_crear_rol_con_permisos_especificos`: Valida la creación de roles con control granular de checkboxes en el modal.

### RNF-2: Principio de Mínimo Privilegio (RBAC)
- **Descripción**: Cada cuenta posee exclusivamente los permisos indispensables para desempeñar sus funciones. Las rutas y llamadas Livewire evalúan `role:`, `permission:` o `role_or_permission:`. Los endpoints de APIs internas exigen autenticación por sesión activa.
- **Implementación**:
  - [routes/web.php](file:///c:/laragon/www/Sistema-de-Inscripcion/routes/web.php)
  - [config/roles_modulos.php](file:///c:/laragon/www/Sistema-de-Inscripcion/config/roles_modulos.php)
  - [app/Providers/AppServiceProvider.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Providers/AppServiceProvider.php) (`Gate::before` para Admin).
- **Pruebas Automatizadas**:
  - `ControlAccesoTest::test_cobertura_de_rutas_protegidas`: Inspección dinámica de todas las rutas de la aplicación confirmando la presencia de middlewares de autorización.
  - `ControlAccesoTest::test_matriz_roles_acceso`: Comprueba que la Subdirectora gestiona aulas y ve personal/horarios, pero recibe 403 en alumnos y creación de horarios; Docentes y Representantes reciben 403 en todo el panel administrativo.
  - `ControlAccesoTest::test_rutas_api_eliminadas_y_protegidas`: Valida que los endpoints no públicos sin sesión devuelvan 401 JSON.
  - `PermisosSeederTest::test_representante_docente_sin_permisos_administrativos`: Valida que los roles no administrativos carecen de permisos de gestión.

### RNF-6: Almacenamiento Seguro de Contraseñas y Sanitización
- **Descripción**: Las contraseñas se almacenan empleando funciones de hashing seguras (`Bcrypt` con costo 12). Ninguna contraseña ni token de recuperación se almacena en texto plano ni se registra en las columnas `valores_nuevos` o `valores_anteriores` de la tabla `auditorias`.
- **Implementación**:
  - [app/Observers/AuditoriaObserver.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Observers/AuditoriaObserver.php) (purga automática de campos sensibles antes de insertar en auditorías).
  - [app/Models/Auditoria.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/Auditoria.php) (`registrar()`).
- **Pruebas Automatizadas**:
  - `SeguridadSeederTest::test_password_nunca_esta_en_texto_plano`: Verifica prefijos `$2y$` y la imposibilidad de lectura directa.
  - `AuthTest::test_contrasena_no_aparece_en_auditorias`: Valida intentos fallidos de autenticación.
  - `AuditoriaTest::test_crear_user_genera_auditoria_sin_contrasena` y `test_modificar_user_genera_auditoria_sin_contrasena`: Comprueba la sanitización de eventos de ciclo de vida de usuarios.

### RNF-7: Políticas de Contraseñas y Cambio Forzado
- **Descripción**: Se exige una longitud mínima de 8 caracteres alfanuméricos combinados. Cuando el Administrador crea un usuario o restablece un acceso, se genera una contraseña temporal y se marca la bandera `debe_cambiar_password = true`, forzando al usuario a cambiar su credencial antes de interactuar con el sistema.
- **Implementación**:
  - [app/Http/Middleware/ForzarCambioPassword.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Http/Middleware/ForzarCambioPassword.php)
  - [app/Http/Controllers/Auth/PasswordChangeController.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Http/Controllers/Auth/PasswordChangeController.php)
- **Pruebas Automatizadas**:
  - `AuthTest::test_contrasena_temporal_fuerza_cambio`: Redirección automática hacia `/password/cambiar`.
  - `AuthTest::test_contrasena_debil_rechazada`: Bloqueo de contraseñas de solo números o de solo letras.
  - `RegistroUsuarioTest::test_contrasena_debil_rechazada`: Verificación en el formulario público de registro.

### RNF-8: Rate Limiting y Protección contra Fuerza Bruta
- **Descripción**: Mitigación de ataques de fuerza bruta mediante la restricción de intentos en el formulario de inicio de sesión (`throttle:6,1`), bloqueando temporalmente al emisor tras superar el umbral estipulado.
- **Implementación**:
  - [app/Http/Controllers/Auth/LoginController.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Http/Controllers/Auth/LoginController.php) (`ThrottlesLogins`).
  - [bootstrap/app.php](file:///c:/laragon/www/Sistema-de-Inscripcion/bootstrap/app.php).
- **Pruebas Automatizadas**:
  - `AuthTest::test_limite_intentos_bloquea`: Comprueba que al 5to intento fallido consecutivo el 6to intento es rechazado inmediatamente con error de rate limiter.

### RNF-9: Verificación Obligatoria de Correo Electrónico
- **Descripción**: Garantiza la validez de los correos mediante el envío obligatorio del enlace de confirmación (`MustVerifyEmail`). Si un usuario no ha verificado su correo, el middleware `verified` le impide acceder al portal y lo redirige a la vista de confirmación. Al corregir administrativamente una dirección de correo, el campo `email_verified_at` se resetea a `null`.
- **Implementación**:
  - [app/Models/User.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/User.php) (`implements MustVerifyEmail`).
  - [app/Actions/Usuarios/CorregirCorreoAction.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Actions/Usuarios/CorregirCorreoAction.php).
- **Pruebas Automatizadas**:
  - `RegistroUsuarioTest::test_se_envia_correo_de_verificacion_al_registrarse`
  - `RegistroUsuarioTest::test_usuario_sin_verificar_es_redirigido_a_email_verify_al_intentar_acceder_al_portal`
  - `GestionUsuariosTest::test_admin_puede_corregir_correo_y_resetea_email_verified_at`

### RNF-10: Protección contra IDOR y Scoping de Datos Fisonómicos
- **Descripción**: Previene ataques de referencia directa insegura a objetos (IDOR). Los Representantes solo tienen autorización para consultar expedientes de sus propios hijos representados. Los Docentes únicamente visualizan información académica; los campos de salud y antropométricos (peso, estatura, calzado) permanecen ocultos.
- **Implementación**:
  - [app/Policies/AlumnoPolicy.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Policies/AlumnoPolicy.php)
  - [app/Livewire/Admin/TransaccionInscripcion/InscripcionProsecucion.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Livewire/Admin/TransaccionInscripcion/InscripcionProsecucion.php)
  - [resources/views/admin/alumnos/show.blade.php](file:///c:/laragon/www/Sistema-de-Inscripcion/resources/views/admin/alumnos/show.blade.php)
- **Pruebas Automatizadas**:
  - `ScopingPoliciesTest::test_seguridad_por_url_y_livewire_representantes`: El Representante A recibe 403 al acceder a `/admin/alumnos/{idB}` o al ejecutar `seleccionarAlumno(idB)` en Livewire.
  - `ScopingPoliciesTest::test_datos_sensibles_ocultos_para_docente`: Comprueba que `peso`, `estatura` y `talla_zapato` no se renderizan para un Docente.

### RNF-11: Trazabilidad y Auditoría Inmutable
- **Descripción**: Registro exhaustivo y de solo lectura de todas las operaciones críticas (altas, modificaciones, eliminaciones, accesos denegados 403, cambios de módulos y correos). La inmutabilidad de la tabla `auditorias` está blindada en la capa ORM (Eloquent) y en el motor de base de datos MySQL (Triggers / restricción de privilegios).
- **Implementación**:
  - [app/Models/Auditoria.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/Auditoria.php) (métodos boot bloqueando updating/deleting con `LogicException`).
  - Base de datos: Triggers `trg_auditorias_no_update` y `trg_auditorias_no_delete`.
- **Pruebas Automatizadas**:
  - `AuditoriaTest::test_update_auditoria_falla_por_eloquent`
  - `AuditoriaTest::test_delete_auditoria_falla_por_eloquent`
  - `AuditoriaTest::test_update_auditoria_falla_por_sql_directo`
  - `AuditoriaTest::test_delete_auditoria_falla_por_sql_directo`
  - `ControlAccesoTest::test_acceso_denegado_registra_auditoria`
  - `ControlAccesoTest::test_auditoria_de_403_en_livewire`

### RNF-12: Sanitización de PII en Logs y URLs
- **Descripción**: Prohíbe almacenar parámetros de búsqueda sensibles o datos personales (como números de cédula, correos o nombres) presentes en el query string de las URLs auditadas. La URI se normaliza antes del guardado persistente.
- **Implementación**:
  - [app/Models/Auditoria.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Models/Auditoria.php#L68) (extracción de `request()->path()` sin `$request->getQueryString()`).
- **Pruebas Automatizadas**:
  - `ControlAccesoTest::test_auditoria_no_guarda_query_string_con_datos_personales`: Comprueba que una petición denegada con `?numero_documento=12345678` almacena la URL depurada y que el valor numérico no existe en ninguna columna de la tabla `auditorias`.

### RNF-20: Prevención de Conflicto de Intereses en Evaluaciones
- **Descripción**: En situaciones donde un usuario posea doble perfil (Docente y Representante), el sistema prohíbe explícitamente que dicho docente pueda modificar, registrar o calificar evaluaciones académicas de alumnos con quienes mantenga vínculo de representación legal directa.
- **Implementación**:
  - [app/Policies/NotaPolicy.php](file:///c:/laragon/www/Sistema-de-Inscripcion/app/Policies/NotaPolicy.php) (verificación de parentesco / tutela legal en `update`).
- **Pruebas Automatizadas**:
  - `ScopingPoliciesTest::test_conflicto_de_intereses_rnf20`: Comprueba que `Gate::forUser($userDoble)->allows('update', $nota)` rechaza la modificación (`false`) y genera un registro de auditoría con acción `acceso_denegado` indicando conflicto de intereses.
