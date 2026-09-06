# Sistema de Inscripción - Laravel 12 + AdminLTE

<p align="center">
<a href="https://laravel.com" target="_blank">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</a>
</p>

## Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js >= 18.x y NPM

## Instalación

Sigue estos pasos después de clonar el proyecto desde GitHub:

### 1. Clonar el repositorio

```bash
git clone [URL_DEL_REPOSITORIO]
cd [NOMBRE_DEL_PROYECTO]
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar paquetes adicionales requeridos

```bash
composer require livewire/livewire
composer require diglactic/laravel-breadcrumbs
composer require spatie/laravel-permission
```

### 4. Configurar el archivo de entorno

Edita el archivo `.env` y configura los siguientes parámetros:

```env
APP_NAME="Sistema de Inscripción"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_inscripcion_proyecto
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Crear la base de datos

Crea la base de datos en MySQL:

```phpMyAdmin
sistema_inscripcion_proyecto
```

### 7. Publicar configuraciones de paquetes

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 8. Ejecutar las migraciones

```bash
php artisan migrate
```

### 9. Ejecutar los seeders

```bash
php artisan db:seed
```

### 10. Limpiar y optimizar caché

```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 11. Instalar dependencias de frontend (si aplica)

```bash
npm install
```

### 12. Crear enlace simbólico para storage

```bash
php artisan storage:link
```

### 13. Iniciar el servidor de desarrollo

```bash
npm run dev & php artisan serve
Clickear en el server https://127.0.0.1:8000
```
### 13. Datos de LogIn

Email: admin@admin.com
Password: password

### 14. DOMPDF
Comando para instalar DOMPDF:

```bash
composer require barryvdh/laravel-dompdf
```

### 15. Swiper

```bash
npm install swiper
```

### 16. Alpine.js

```bash
npm install alpinejs
```

### 18 . comando para instalar la libreria de smalot/pdfparser
```bash
composer require smalot/pdfparser
```

### 19. Compilar assets

```bash
npm run build
```

## Credenciales por Defecto

Si se ejecutaron los seeders correctamente, las credenciales predeterminadas son:

- **Email**: admin@example.com
- **Password**: 12345678

*(Verifica en tu seeder las credenciales exactas)*

## Paquetes Incluidos

- **[Livewire](https://livewire.laravel.com)** - Componentes dinámicos sin escribir JavaScript
- **[Laravel Breadcrumbs](https://github.com/diglactic/laravel-breadcrumbs)** - Migas de pan para navegación
- **[Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)** - Gestión de roles y permisos
- **[AdminLTE](https://adminlte.io)** - Template de administración

## Estructura del Proyecto

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Livewire/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
└── routes/
```

## Comandos Útiles

```bash
# Crear un nuevo controller
php artisan make:controller NombreController

# Crear un modelo con migración
php artisan make:model Nombre -m

# Crear un seeder
php artisan make:seeder NombreSeeder

# Crear un componente Livewire
php artisan make:livewire NombreComponente

# Refrescar base de datos con seeders
php artisan migrate:fresh --seed
```

## Solución de Problemas

### Error de permisos en storage

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Problemas con caché

```bash
php artisan optimize:clear
composer dump-autoload
```

## Contribuir

Si deseas contribuir al proyecto, por favor crea un fork y envía un pull request con tus mejoras.

## Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).
