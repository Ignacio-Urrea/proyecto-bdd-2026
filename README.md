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

`clinicas_tablas.sql` documenta el esquema actual de tablas, incluidas las columnas agregadas posteriormente como `RADIOLOGO_ID`; las migraciones siguen siendo la ruta de instalación de la aplicación e instalan además secuencias, paquetes y triggers. No ejecutes el SQL base y las migraciones sobre el mismo esquema.

## Mantenedores IMAJ

La aplicación incluye modelos sin prefijo (`Rol`, `Paciente`, etc.) y CRUD web para las 16 tablas. Las tablas y secuencias conservan el prefijo `IMAJ_`. Laravel valida entradas y controla el acceso a rutas; `IMAJ_ACCESS_PKG` aplica en Oracle el alcance por usuario para lecturas, escrituras, gestores, paginación y reportes, y delega las mutaciones de negocio a `IMAJ_APP_PKG`. La aplicación solo envía el ID autenticado, no listas de registros autorizados.

Las vistas de cada modelo están en `resources/views/{modelo}/` (por ejemplo, `resources/views/ciudad/`) y contienen `index`, `create`, `update`, `delete`, `form` y `view`. Los formularios y listados reutilizan plantillas comunes para mantener el mismo comportamiento y facilitar su personalización por modelo.

La capa HTTP usa rutas protegidas por middleware, Form Requests para validar CRUD, autenticación y gestores, y servicios para las operaciones Oracle y flujos de negocio. Las escrituras exitosas emiten `OracleOperationCompleted` después de la transacción; su listener registra solo metadatos de operación, recurso y actor, sin datos clínicos. Por ahora no se agregan Jobs: no hay tareas que deban ejecutarse en segundo plano y los flujos deben responder inmediatamente.

Antes de ejecutar `php artisan migrate`, confirma que las tablas `IMAJ_*` no existan ya en el esquema. Las migraciones crean las tablas y secuencias desde cero; no ejecutes `migrate:fresh` en una base con datos.

La migración de consolidación mantiene el hash en `IMAJ_USUARIOS.CONTRASEÑA` y elimina la columna duplicada `PASSWORD_HASH`. Para una instalación vacía, crea el primer administrador con `php artisan app:create-admin` o carga las cuentas y datos de demostración indicados a continuación.

Los perfiles activos de la aplicación son `SUPERADMIN`, `CLINICA_ADMIN`, `SUCURSAL_ADMIN` y `RADIOLOGO`; las autorizaciones están centralizadas en `config/imaj.php`.

### Datos de demostración Oracle

Después de `php artisan migrate`, ejecuta `@database/oracle/IMAJ_DEMO_SEED.sql` desde SQLcl o SQL*Plus, conectado al mismo esquema Oracle. El script carga las seis cuentas requeridas, dos clínicas y sucursales, 10 pacientes sintéticos, cinco prestaciones por clínica y 20 órdenes con sus presupuestos, pagos, exámenes, informes e imágenes de ejemplo. También carga tres regiones, nueve ciudades y 27 direcciones.

El script funciona tanto después de una migración limpia como en una base con datos. Reutiliza o actualiza las filas que identifica como demo, sincroniza las secuencias antes de insertar y conserva las demás filas. Configura las cuentas solicitadas (incluido el `superadmin` de la migración) con la contraseña suministrada como hash. Solo elimina los roles heredados `ADMINISTRADOR`, `RECEPCIONISTA`, `TECNICO` y `FINANZAS` cuando no están asignados a ningún usuario; los que tengan usuarios se conservan sin interrumpir la carga. Se puede volver a ejecutar sin duplicar sus filas demo. Las rutas de imágenes e informes son metadatos de demostración, no archivos clínicos reales.

La contraseña común se proporciona como hash bcrypt en un prompt oculto. Genera el hash localmente con `Hash::make(...)` desde Tinker de Laravel y pega solo el resultado en el prompt; no guardes contraseñas ni hashes en el repositorio. Los datos de pacientes y sus RUT son ficticios.

Las prestaciones pertenecen a una clínica mediante `CLINICA_ID`. Al generar un presupuesto, Oracle obtiene la clínica de la sucursal de la orden y solo acepta prestaciones habilitadas de esa clínica. Cada prestación solo puede aparecer una vez por presupuesto; la base de datos impone esa unicidad en el detalle.

El diagrama de entidades y la revisión de normalización están en [database/ESQUEMA_Y_3FN.md](database/ESQUEMA_Y_3FN.md). El modelo mantiene como pendiente resolver si una imagen puede asociarse simultáneamente a un examen y a un informe.

La pauta se cubre en código con cinco gestores (agendar orden, generar presupuesto, registrar pago, actualizar examen y publicar informe), tres reportes expuestos en la aplicación (órdenes por sucursal/estado, ingresos mensuales y exámenes por urgencia/estado), `IMAJ_ACCESS_PKG` (autorización y consultas con alcance) y `IMAJ_APP_PKG` (CRUD, gestores y funciones de negocio), además de triggers de secuencia/auditoría en las 16 tablas.

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
