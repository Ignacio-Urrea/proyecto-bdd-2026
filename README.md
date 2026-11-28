# Proyecto IMAJ — Bases de Datos 2026

Aplicación Laravel con Oracle XE 21c para administrar clínicas, sucursales, pacientes, órdenes, presupuestos, pagos, exámenes e informes.

## Antes de empezar

Hay tres nombres distintos que no se deben confundir:

| Concepto | Ejemplo | Para qué sirve |
| --- | --- | --- |
| Nombre de la conexión en SQL Developer | `PROYECTODB` | Es una etiqueta guardada en SQL Developer. No crea un usuario ni una base de datos. |
| Usuario Oracle | `SYSTEM`, `PROYECTODB` o `PROYECTO_BD` | Es el usuario con el que se conecta realmente. Sus tablas pertenecen a su esquema. Debe coincidir con `DB_USERNAME` en Laravel. |
| Usuario de la aplicación web | `superadmin` | Se utiliza en el formulario de inicio de sesión del sitio, después de cargar los datos demo. |

**Eliminar una conexión de SQL Developer no elimina sus tablas ni sus datos.** Otra conexión con el mismo usuario Oracle y servicio permite ver las mismas tablas.

Los comandos de PowerShell de esta guía se ejecutan en la raíz del proyecto: la carpeta que contiene `artisan`, `composer.json` y `.env.example`. Cada colaborador debe usar la ruta de su propia copia; no es necesariamente la misma en todos los computadores.

## 1. Requisitos locales

- PHP **8.4.1 o superior compatible con `composer.lock`**. Aunque `composer.json` declara PHP 8.3, las dependencias bloqueadas de Symfony requieren al menos 8.4.1. La instalación se verificó con PHP 8.4.28.
- Composer 2 y Node.js con npm. El frontend se verificó con Node 24.
- Oracle Database XE 21c instalado y ejecutándose. SQL Developer es el cliente: instalarlo por sí solo no instala el servidor Oracle.
- Extensión PHP OCI8 y Oracle Instant Client. La versión de PHP, arquitectura y variante TS/NTS de la extensión deben coincidir. Las bibliotecas del cliente Oracle deben estar disponibles en `PATH`.
- Las demás extensiones PHP que indique `composer check-platform-reqs`.

En PowerShell:

```powershell
php --version
php --ri oci8
composer --version
node --version
npm --version
```

Si un comando no se reconoce, corrige el `PATH` y abre una terminal nueva. Los helpers privados dentro de `.codex` y las herramientas instaladas en otro computador no se distribuyen al hacer `git pull`.

## 2. Preparar Laravel y el frontend

Desde la raíz del repositorio:

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
composer install
composer check-platform-reqs
npm ci
npm run build
```

Para una instalación nueva, genera la clave de Laravel una sola vez:

```powershell
php artisan key:generate
```

Si ya tienes un `.env` configurado y una `APP_KEY`, consérvalos al actualizar el código.

**No uses `composer setup` para esta guía:** ese comando ejecuta migraciones. La instalación desde cero con las migraciones actuales falla al compilar `IMAJ_APP_PKG` antes de que migraciones posteriores agreguen las columnas que utiliza. Aquí se usa el instalador SQL completo.

## 3. Identificar y configurar el esquema Oracle

Abre una hoja SQL en la conexión que utilizarás y ejecuta:

```sql
SELECT USER AS USUARIO_ORACLE,
       SYS_CONTEXT('USERENV', 'CON_NAME') AS CONTENEDOR
FROM DUAL;
```

Para Oracle XE local se utiliza normalmente el host `127.0.0.1`, puerto `1521` y **nombre de servicio `XEPDB1`**. En SQL Developer selecciona «Nombre de servicio», no «SID». Si aparece `CDB$ROOT`, revisa el servicio antes de instalar el proyecto.

Para una instalación nueva se recomienda un usuario dedicado. Desde una conexión administrativa a `XEPDB1`, puedes crearlo así, reemplazando la contraseña de ejemplo:

```sql
CREATE USER PROYECTO_BD
IDENTIFIED BY "REEMPLAZAR_CON_CLAVE_ORACLE"
DEFAULT TABLESPACE USERS
TEMPORARY TABLESPACE TEMP
QUOTA 500M ON USERS;

GRANT CREATE SESSION, CREATE TABLE, CREATE SEQUENCE,
      CREATE TRIGGER, CREATE PROCEDURE, CREATE VIEW
TO PROYECTO_BD;
```

Luego crea una conexión con ese usuario, su contraseña y el servicio `XEPDB1`. Este paso no es necesario si ya tienes un usuario preparado.

Si el entorno de desarrollo existente tiene las tablas bajo `SYSTEM` y vas a continuar con ese mismo usuario, usa esa conexión para instalar y configura `DB_USERNAME=SYSTEM`. El nombre visible de la conexión no cambia el propietario de las tablas. La sección de reinicio al final permite borrar solamente los objetos del proyecto sin eliminar el usuario.

En `.env`, ajusta los valores para que correspondan a esa conexión real:

```dotenv
DB_CONNECTION=oracle
DB_HOST=127.0.0.1
DB_PORT=1521
DB_DATABASE=XE
DB_SERVICE_NAME=XEPDB1
DB_USERNAME=PROYECTO_BD
DB_PASSWORD="REEMPLAZAR_CON_CLAVE_ORACLE"
DB_CHARSET=AL32UTF8
```

`PROYECTO_BD` es un ejemplo; usa el resultado de `SELECT USER`. La contraseña aquí es la de Oracle, no la contraseña de `superadmin`. No subas `.env`, contraseñas ni hashes al repositorio.

## 4. Instalar el esquema vacío

Usa [IMAJ_INSTALL.sql](database/oracle/IMAJ_INSTALL.sql). Crea tablas, secuencias, funciones, procedimientos, paquetes, triggers y las tablas Laravel de sesiones, cache y cola. `clinicas_tablas.sql` por sí solo no es un instalador completo.

El instalador exige que no existan objetos previos del proyecto. Si deseas conservar una base que ya funciona, ve a «Actualizar un entorno existente». Si deseas borrar sus datos y reinstalar, usa primero «Reiniciar el proyecto con el mismo usuario».

### Desde Oracle SQL Developer

1. Conecta con el usuario Oracle elegido y abre una hoja de trabajo de esa conexión.
2. En **Herramientas → Preferencias → Base de datos → Hoja de trabajo** (`Tools → Preferences → Database → Worksheet`), configura **Ruta por defecto para buscar scripts** (`Default path to look for scripts`) con la ruta completa a la carpeta `database\oracle` de tu copia del repositorio. El instalador incluye otros archivos mediante rutas relativas; conserva la estructura completa del repositorio.
3. En la hoja, ejecuta con **F5 / Ejecutar script**, no solamente Ctrl+Enter:

```sql
@IMAJ_INSTALL.sql
```

Espera a que aparezca:

```text
Instalacion completa: 17 tablas IMAJ, 8 tablas Laravel, 17 secuencias y 34 triggers activos.
Instalacion IMAJ finalizada correctamente.
```

Si aparecen errores, no continúes con el seed hasta resolverlos. La instalación contiene DDL: un error no revierte automáticamente todos los objetos que ya se crearon.

### Desde SQL*Plus

En PowerShell, desde la raíz del proyecto:

```powershell
Set-Location database\oracle
sqlplus PROYECTO_BD@//127.0.0.1:1521/XEPDB1
```

Reemplaza el usuario por el de tu entorno. SQL*Plus solicitará su contraseña. Una vez conectado:

```sql
@IMAJ_INSTALL.sql
```

**El instalador SQL no registra migraciones de Laravel. No ejecutes después `php artisan migrate`, `migrate:fresh` ni `composer setup` sobre este esquema.**

## 5. Cargar cuentas y datos de demostración

Este paso es independiente de la instalación de tablas. [IMAJ_DEMO_SEED.sql](database/oracle/IMAJ_DEMO_SEED.sql) agrega datos demo al esquema ya instalado; no crea las tablas.

### Generar el hash bcrypt

En una terminal, desde la raíz del proyecto —la carpeta con `artisan`— ejecuta:

```powershell
php artisan tinker
```

Dentro de Tinker, reemplaza el texto de ejemplo por la contraseña que quieres para las cuentas web:

```php
Hash::make('REEMPLAZAR_CON_CLAVE_DEMO');
```

Copia el resultado completo **sin las comillas externas, sin espacios y sin saltos de línea**. Debe tener exactamente **60 caracteres** y normalmente comenzar con `$2y$`. Sal de Tinker con `exit`.

La contraseña original se utiliza para iniciar sesión en la web. El hash se pega en el seed. Ninguno de los dos cambia la contraseña del usuario Oracle.

### Ejecutar el seed

En SQL Developer, en la misma conexión donde instalaste las tablas, ejecuta con **F5**:

```sql
SET DEFINE ON
@IMAJ_DEMO_SEED.sql
```

Cuando aparezca **«Hash bcrypt para las cuentas de demostracion»**, pega el hash. El script actualizado también ejecuta `SET DEFINE ON` internamente: el instalador deja la sustitución de variables desactivada y el seed necesita activarla para leer el valor introducido.

Si aparece `SP2-0310`, SQL Developer no encontró el archivo. Revisa la carpeta de scripts o usa su ruta absoluta entre comillas, por ejemplo:

```sql
@"C:\ruta\a\tu\proyecto-bdd-2026\database\oracle\IMAJ_DEMO_SEED.sql"
```

La ruta es un ejemplo: usa **Copiar como ruta** sobre el archivo real en el Explorador de Windows y agrega `@` delante de la comilla inicial.

Al terminar correctamente, el seed informa que los datos se sincronizaron. En un esquema vacío carga 4 perfiles, 6 cuentas, 3 regiones, 9 ciudades, 27 direcciones, 2 clínicas, 2 sucursales, 10 pacientes, 10 prestaciones, 20 órdenes, 20 presupuestos, 20 exámenes, 60 detalles, 60 pagos, 30 informes y 38 imágenes.

| Usuario web | Perfil |
| --- | --- |
| `superadmin` | SUPERADMIN |
| `clinica_admin1`, `clinica_admin2` | CLINICA_ADMIN |
| `sucursal_admin1`, `sucursal_admin2` | SUCURSAL_ADMIN |
| `radiologo` | RADIOLOGO |

Todas las cuentas demo usan la contraseña con la que generaste el hash. El seed puede volver a ejecutarse sin duplicar sus registros demo, pero actualiza la contraseña de esas cuentas y los datos demo que reconoce. Conserva las filas ajenas a esas claves y elimina roles heredados solo cuando no tienen usuarios asociados. Los pacientes y RUT son ficticios; las rutas de imágenes e informes son metadatos, no archivos clínicos reales.

`php artisan db:seed` ejecuta el seeder genérico de Laravel; **no sustituye a `IMAJ_DEMO_SEED.sql`**.

## 6. Iniciar y verificar la aplicación

En PowerShell, vuelve a la raíz del proyecto y ejecuta:

```powershell
php artisan config:clear
composer dev
```

Abre **http://localhost:8000**. Deja esa terminal abierta; Ctrl+C detiene los procesos de desarrollo. Inicia sesión con `superadmin` y la contraseña demo original, no con el hash ni con la contraseña de Oracle.

Para ejecutar las pruebas del repositorio:

```powershell
composer test
```

Estas pruebas no sustituyen la comprobación de la instalación Oracle. En la hoja SQL del proyecto puedes verificar:

```sql
SELECT OBJECT_NAME, OBJECT_TYPE
FROM USER_OBJECTS
WHERE STATUS = 'INVALID'
  AND (SUBSTR(OBJECT_NAME, 1, 5) = 'IMAJ_'
       OR SUBSTR(OBJECT_NAME, 1, 9) IN ('SEQ_IMAJ_', 'TRG_IMAJ_'));

SELECT COUNT(*) AS CUENTAS_DEMO FROM IMAJ_USUARIOS;
SELECT COUNT(*) AS ORDENES FROM IMAJ_ORDENES;
```

La primera consulta no debe devolver filas. En una instalación nueva con el seed, las otras devuelven 6 cuentas y 20 órdenes.

## Actualizar un entorno existente

Si ya tenías el proyecto funcionando, conserva `.env` y los datos. Integra los cambios de la rama del equipo; ejecuta `composer install` o `npm ci` si cambiaron sus archivos de dependencias.

Para las correcciones del paquete de acceso, desde la hoja del usuario propietario y con la carpeta de scripts configurada, ejecuta con F5:

```sql
@IMAJ_ACCESS_PKG.sql
SHOW ERRORS PACKAGE BODY IMAJ_ACCESS_PKG
```

Reinicia `composer dev`. El cambio del dashboard viene con el código PHP; no requiere reinstalar el esquema ni repetir el seed.

Si usaste una versión antigua del instalador SQL que creó tablas Laravel con nombres entre comillas en minúsculas, actualizar el archivo no renombra los objetos existentes. El driver OCI8 consulta nombres en mayúsculas. Conserva los datos mediante una corrección específica de nombres, o usa el reinicio siguiente si tu base de desarrollo es descartable.

## Reiniciar el proyecto con el mismo usuario

**Este procedimiento elimina permanentemente los datos de las tablas enumeradas.** Úsalo solo cuando quieras reinstalar tu esquema de desarrollo. Puedes conservar el mismo usuario Oracle, incluso si tu entorno existente utiliza `SYSTEM`; no elimines el usuario ni todas las tablas de SYSTEM.

Detén la aplicación y verifica el usuario y contenedor con la consulta de la sección 3. En la hoja SQL de ese usuario, ejecuta con F5:

```sql
SET SERVEROUTPUT ON
WHENEVER SQLERROR EXIT SQL.SQLCODE

BEGIN
    FOR r IN (
        SELECT table_name
        FROM user_tables
        WHERE UPPER(table_name) IN (
            'IMAJ_ROLES', 'IMAJ_REGIONES', 'IMAJ_USUARIOS',
            'IMAJ_CIUDADES', 'IMAJ_DIRECCIONES', 'IMAJ_CLINICAS',
            'IMAJ_PRESTACIONES', 'IMAJ_SUCURSALES', 'IMAJ_PACIENTES',
            'IMAJ_ORDENES', 'IMAJ_EXAMENES', 'IMAJ_PRESUPUESTOS',
            'IMAJ_PAGOS', 'IMAJ_DETALLE_PRESTACIONES',
            'IMAJ_INFORMES', 'IMAJ_IMAGENES', 'IMAJ_AUDITORIA',
            'USERS', 'PASSWORD_RESET_TOKENS', 'SESSIONS',
            'CACHE', 'CACHE_LOCKS', 'JOBS', 'JOB_BATCHES',
            'FAILED_JOBS', 'MIGRATIONS'
        )
    ) LOOP
        EXECUTE IMMEDIATE
            'DROP TABLE "' || USER || '"."' || r.table_name ||
            '" CASCADE CONSTRAINTS PURGE';
        DBMS_OUTPUT.PUT_LINE('Tabla eliminada: ' || r.table_name);
    END LOOP;

    FOR r IN (
        SELECT object_name, object_type
        FROM user_objects
        WHERE object_type IN (
            'PACKAGE', 'FUNCTION', 'PROCEDURE', 'SEQUENCE', 'TRIGGER'
        )
        AND (
            SUBSTR(object_name, 1, 5) = 'IMAJ_'
            OR SUBSTR(object_name, 1, 9) = 'SEQ_IMAJ_'
            OR SUBSTR(object_name, 1, 9) = 'TRG_IMAJ_'
        )
    ) LOOP
        EXECUTE IMMEDIATE
            'DROP ' || r.object_type || ' "' || USER ||
            '"."' || r.object_name || '"';
        DBMS_OUTPUT.PUT_LINE('Objeto eliminado: ' || r.object_name);
    END LOOP;
END;
/
```

Cuando termine sin errores, vuelve a ejecutar **`IMAJ_INSTALL.sql`** y, si necesitas cuentas y datos demo, **`IMAJ_DEMO_SEED.sql`**, siguiendo las secciones 4 y 5. No necesitas cambiar el usuario ni la contraseña Oracle.

## Pruebas

Ejecuta la suite habitual y la compilación del frontend desde la raíz del proyecto:

```powershell
composer test
npm run build
```

Para probar las operaciones reales contra Oracle, usa un esquema **de desarrollo** configurado en `.env`, con `IMAJ_INSTALL.sql` y `IMAJ_DEMO_SEED.sql` ya ejecutados:

```powershell
php artisan config:clear
php vendor/phpunit/phpunit/phpunit -c phpunit.oracle.xml
```

Esta suite adicional prueba altas, consultas, modificaciones y eliminaciones de los 16 módulos, los cinco gestores, reportes, filtros, paginación, autenticación, auditoría y permisos de las seis cuentas demo. Verifica los valores guardados en Oracle, además de las respuestas HTTP. Usa sesiones y caché en memoria, y revierte las escrituras y filas de auditoría al terminar cada prueba. Las secuencias Oracle avanzan aunque se revierta la transacción; no exige IDs consecutivos. No ejecuta migraciones, reinstala tablas ni vuelve a cargar el seed. No la ejecutes contra producción.

Si ya tenías instalado el proyecto, actualiza `database/oracle/IMAJ_APP_PKG.sql` en tu mismo esquema antes de correrla. El paquete corregido evita que Oracle XE 21c informe una actualización exitosa sin guardar algunos campos. No necesitas eliminar tablas ni datos.

## Problemas frecuentes

| Síntoma | Qué revisar |
| --- | --- |
| `php`, `composer` o `npm` no se reconoce | Instala la herramienta, corrige `PATH` y abre una terminal nueva en tu computador. |
| Composer rechaza PHP 8.3 | El lock actual requiere PHP 8.4.1 como mínimo. Comprueba `php --version` y `composer check-platform-reqs`. |
| OCI8 no carga | Comprueba `php --ri oci8`, el `php.ini` usado por la terminal, la arquitectura/TS/NTS y el cliente Oracle en `PATH`. |
| `SP2-0310: No se ha podido abrir el archivo` | Es un problema de ruta. Configura la carpeta `database\oracle` en SQL Developer o usa la ruta absoluta del archivo. |
| `ORA-20100: Se requiere un hash bcrypt valido de 60 caracteres` | Ejecuta `SET DEFINE ON` antes del seed y pega el hash completo, sin comillas ni espacios. No pegues la contraseña original. Usa el script actualizado, que activa DEFINE por sí mismo. |
| `ORA-06512` acompañando otro error | Indica la línea de PL/SQL. Busca el primer error de la salida; por ejemplo, `ORA-20100` explica el problema del hash. |
| El instalador detecta objetos previos | El esquema ya contiene tablas o paquetes. Conserva la instalación existente o reiníciala expresamente; borrar la conexión no los elimina. |
| `ORA-24344` durante `php artisan migrate` desde cero | La ruta de migraciones tiene el problema de orden descrito en la sección 2. Para instalar desde cero, usa el instalador SQL sobre un esquema limpio. |
| `ORA-00942` en sesiones o cache | Verifica usuario, servicio, instalación de tablas Laravel y nombres en mayúsculas. |
| `ORA-12514` | Verifica que Oracle esté iniciado, que `XEPDB1` esté abierto y que la conexión use ese nombre de servicio. |
| `ORA-01017` | Revisa el usuario y la contraseña Oracle. El usuario web `superadmin` y su hash no sirven para conectar a Oracle. |

## Estructura de la aplicación

La aplicación incluye modelos sin prefijo (`Rol`, `Paciente`, etc.) y CRUD web para 16 tablas. Las tablas y secuencias conservan el prefijo `IMAJ_`. Laravel valida entradas y controla las rutas; `IMAJ_ACCESS_PKG` aplica el alcance por usuario en Oracle y delega las operaciones de negocio a `IMAJ_APP_PKG`. El dashboard obtiene los IDs permitidos mediante `OracleCrudService` antes de calcular sus indicadores.

Las vistas están en `resources/views/{modelo}/`, con páginas `index`, `create`, `update`, `delete`, `form` y `view`, y reutilizan plantillas comunes. Las autorizaciones están en `config/imaj.php`. Los perfiles activos son `SUPERADMIN`, `CLINICA_ADMIN`, `SUCURSAL_ADMIN` y `RADIOLOGO`. Las contraseñas web se guardan como hash en `IMAJ_USUARIOS.CONTRASEÑA`.

Los cinco gestores permiten agendar órdenes, generar presupuestos, registrar pagos, actualizar exámenes y publicar informes. Los tres reportes muestran órdenes por sucursal/estado, ingresos mensuales y exámenes por urgencia/estado.

Las prestaciones pertenecen a una clínica mediante `CLINICA_ID`. Los presupuestos solo aceptan prestaciones habilitadas de la clínica correspondiente; cada prestación aparece como máximo una vez por presupuesto.

Las escrituras exitosas emiten `OracleOperationCompleted` después de la transacción. Su listener registra metadatos de operación, recurso y actor, sin datos clínicos. Los flujos actuales son síncronos; no hay Jobs propios de negocio.

`IMAJ_AUDITORIA` registra tabla, operación, ID, actor y fecha sin copiar datos clínicos. Los reportes de ingresos usan la fecha de auditoría del pago, porque la tabla de pagos no tiene una columna de fecha.

El diagrama incluido en el repositorio está en [diagrama3fn.jpeg](diagrama3fn.jpeg). Sigue pendiente resolver si una imagen puede asociarse simultáneamente a un examen y a un informe.
