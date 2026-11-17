<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Configuracion de Oracle XE 21c

El proyecto usa Laravel-OCI8 para conectarse a Oracle. Cada entorno necesita PHP 8.3 o superior, la extension OCI8 y las bibliotecas cliente de Oracle compatibles con la arquitectura de PHP.

1. Instala OCI8 para tu version y arquitectura de PHP, y asegúrate de que Oracle Instant Client o el cliente incluido con Oracle XE esté disponible en el `PATH`.
2. Copia los valores de Oracle de `.env.example` a tu `.env` local si aún no existen. Usa `XEPDB1` como servicio y las credenciales de un usuario de aplicación, no las de `SYSTEM`.
3. Ejecuta `php artisan migrate` para crear las tablas de Laravel y el esquema IMAJ en el esquema de ese usuario.

No agregues `.env` ni contraseñas al repositorio. En Windows, reinicia la terminal después de cambiar el `PATH`.

## Mantenedores IMAJ

La aplicación incluye modelos sin prefijo (`Rol`, `Paciente`, etc.) y CRUD web para las 16 tablas. Las tablas y secuencias conservan el prefijo `IMAJ_`. Las operaciones CRUD llaman a `IMAJ_APP_PKG` mediante Laravel-OCI8; los listados y reportes consumen cursores Oracle.

Las vistas de cada modelo están en `resources/views/{modelo}/` (por ejemplo, `resources/views/ciudad/`) y contienen `index`, `create`, `update`, `delete`, `form` y `view`. Los formularios y listados reutilizan plantillas comunes para mantener el mismo comportamiento y facilitar su personalización por modelo.

Antes de ejecutar `php artisan migrate`, confirma que las tablas `IMAJ_*` no existan ya en el esquema. Las migraciones crean las tablas y secuencias desde cero; no ejecutes `migrate:fresh` en una base con datos.

Después de migrar, crea el primer administrador de forma interactiva con `php artisan app:create-admin`. La contraseña se solicita de forma oculta y se almacena como hash en `PASSWORD_HASH`; los valores existentes de `CONTRASEÑA` se copian al nuevo campo hasheados, sin borrar ni modificar la columna original.

Perfiles iniciales: `ADMINISTRADOR`, `RECEPCIONISTA`, `TECNICO`, `RADIOLOGO` y `FINANZAS`. Las autorizaciones de rutas están centralizadas en `config/imaj.php`.

La pauta se cubre con cinco gestores (agendar orden, generar presupuesto, registrar pago, actualizar examen y publicar informe), tres reportes (órdenes por sucursal/estado, ingresos mensuales y exámenes por urgencia/estado), `IMAJ_APP_PKG` (CRUD, gestores, reportes y función de total de presupuesto) y triggers de secuencia/auditoría en las 16 tablas.

Los reportes de ingresos usan la fecha de auditoría de cada pago, porque el esquema de pagos no tiene una columna de fecha. La tabla `IMAJ_AUDITORIA` conserva tabla, operación, ID, usuario de aplicación y fecha, pero no copia datos clínicos.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
