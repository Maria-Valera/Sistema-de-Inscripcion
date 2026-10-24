# Política de Seguridad y Manejo de Accesos

## 1. Política de Corrección y Cambio de Correo Electrónico
- **Principio Fundamental**: Solo la institución educativa a través de usuarios con rol **Admin** tiene la potestad de corregir o modificar direcciones de correo electrónico registradas.
- **Protocolo de Validación de Identidad**:
  - Toda solicitud de cambio o corrección de correo electrónico debe realizarse de forma presencial.
  - El solicitante (representante, docente o estudiante) debe presentar su **Cédula de Identidad física y original**.
  - El personal administrativo verifica la identidad cotejando el documento físico con los datos registrados en el expediente del sistema.
  - Una vez validada la identidad, el Administrador efectúa la corrección desde el módulo de Gestión de Usuarios (`admin.usuarios.index` / `CorregirCorreoAction`).
  - La corrección sincroniza automáticamente el email tanto en la ficha de la Persona como en la cuenta de Usuario asociada.
  - La acción genera un registro inmutable en la tabla de `auditorias` indicando el email previo, el nuevo email, la dirección IP y el Administrador responsable.

## 2. Decisión de Auditoría sobre `email_verified_at`
- **Criterio Técnico**: No se audita el timestamp crudo (`email_verified_at`) como mutación genérica de atributo por las siguientes razones:
  1. El timestamp exacto es un metadato interno del protocolo de verificación de Laravel.
  2. Registrar cada variación microscópica de fechas de verificación genera ruido analítico innecesario en los logs de auditoría.
- **Acción Auditada**: En su lugar, el sistema audita exclusivamente el **evento de verificación exitosa** (`email_verificado`), capturando la acción de alto nivel junto al usuario y la fecha/hora en que confirmó su cuenta.
