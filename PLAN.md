# Plan de trabajo

## Objetivo

Completar y documentar el proyecto de base de datos IMAJ siguiendo los contenidos de las clases de SQL y PL/SQL de Oracle.

La solución debe entregar una aplicación funcional basada en servidor robusto, con:

- Control de acceso mediante autenticación y perfiles diferenciados.
- Al menos 10 mantenedores CRUD.
- Al menos 5 gestores de información.
- Exactamente 3 reportes estratégicos.
- Procedimientos almacenados, funciones, secuencias y cursores utilizados directamente por la lógica de la aplicación.
- Triggers de control y monitoreo.
- Diseño gráfico de la base de datos normalizada como mínimo a tercera forma normal.
- Explicación de la arquitectura cliente-servidor y de la capa de servicios.

## Estado actual

- Existe un modelo de datos con 16 tablas IMAJ y datos administrados mediante Laravel.
- El paquete principal `IMAJ_APP_PKG` contiene CRUD dinámico, gestores de negocio y reportes.
- Existen scripts separados para secuencias, procedimientos, funciones, cursores y triggers.
- Los triggers se separaron en archivos individuales dentro de `database/oracle/triggers`.
- Las migraciones que ya fueron cargadas en la base no deben modificarse.
- El sistema ya supera el mínimo de 10 mantenedores mediante sus recursos CRUD existentes.

## Requerimiento mínimo de entrega

| Componente | Cantidad mínima | Implementación prevista |
| --- | ---: | --- |
| Mantenedores CRUD | 10 | Insert, Select, Update y Delete mediante procedimientos almacenados. |
| Gestores | 5 | Procesos transaccionales que cruzan datos existentes. |
| Reportes | 3 | Consultas estratégicas presentadas como dashboards. |
| Roles de usuario | Más de uno | Acceso diferenciado según perfiles y permisos. |
| Triggers | Según el negocio | Integridad, auditoría, normalización y monitoreo. |

Los módulos CRUD deben invocar directamente a objetos Oracle desde la capa de lógica de negocio. Laravel se limitará a validar entradas, controlar permisos, llamar a los objetos Oracle y presentar resultados.

## Avance actual contrastado con los requerimientos

| Requerimiento | Estado | Avance comprobado | Falta |
| --- | --- | --- | --- |
| Control de acceso | Completo | Inicio/cierre de sesión, hash de contraseñas, rate limiting y permisos por rol. | Evidencia de pruebas de perfiles y permisos. |
| Mantenedores CRUD | Completo | 16 recursos superan el mínimo de 10; cada recurso tiene Index, Create, Update, Delete y View. | Confirmar casos CRUD en la base Oracle para los 16 recursos. |
| Persistencia con objetos Oracle | Completo | `INSERT_ROW`, `UPDATE_ROW`, `DELETE_ROW`, `LIST_ROWS` y `FIND_ROW` están en `IMAJ_APP_PKG`; Laravel los invoca desde `OracleCrudService`. | Ejecutar y documentar una prueba integral en Oracle. |
| Procedimientos almacenados | Completo | Incluye CRUD dinámico y los cinco gestores de negocio. | Validación de ejecución real en Oracle. |
| Funciones | Completo | Total de presupuesto, monto pagado y nombre completo de paciente. | Pruebas Oracle específicas de retorno y excepciones. |
| Secuencias | Completo | 17 secuencias para claves primarias y auditoría. | Probar reinicio o límites y el uso integrado en datos reales. |
| Cursores | Completo | Consultas mediante `SYS_REFCURSOR` para mantenedores y tres reportes. | Incorporar cursores explícitos de procesamiento solo si aparecen reportes adicionales. |
| Gestores de información | Completo | 5 gestores: órdenes, presupuestos, pagos, exámenes e informes. | Completar pruebas de extremo a extremo de los cinco flujos. |
| Reportes | Parcial | 3 reportes implementados: órdenes, ingresos y exámenes. | Presentar los reportes como dashboards visuales, no solo como tablas. |
| Monitoreo con triggers | Completo | 16 triggers de clave primaria, 16 de auditoría y 2 de normalización separados en archivos individuales. | Crear/aplicar migración pendiente y ejecutar pruebas de auditoría y normalización. |
| Diseño gráfico de base de datos | Pendiente | Modelo relacional y tablas existentes. | Agregar diagrama gráfico final exportado al repositorio y explicar relaciones, claves y restricciones. |
| Validación de tercera forma normal | Pendiente | Modelo distribuido en tablas y claves foráneas. | Preparar la explicación formal y evidencia de que no existen dependencias parciales ni transitivas. |
| Declaración de solución y tecnologías | Parcial | Laravel, Oracle XE, OCI8 y arquitectura aparecen en código y README. | Redactar formalmente qué solución entrega el equipo y qué tecnologías utiliza. |
| Arquitectura de capa servidor | Parcial | La aplicación invoca el paquete Oracle y la lógica principal reside en procedimientos y funciones. | Agregar explicación formal del servidor robusto, cliente ligero, flujo de llamadas y responsabilidades. |

### Resumen cuantitativo

- Requerimientos mínimos de cantidad: **16 mantenedores disponibles / mínimo 10**, **5 gestores / mínimo 5**, **3 reportes / mínimo 3**.
- Objetos avanzados:
  - 17 secuencias.
  - 34 archivos individuales de trigger.
  - 10 procedimientos principales en el paquete de aplicación: 5 operaciones CRUD generales y 5 gestores.
  - 3 procedimientos de reporte.
  - 3 funciones independientes adicionales más la función del paquete.
  - 5 operaciones CRUD invocadas desde Laravel.
- Estado global estimado:
  - Completados: 9 elementos.
  - Parciales: 3 elementos.
  - Pendientes de entrega documental o gráfica: 2 elementos.
- La ejecución real en Oracle y las pruebas de integración siguen siendo necesarias para convertir avances de código en avances comprobados.

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
3. Implementar métodos `up` y `down` reversibles.
4. Ejecutar `php artisan migrate` únicamente para aplicar migraciones pendientes.

## Fase 9: Integración con Laravel

1. Mantener las operaciones CRUD a través de `IMAJ_APP_PKG`.
2. Mantener validaciones y permisos en Laravel.
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
2. Implementar creación, lectura, actualización y eliminación mediante `IMAJ_APP_PKG`.
3. Reservar las operaciones DML de escritura para procedimientos almacenados.
4. Obtener identificadores mediante secuencias.
5. Ejecutar consultas mediante cursores o procedimientos que devuelvan `SYS_REFCURSOR`.
6. Validar integridad referencial, restricciones y errores en la base de datos.
7. Registrar operaciones importantes mediante triggers de auditoría.

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
- Presentarse en un dashboard dentro de la aplicación.

## Fase 14: Arquitectura y diseño

1. Documentar la solución seleccionada y las tecnologías utilizadas.
2. Presentar el esquema gráfico de la base de datos.
3. Explicar las relaciones, claves primarias, claves foráneas y restricciones.
4. Mostrar que el modelo final cumple como mínimo tercera forma normal.
5. Explicar la arquitectura cliente-servidor.
6. Demostrar que Laravel actúa como capa delgada y que la lógica pesada reside en Oracle.
7. Documentar las llamadas desde la capa de negocio hacia procedimientos, funciones y cursores.

## Fase 15: Verificación

1. Ejecutar `php artisan test`.
2. Revisar los nombres y dependencias de tablas, secuencias, paquetes y triggers.
3. Ejecutar los scripts Oracle en el orden correcto:
   - tablas;
   - secuencias;
   - funciones;
   - procedimientos;
   - cursores;
   - triggers;
   - paquete `IMAJ_APP_PKG`.
4. Verificar insert, update, delete, rollback, auditoría, reportes y errores esperados.
5. Confirmar al menos 10 mantenedores CRUD y los 5 gestores implementados.
6. Confirmar los 3 reportes y su presentación como dashboards.
7. Confirmar la separación de roles y los permisos de acceso.
8. Resolver los errores observados antes de confirmar el plan como finalizado.
