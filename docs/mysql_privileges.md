# Privilegios MySQL Recomendados para Producción

En cumplimiento con los requerimientos no funcionales de seguridad y el principio de mínimo privilegio (RNF-12, RNF-2), se establecen las siguientes directrices para la base de datos MySQL en entornos de producción:

---

## 1. Usuario de la Aplicación Web (`app_user`)
El usuario con el cual la aplicación Laravel se conecta a la base de datos en tiempo de ejecución debe tener únicamente los privilegios indispensables para operar la lógica de negocio.

### Privilegios Permitidos
- `SELECT`
- `INSERT`
- `UPDATE`
- `DELETE`

### Restricciones Críticas
- **Prohibición de DDL**: El usuario de la aplicación **NO debe tener** privilegios de `DROP`, `TRUNCATE` ni `ALTER` sobre las tablas del sistema, y especialmente **NUNCA sobre la tabla `auditorias`**.
- La inmutabilidad de la tabla `auditorias` está protegida tanto por Eloquent como por triggers de base de datos. Quitar privilegios de `DROP` y `TRUNCATE` a nivel de motor garantiza que un compromiso en la capa de aplicación no pueda purgar la trazabilidad histórica.

```sql
-- Ejemplo de concesión de privilegios mínimos para app_user:
REVOKE ALL PRIVILEGES ON sistema_inscripcion_proyecto.* FROM 'app_user'@'%';

GRANT SELECT, INSERT, UPDATE, DELETE ON sistema_inscripcion_proyecto.* TO 'app_user'@'%';

-- Asegurar que NO tenga DROP ni TRUNCATE ni ALTER:
REVOKE DROP, ALTER, INDEX, CREATE, REFERENCES ON sistema_inscripcion_proyecto.* FROM 'app_user'@'%';
```

---

## 2. Usuario de Despliegue y Migraciones (`deploy_user`)
Para la ejecución de tareas de mantenimiento, migraciones de Laravel (`php artisan migrate`) y despliegues automáticos (CI/CD), se debe utilizar un usuario con privilegios administrativos dedicados.

### Privilegios del Usuario de Despliegue
- `CREATE`, `ALTER`, `INDEX`, `DROP`, `REFERENCES`
- `TRIGGER`: Requerido obligatoriamente para crear y mantener los triggers de inmutabilidad (`prevent_auditorias_update`, `prevent_auditorias_delete`) en la tabla `auditorias`.
- `SELECT`, `INSERT`, `UPDATE`, `DELETE`

```sql
-- Ejemplo de concesión de privilegios para deploy_user:
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, TRIGGER 
ON sistema_inscripcion_proyecto.* TO 'deploy_user'@'localhost';
```

---

## 3. Resumen de Separación de Responsabilidades

| Privilegio / Capacidad | Usuario App (`app_user`) | Usuario Despliegue (`deploy_user`) |
|------------------------|:------------------------:|:----------------------------------:|
| `SELECT`               | Sí                       | Sí                                 |
| `INSERT`               | Sí                       | Sí                                 |
| `UPDATE`               | Sí (excepto auditorias via trigger) | Sí                       |
| `DELETE`               | Sí (excepto auditorias via trigger) | Sí                       |
| `DROP` / `TRUNCATE`    | **NO**                   | Sí                                 |
| `ALTER`                | **NO**                   | Sí                                 |
| `TRIGGER`              | **NO**                   | **Sí (para inmutabilidad)**        |
