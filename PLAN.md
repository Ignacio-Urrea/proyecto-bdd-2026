# Plan de trabajo

## Objetivo

Completar y documentar el proyecto de base de datos IMAJ siguiendo los contenidos de las clases de SQL y PL/SQL de Oracle.

La solución debe entregar una aplicación funcional basada en servidor robusto, con:

- Control de acceso mediante autenticación y perfiles diferenciados.
- Al menos 10 mantenedores CRUD.
- Al menos 5 gestores de información.
- Exactamente 3 reportes estratégicos.
- Procedimientos almacenados, funciones, secuencias y cursores integrados y verificables desde la aplicación.
- Triggers de control y monitoreo.
- Diseño gráfico de la base de datos normalizada como mínimo a tercera forma normal.
- Explicación de la arquitectura cliente-servidor y de la capa de servicios.

## Estado actual

- Existe un modelo de datos con 16 tablas de negocio IMAJ más `IMAJ_AUDITORIA`. Las prestaciones pertenecen a una clínica y cada par presupuesto–prestación es obligatorio y único. El seed de demostración ya está preparado para cargar y reconciliar datos, pero falta ejecutarlo y validarlo en Oracle.
- `clinicas_tablas.sql` se sincronizó con el esquema final de las migraciones, incluyendo `RADIOLOGO_ID`, auditoría, índices y restricciones posteriores.
- `IMAJ_APP_PKG` contiene CRUD dinámico, gestores y funciones; `IMAJ_ACCESS_PKG` aplica el alcance de usuario en Oracle y ofrece la interfaz que consume Laravel.
- Los reportes y listados paginados construyen el alcance directamente en Oracle; la paginación usa filtros, `OFFSET/FETCH` y conteo total.
- Existen scripts separados para secuencias, procedimientos, funciones, cursores y triggers.
- Los triggers se separaron en archivos individuales dentro de `database/oracle/triggers`.
- Las migraciones que ya fueron cargadas en la base no deben modificarse.
- El sistema ya supera el mínimo de 10 mantenedores mediante sus recursos CRUD existentes.
- Las pruebas automatizadas pasan, pero no se verificó la instalación ni la operación contra una instancia Oracle.

## Requerimiento mínimo de entrega

| Componente | Cantidad mínima | Implementación prevista |
| --- | ---: | --- |
| Mantenedores CRUD | 10 | Insert, Select, Update y Delete mediante procedimientos almacenados. |
| Gestores | 5 | Procesos transaccionales que cruzan datos existentes. |
| Reportes | 3 | Consultas estratégicas Oracle desplegadas en tablas mediante procedimientos y cursores. |
| Roles de usuario | Más de uno | Acceso diferenciado según perfiles y permisos. |
| Triggers | Según el negocio | Integridad, auditoría, normalización y monitoreo. |

Los módulos CRUD deben invocar objetos Oracle desde la capa de lógica de negocio. Laravel valida entradas y controla permisos de rutas; `IMAJ_ACCESS_PKG` aplica el alcance de datos en Oracle y presenta sus resultados.

## Diagnóstico de la pauta: Entregables de SW y BBDD (31 pts)

Estados usados: **Cumple (código)** significa que la implementación fuente satisface el indicador, pero no certifica que esté ejecutándose en Oracle; **Parcial** identifica evidencia junto con brechas; **No cumple** indica que falta un entregable requerido; **No comprobado** requiere ejecución o evidencia que no estuvo disponible.

| # | Indicador de la pauta | Estado | Diagnóstico y evidencia |
| ---: | --- | --- | --- |
| 1 | Claves primarias coherentes en todas las tablas | Cumple (código) | Las 16 tablas de negocio y `IMAJ_AUDITORIA` en `clinicas_tablas.sql` declaran PK. |
| 2 | Claves foráneas con tipos y relaciones 1:N correctos | Cumple (código) | Las relaciones principales usan columnas `NUMBER` y referencias a PK; pacientes se asocian a sucursal y prestaciones a clínica. La migración de prestaciones verifica las asignaciones existentes. |
| 3 | Relaciones N:M mediante tablas intermedias bien estructuradas | Cumple (código) | `IMAJ_DETALLE_PRESTACIONES` conecta presupuesto y prestación mediante dos FK obligatorias y una restricción única compuesta; el gestor también rechaza IDs repetidos. Aplicación de la migración aún no comprobada en Oracle. |
| 4 | Esquema justificado en 3FN | Parcial (diagrama existente) | El diagrama gráfico está en [diagrama3fn.jpeg](./diagrama3fn.jpeg) y la justificación está en `database/ESQUEMA_Y_3FN.md`; el artefacto visual sí está entregado. Se mantiene parcial porque la regla de asociación de imágenes sigue sin definirse ni imponerse: Oracle permite que `EXAMEN_ID` e `INFORME_ID` estén vacíos o informados simultáneamente y no comprueba que el informe pertenezca al mismo examen. Además, el gráfico no representa `IMAJ_AUDITORIA` ni deja visible la unicidad compuesta del detalle; falta sincronizarlo con el esquema final. |
| 5 | Script SQL ejecutable secuencialmente en Oracle sin errores | No comprobado | `clinicas_tablas.sql` contiene el DDL de las tablas en orden de dependencias, pero es un snapshot: no instala secuencias, paquetes ni triggers. La ruta de instalación documentada es `php artisan migrate`; la ejecución y compilación completa no están verificadas porque la conexión Oracle `PROYECTODB` devuelve `ORA-01017`. No marcar como cumplido hasta instalar en un esquema limpio y confirmar que no queden objetos inválidos. |
| 6 | Script de poblamiento con datos coherentes para operar todos los módulos | Cumple (código) | `database/oracle/IMAJ_DEMO_SEED.sql` carga roles y cuentas, geografía, clínicas, sucursales, pacientes, prestaciones, órdenes, presupuestos, detalles, pagos, exámenes, informes e imágenes con las cantidades solicitadas. Sincroniza secuencias y reconcilia los pagos al reutilizar presupuestos demo; admite tablas vacías o con datos previos. Falta ejecutarlo en Oracle. |
| 7 | Cantidad y modularidad de procedimientos, parámetros y variables | Cumple (código) | `IMAJ_APP_PKG` expone los procedimientos genéricos `LIST_ROWS`, `LIST_ROWS_PAGE`, `COUNT_ROWS_PAGE`, `FIND_ROW`, `INSERT_ROW`, `UPDATE_ROW` y `DELETE_ROW`, además de `LIST_ORDER_PRESTATIONS`, cinco gestores, tres reportes y funciones de negocio; los 16 recursos usan el contrato CRUD. |
| 8 | COMMIT/ROLLBACK efectivos ante fallos en todos los procedimientos | Parcial | Las mutaciones de `IMAJ_APP_PKG` ahora usan savepoints y `ROLLBACK TO` ante fallos; Laravel conserva el `COMMIT` externo mediante `DB::transaction`. No se pudo comprobar este contrato con Oracle y los procedimientos independientes mantienen su propio COMMIT/ROLLBACK. |
| 9 | Excepciones PL/SQL controladas y mensajes descriptivos | Parcial | Las mutaciones del paquete ahora revierten a su savepoint y relanzan la excepción original; validaciones de negocio usan `RAISE_APPLICATION_ERROR` con mensajes. La traducción de errores de restricciones sigue en Laravel y falta validación real en Oracle. |
| 10 | Funciones con retorno coherente y prefijo estándar | Cumple (código) | El paquete usa `IMAJ_TOTAL_BUDGET`; junto a `IMAJ_AMOUNT_PAID` e `IMAJ_PATIENT_NAME`, las funciones siguen el prefijo del proyecto. Falta validar retornos en Oracle. |
| 11 | Uso transversal de funciones en consultas, procedimientos o triggers | Cumple (código) | `IMAJ_APP_PKG` integra `IMAJ_PATIENT_NAME` en `CREATE_ORDER` para validar el paciente, `IMAJ_AMOUNT_PAID` en `REGISTER_PAYMENT` e `IMAJ_TOTAL_BUDGET` para actualizar el estado del presupuesto. Falta compilar y probar estos llamados en Oracle. |
| 12 | Secuencias creadas y aplicadas consistentemente | Cumple (código) | `IMAJ_SEQUENCES.sql` declara 17 secuencias; los objetos Oracle usan `NEXTVAL` y los gestores independientes también usan `CURRVAL`. |
| 13 | Cursores explícitos/implícitos con recorrido y uso en consultas | Cumple (código) | `BUILD_BUDGET` declara y abre `C_SERVICES`, recorre filas con `FETCH`/`%NOTFOUND` y cierra el cursor, incluso ante excepción. Los listados y reportes también devuelven `SYS_REFCURSOR`; falta validación de compilación en Oracle. |
| 14 | Triggers BEFORE/AFTER con `:NEW` y `:OLD` configurados | Parcial | Hay triggers de PK, auditoría y normalización en scripts/migraciones; la conexión fallida impide confirmar que estén activos en la instancia Oracle. |
| 15 | Auditoría o reglas de integridad automáticas desde la BD | Cumple (código) | Se crea `IMAJ_AUDITORIA` y hay triggers de auditoría por tabla; PK, FK y restricciones CHECK también aplican integridad en la BD. |
| 16 | Arquitectura cliente-servidor de dos capas, con lógica pesada en Oracle | Cumple (código) | Laravel valida entradas y controla permisos de rutas; `IMAJ_ACCESS_PKG` calcula y aplica el alcance por usuario en lecturas, escrituras, gestores, reportes y paginación. Falta validación de extremo a extremo en Oracle. |
| 17 | Desacoplamiento: la aplicación invoca servicios de BD sin lógica dura | Cumple (código) | `OracleCrudService` llama a `IMAJ_ACCESS_PKG`, que aplica autorización y delega CRUD/gestores a `IMAJ_APP_PKG`; Laravel ya no construye ni envía listas de IDs autorizados. Falta validar la ruta de despliegue y ejecución en Oracle. |
| 18 | Login operativo validado contra la base de datos | Parcial | Hay autenticación contra `IMAJ_USUARIOS`, comprobación de hash y rate limiting; no se pudo realizar un inicio de sesión con Oracle. |
| 19 | Acceso por al menos dos roles con vistas/acciones restringidas | Cumple (código) | `config/imaj.php` define cuatro perfiles y permisos por recurso; middleware protege rutas e `IMAJ_ACCESS_PKG` aplica el alcance de datos por usuario en Oracle. |
| 20 | Mantenedores 1–3 invocan procedimientos desde la aplicación | Cumple (código) | Los recursos CRUD se registran con `Route::resource` y delegan lecturas/escrituras en `OracleCrudService` e `IMAJ_ACCESS_PKG`; este invoca los procedimientos CRUD de `IMAJ_APP_PKG`. |
| 21 | Mantenedores 4–7 invocan procedimientos desde la aplicación | Cumple (código) | El mismo flujo de servicio y paquetes Oracle se reutiliza para cada uno de los 16 recursos configurados. |
| 22 | Mantenedores 8–10 invocan procedimientos desde la aplicación | Cumple (código) | Se supera el mínimo de diez recursos; las operaciones pasan por el servicio y los paquetes Oracle. |
| 23 | Gestores 1–3 llaman procedimientos y generan información desde datos existentes | Cumple (código) | Los flujos de órdenes, presupuestos y pagos llaman las fachadas de `IMAJ_ACCESS_PKG`, que valida referencias y delega a `IMAJ_APP_PKG`; ejecución real pendiente. |
| 24 | Gestores 4–5 llaman procedimientos y generan información desde datos existentes | Cumple (código) | Los flujos de exámenes e informes también pasan por las fachadas Oracle con validación de alcance; ejecución real pendiente. |
| 25 | Tres reportes estratégicos en tablas, obtenidos por PA con cursores | Cumple (código) | Los tres reportes usan procedimientos y `SYS_REFCURSOR`; `IMAJ_ACCESS_PKG` filtra en Oracle según el usuario autenticado. Falta compilar y validar los procedimientos en Oracle. |
| 26 | Validación de tipos, obligatorios y formatos antes de invocar la BD | Cumple (código) | `CrudRequestRules` define reglas por recurso y acción; las FK se protegen además en Oracle y sus errores se traducen en mensajes. |
| 27 | La interfaz impide borrados/modificaciones inconsistentes e informa | Cumple (código) | Los formularios ofrecen opciones para referencias y `OracleCrudService` convierte errores de FK (`ORA-02291`/`ORA-02292`) en mensajes comprensibles. |
| 28 | Navegación ordenada, jerarquía visual y coherencia estética | Cumple (código) | `config/imaj.php` organiza navegación y permisos; `layouts/app.blade.php` implementa estructura y estilos compartidos. |
| 29 | Paginación o filtrado operativo con listados de alto volumen | Cumple (código) | `IMAJ_ACCESS_PKG.LIST_ROWS_PAGE` aplica filtros, alcance SQL y `OFFSET/FETCH`; `COUNT_ROWS_PAGE` conserva el total incluso en páginas vacías. Rendimiento real con volumen representativo sigue sin probarse en Oracle. |
| 30 | Conexión segura/estable sin credenciales expuestas en vistas | Parcial | La conexión toma valores desde variables de entorno y no desde vistas; no hay evidencia de transporte Oracle cifrado y no se pudo autenticar/validar estabilidad. |
| 31 | Operación de extremo a extremo con datos reales y sin fallos fatales | No comprobado | `php artisan test` pasó en esta revisión (34 pruebas y 419 aserciones); las pruebas automatizadas/mock no sustituyen una prueba de integración Oracle. El seed IMAJ ya existe, pero falta ejecutarlo y demostrar los módulos conectados a una instancia Oracle. |

### Hallazgos transversales y orden de trabajo

- **Esquema de referencia:** `clinicas_tablas.sql` del repositorio ya consolida la forma actual de las tablas, incluidos cambios posteriores de las migraciones. La ruta única documentada es `php artisan migrate`; el SQL independiente es un snapshot y los scripts Oracle auxiliares no deben ejecutarse además de las migraciones en el mismo esquema. Falta validar la ruta en un esquema Oracle vacío. La copia externa facilitada anteriormente es antigua e incompatible y no debe combinarse con el paquete actual.
- **Documentación:** se corrigió `README.md` para documentar el uso de `CONTRASEÑA` para hashes, la visibilidad de reportes con alcance restringido y la relación clínica–prestación.
- **Datos de demostración:** `database/oracle/IMAJ_DEMO_SEED.sql` contiene datos sintéticos, cinco prestaciones por clínica con precios de muestra y cuentas con hash bcrypt ingresado en un prompt oculto. Es idempotente para sus claves demo y conserva los datos ajenos; los roles heredados con usuarios no se eliminan. El script estático está preparado, pero todavía no se ha ejecutado ni comprobado en Oracle.
- **Alcance de reportes:** la aplicación expone exactamente tres reportes. `IMAJ_CURSORS.sql` conserva un cuarto procedimiento (`REPORT_PAID_BUDGETS`) que no se instala ni se expone por la ruta Laravel; mantenerlo fuera de la entrega de tres reportes o confirmar si debe retirarse.
- **Sin calificación inferida:** esta matriz diagnostica evidencia del código y brechas; no equivale a afirmar una nota ni a confirmar objetos activos en Oracle.
- Prioridad inmediata: instalar en Oracle limpio y validar compilación/contratos; ejecutar el seed y comprobar relaciones y pagos; acordar y aplicar la regla de asociación de imágenes y actualizar el diagrama; revisar el uso efectivo de cursores explícitos y paginación en grandes volúmenes; completar pruebas de extremo a extremo con Oracle.

## Fase 1: Consultas SQL

1. Mantener consultas claras con `SELECT`, `FROM`, alias y `WHERE`.
2. Utilizar `DISTINCT` cuando se requieran valores únicos.
3. Usar `BETWEEN`, `IN`, `LIKE`, operadores lógicos y `ORDER BY`.
4. Incorporar agregaciones con `GROUP BY` y restricciones de grupos con `HAVING`.
5. Utilizar joins con la sintaxis moderna `JOIN ... ON ...`.

## Fase 2: Estructuras PL/SQL

1. Organizar bloques con `DECLARE`, `BEGIN`, `EXCEPTION` y `END`.
2. Utilizar variables con tipos predefinidos y tipos asociados mediante `%TYPE` y `%ROWTYPE`.
3. Implementar estructuras `IF`, `ELSIF`, `LOOP` y `FOR`.
4. Incorporar manejo de excepciones y mensajes con `RAISE_APPLICATION_ERROR`.

## Fase 3: Secuencias

1. Mantener una secuencia por clave primaria incremental.
2. Utilizar `NEXTVAL` para generar el siguiente identificador.
3. Utilizar `CURRVAL` únicamente cuando se necesite recuperar el último valor generado por la sesión.
4. Definir `START WITH`, `INCREMENT BY`, `MINVALUE`, `MAXVALUE` y `NOCYCLE`.

## Fase 4: Procedimientos almacenados

1. Mantener procedimientos de carga, modificación y eliminación.
2. Utilizar un procedimiento I/U/D integral para operaciones del mismo recurso.
3. Bloquear tablas de escritura con `LOCK TABLE`.
4. Incluir `COMMIT` después de operaciones exitosas y `ROLLBACK` en la sección de excepciones.
5. Validar que las filas fueron actualizadas o eliminadas mediante `SQL%ROWCOUNT`.

## Fase 5: Funciones

1. Crear funciones que devuelvan un único valor.
2. Utilizar `SELECT INTO` para asignar el resultado de una consulta a una variable.
3. Manejar `NO_DATA_FOUND` y resultados nulos.
4. Reutilizar funciones en `SELECT`, `WHERE`, `HAVING`, `INSERT`, `UPDATE` y reportes.

## Fase 6: Cursores

1. Usar cursores explícitos para consultas que puedan devolver varias filas.
2. Declarar la consulta antes de `BEGIN`.
3. Abrir, recorrer con `FETCH` o `FOR`, y cerrar cada cursor.
4. Verificar `%ISOPEN`, `%FOUND`, `%NOTFOUND` y `%ROWCOUNT` cuando corresponda.
5. Preferir consultas SQL y `BULK COLLECT` antes de procesos fila por fila innecesarios.
6. Mantener los reportes mediante procedimientos que abran `SYS_REFCURSOR`.

## Fase 7: Triggers

1. Mantener una responsabilidad por trigger.
2. Utilizar triggers `BEFORE INSERT` para generar claves primarias.
3. Utilizar `:NEW` y `:OLD` para capturar los valores de las operaciones DML.
4. Utilizar triggers `AFTER INSERT OR UPDATE OR DELETE` para auditoría.
5. Mantener triggers de normalización separados de triggers de claves y auditoría.
6. No incluir `COMMIT`, `ROLLBACK` ni `SAVEPOINT` dentro de triggers.

## Fase 8: Migraciones

1. No modificar una migración que ya fue aplicada con `php artisan migrate`.
2. Crear una migración nueva para cualquier cambio posterior del esquema o de objetos Oracle.
3. Implementar `down` cuando la reversión preserve los datos; si una migración protege una asociación de negocio contra pérdida, impedir explícitamente el rollback destructivo y documentar cómo se recupera.
4. Ejecutar `php artisan migrate` únicamente para aplicar migraciones pendientes.

## Fase 9: Integración con Laravel

1. Mantener las operaciones CRUD a través de `IMAJ_ACCESS_PKG`, delegadas a `IMAJ_APP_PKG`.
2. Mantener validaciones de entrada y permisos de ruta en Laravel; aplicar en Oracle los permisos de datos y referencias.
3. Mantener los reportes con cursores Oracle devueltos a Laravel.
4. Registrar auditoría sin almacenar datos clínicos sensibles.

## Fase 10: Control de acceso

1. Mantener autenticación mediante usuario y contraseña.
2. Almacenar contraseñas como hash y no como texto plano.
3. Definir perfiles diferenciados, por ejemplo administrador, recepcionista, técnico, radiólogo y finanzas.
4. Restringir mantenedores, gestores y reportes según el rol.
5. Mantener la confidencialidad mediante permisos y contexto de usuario Oracle.

## Fase 11: Mantenedores CRUD

1. Seleccionar al menos 10 recursos relevantes del modelo IMAJ.
2. Implementar creación, lectura, actualización y eliminación mediante `IMAJ_ACCESS_PKG`, delegando el DML a `IMAJ_APP_PKG`.
3. Reservar las operaciones DML de escritura para procedimientos almacenados.
4. Obtener identificadores mediante secuencias.
5. Ejecutar consultas mediante cursores o procedimientos que devuelvan `SYS_REFCURSOR`.
6. Validar integridad referencial, restricciones y errores en la base de datos.
7. Registrar operaciones importantes mediante triggers de auditoría.
8. Mantener obligatorias y únicas las referencias presupuesto–prestación en `IMAJ_DETALLE_PRESTACIONES`.

## Fase 12: Gestores de información

Mantener los cinco gestores existentes:

1. Crear una orden asociada a paciente y sucursal.
2. Generar un presupuesto a partir de una orden y varias prestaciones.
3. Registrar pagos asociados a presupuestos.
4. Actualizar el estado de un examen y ejecutar la orden asociada.
5. Publicar informes asociados a exámenes.

Cada gestor debe utilizar procedimientos almacenados y, cuando corresponda, secuencias, funciones y cursores.

## Fase 13: Reportes estratégicos

Mantener exactamente tres reportes de alto valor:

1. Órdenes por sucursal y estado.
2. Ingresos mensuales por los pagos registrados.
3. Exámenes por urgencia y estado.

Cada reporte debe:

- Consultar datos reales existentes.
- Agrupar y ordenar información.
- Devolver resultados mediante un procedimiento Oracle y `SYS_REFCURSOR`.
- Presentarse en una tabla dentro de la aplicación, como pide la pauta.
- Mantener el acceso a través de los procedimientos y cursores también para usuarios con alcance restringido.

## Fase 14: Arquitectura y diseño

1. Documentar la solución seleccionada y las tecnologías utilizadas en `README.md`.
2. Diagrama gráfico entregado en [diagrama3fn.jpeg](./diagrama3fn.jpeg); mantenerlo sincronizado con el esquema y explicar claves/relaciones en `database/ESQUEMA_Y_3FN.md`.
3. **Pendiente:** completar la demostración formal de 3FN después de definir y restringir la relación de `IMAJ_IMAGENES`; actualizar el diagrama para incluir auditoría y restricciones relevantes.
4. Documentar la arquitectura cliente-servidor y el flujo de llamadas desde Laravel hacia paquetes, funciones y cursores.
5. Revisar la distribución de lógica entre Laravel y Oracle; la paginación y parte del alcance de datos siguen en PHP.

## Fase 15: Verificación

1. Ejecutar `php artisan test` (verificado en esta revisión: 36 pruebas y 379 aserciones aprobadas).
2. Ruta elegida y documentada: migraciones Laravel; `clinicas_tablas.sql` es el snapshot del esquema, no un segundo instalador.
3. El snapshot ya incorpora cambios posteriores; contrastar todos los objetos de la ruta elegida al instalar en Oracle.
4. **Pendiente:** instalar en Oracle limpio y comprobar compilación de paquetes, funciones y triggers.
5. **Pendiente:** ejecutar `database/oracle/IMAJ_DEMO_SEED.sql` después de las migraciones y validar en Oracle las cantidades, relaciones y sumas de pagos generadas.
6. **Pendiente:** verificar insert, update, delete, COMMIT/ROLLBACK, auditoría, reportes, cursores y errores esperados directamente contra Oracle.
7. **Pendiente:** completar pruebas CRUD de los 16 recursos y los cinco gestores desde la aplicación.
8. **Pendiente:** verificar los tres reportes, sus tablas y los cursores para cada perfil en Oracle.
9. **Pendiente:** probar inicio de sesión, permisos por rol, integridad referencial y paginación con volumen representativo en Oracle.
10. Resolver las brechas restantes de la matriz antes de declarar la entrega lista.
