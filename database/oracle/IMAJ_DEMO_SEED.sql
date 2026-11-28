-- Cargar datos de demostracion despues de ejecutar php artisan migrate.
-- Proporcionar un hash bcrypt generado localmente; nunca pegar la contrasena aqui.
SET ECHO OFF
SET VERIFY OFF
SET SERVEROUTPUT ON
WHENEVER SQLERROR EXIT SQL.SQLCODE ROLLBACK

ACCEPT DEMO_BCRYPT_HASH CHAR PROMPT 'Hash bcrypt para las cuentas de demostracion: ' HIDE

DECLARE
    V_PASSWORD_HASH VARCHAR2(255) := '&DEMO_BCRYPT_HASH';
    V_ID NUMBER;
    V_COUNT NUMBER;
    V_PREVIOUS_CLIENT_ID VARCHAR2(64);
    V_IDENTIFIER_SET BOOLEAN := FALSE;
    V_NEXT_ID NUMBER;
    V_MAX_ID NUMBER;
    V_INCREMENT NUMBER;
    V_USER_ID NUMBER;
    V_REGION_INDEX PLS_INTEGER;
    V_CITY_INDEX PLS_INTEGER;
    V_SERVICE_ID NUMBER;
    V_RADIOLOGO_ID NUMBER;
    V_CLINIC_INDEX PLS_INTEGER;
    V_GLOBAL_ORDER PLS_INTEGER;
    V_PATIENT_INDEX PLS_INTEGER;
    V_SERVICE_INDEX PLS_INTEGER;
    V_DETAIL_COUNT PLS_INTEGER;
    V_PAYMENT_COUNT PLS_INTEGER;
    V_EFFECTIVE_PAYMENT_COUNT PLS_INTEGER;
    V_REPORT_COUNT PLS_INTEGER;
    V_IMAGE_COUNT PLS_INTEGER;
    V_TOTAL NUMBER;
    V_PRICE NUMBER;
    V_AMOUNT NUMBER;
    V_BASE_AMOUNT NUMBER;
    V_REMAINDER NUMBER;
    V_ACTUAL_TOTAL NUMBER;
    V_ROLE_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'SUPERADMIN',
        'CLINICA_ADMIN',
        'SUCURSAL_ADMIN',
        'RADIOLOGO'
    );
    V_REGION_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Region Demo Norte',
        'Region Demo Centro',
        'Region Demo Sur'
    );
    V_CITY_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Arica Demo', 'Putre Demo', 'Camarones Demo',
        'Valparaiso Demo', 'Vina del Mar Demo', 'Quillota Demo',
        'Puerto Montt Demo', 'Osorno Demo', 'Castro Demo'
    );
    V_SERVICE_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Radiografia general',
        'Ecografia abdominal',
        'Tomografia simple',
        'Resonancia simple',
        'Mamografia digital'
    );
    V_FIRST_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Demo Camila', 'Demo Tomas', 'Demo Valentina', 'Demo Diego', 'Demo Francisca',
        'Demo Mateo', 'Demo Isidora', 'Demo Benjamin', 'Demo Fernanda', 'Demo Joaquin'
    );
    V_LAST_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Rivera', 'Torres', 'Fuentes', 'Rojas', 'Vargas',
        'Castro', 'Morales', 'Herrera', 'Navarro', 'Silva'
    );
    V_SECOND_LAST_NAMES SYS.ODCIVARCHAR2LIST := SYS.ODCIVARCHAR2LIST(
        'Soto', 'Mella', 'Paredes', 'Vega', 'Contreras',
        'Araya', 'Reyes', 'Cortes', 'Sepulveda', 'Tapia'
    );
    TYPE T_NUMBER_MAP IS TABLE OF NUMBER INDEX BY PLS_INTEGER;
    L_ROLE_IDS T_NUMBER_MAP;
    L_CLINIC_ADMIN_IDS T_NUMBER_MAP;
    L_BRANCH_ADMIN_IDS T_NUMBER_MAP;
    L_REGION_IDS T_NUMBER_MAP;
    L_CITY_IDS T_NUMBER_MAP;
    L_ADDRESS_IDS T_NUMBER_MAP;
    L_CLINIC_IDS T_NUMBER_MAP;
    L_BRANCH_IDS T_NUMBER_MAP;
    L_PATIENT_IDS T_NUMBER_MAP;
    L_SERVICE_IDS T_NUMBER_MAP;
    L_SERVICE_PRICES T_NUMBER_MAP;
    L_ORDER_IDS T_NUMBER_MAP;
    L_BUDGET_IDS T_NUMBER_MAP;
    L_EXAM_IDS T_NUMBER_MAP;

    PROCEDURE SYNCHRONIZE_SEQUENCE(P_TABLE IN VARCHAR2, P_SEQUENCE IN VARCHAR2) IS
    BEGIN
        EXECUTE IMMEDIATE 'SELECT NVL(MAX(ID), 0) FROM ' || P_TABLE INTO V_MAX_ID;
        EXECUTE IMMEDIATE 'SELECT ' || P_SEQUENCE || '.NEXTVAL FROM DUAL' INTO V_NEXT_ID;

        IF V_NEXT_ID <= V_MAX_ID THEN
            V_INCREMENT := V_MAX_ID + 1 - V_NEXT_ID;
            EXECUTE IMMEDIATE
                'ALTER SEQUENCE ' || P_SEQUENCE || ' INCREMENT BY ' ||
                TO_CHAR(V_INCREMENT, 'FM9999999999999999999999999990');
            BEGIN
                EXECUTE IMMEDIATE 'SELECT ' || P_SEQUENCE || '.NEXTVAL FROM DUAL' INTO V_NEXT_ID;
            EXCEPTION
                WHEN OTHERS THEN
                    EXECUTE IMMEDIATE 'ALTER SEQUENCE ' || P_SEQUENCE || ' INCREMENT BY 1';
                    RAISE;
            END;
            EXECUTE IMMEDIATE 'ALTER SEQUENCE ' || P_SEQUENCE || ' INCREMENT BY 1';
        END IF;
    END SYNCHRONIZE_SEQUENCE;

    PROCEDURE ENSURE_ROLE(P_NAME IN VARCHAR2, P_ROLE_ID OUT NUMBER) IS
    BEGIN
        SELECT COUNT(*), MIN(ID)
        INTO V_COUNT, P_ROLE_ID
        FROM IMAJ_ROLES
        WHERE UPPER(NOMBRE_ROL) = UPPER(P_NAME);

        IF P_ROLE_ID IS NULL THEN
            INSERT INTO IMAJ_ROLES (ID, NOMBRE_ROL)
            VALUES (SEQ_IMAJ_ROLES.NEXTVAL, P_NAME)
            RETURNING ID INTO P_ROLE_ID;
        ELSE
            UPDATE IMAJ_ROLES
            SET NOMBRE_ROL = P_NAME
            WHERE ID = P_ROLE_ID;
        END IF;
    END ENSURE_ROLE;

    PROCEDURE ENSURE_USER(
        P_NAME IN VARCHAR2,
        P_ROLE_ID IN NUMBER,
        P_USER_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT COUNT(*), MIN(ID)
        INTO V_COUNT, P_USER_ID
        FROM IMAJ_USUARIOS
        WHERE UPPER(NOMBRE_USUARIO) = UPPER(P_NAME);

        IF P_USER_ID IS NULL THEN
            INSERT INTO IMAJ_USUARIOS (ID, NOMBRE_USUARIO, CONTRASEÑA, ROL_ID)
            VALUES (SEQ_IMAJ_USUARIOS.NEXTVAL, P_NAME, V_PASSWORD_HASH, P_ROLE_ID)
            RETURNING ID INTO P_USER_ID;
        ELSE
            UPDATE IMAJ_USUARIOS
            SET CONTRASEÑA = V_PASSWORD_HASH,
                ROL_ID = P_ROLE_ID
            WHERE UPPER(NOMBRE_USUARIO) = UPPER(P_NAME);
        END IF;
    END ENSURE_USER;

    PROCEDURE ENSURE_REGION(P_NAME IN VARCHAR2, P_REGION_ID OUT NUMBER) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_REGION_ID
        FROM IMAJ_REGIONES
        WHERE UPPER(NOMBRE_REGION) = UPPER(P_NAME);

        IF P_REGION_ID IS NULL THEN
            INSERT INTO IMAJ_REGIONES (ID, NOMBRE_REGION)
            VALUES (SEQ_IMAJ_REGIONES.NEXTVAL, P_NAME)
            RETURNING ID INTO P_REGION_ID;
        END IF;
    END ENSURE_REGION;

    PROCEDURE ENSURE_CITY(
        P_REGION_ID IN NUMBER,
        P_NAME IN VARCHAR2,
        P_CITY_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_CITY_ID
        FROM IMAJ_CIUDADES
        WHERE UPPER(NOMBRE_CIUDAD) = UPPER(P_NAME);

        IF P_CITY_ID IS NULL THEN
            INSERT INTO IMAJ_CIUDADES (ID, REGION_ID, NOMBRE_CIUDAD)
            VALUES (SEQ_IMAJ_CIUDADES.NEXTVAL, P_REGION_ID, P_NAME)
            RETURNING ID INTO P_CITY_ID;
        ELSE
            UPDATE IMAJ_CIUDADES
            SET REGION_ID = P_REGION_ID
            WHERE ID = P_CITY_ID;
        END IF;
    END ENSURE_CITY;

    PROCEDURE ENSURE_ADDRESS(
        P_CITY_ID IN NUMBER,
        P_STREET IN VARCHAR2,
        P_ADDRESS_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_ADDRESS_ID
        FROM IMAJ_DIRECCIONES
        WHERE CIUDAD_ID = P_CITY_ID
          AND UPPER(CALLE) = UPPER(P_STREET);

        IF P_ADDRESS_ID IS NULL THEN
            INSERT INTO IMAJ_DIRECCIONES (ID, CIUDAD_ID, CALLE)
            VALUES (SEQ_IMAJ_DIRECCIONES.NEXTVAL, P_CITY_ID, P_STREET)
            RETURNING ID INTO P_ADDRESS_ID;
        END IF;
    END ENSURE_ADDRESS;

    PROCEDURE ENSURE_CLINIC(
        P_ADMIN_ID IN NUMBER,
        P_ADDRESS_ID IN NUMBER,
        P_NAME IN VARCHAR2,
        P_EMAIL IN VARCHAR2,
        P_CLINIC_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_CLINIC_ID
        FROM IMAJ_CLINICAS
        WHERE UPPER(NOMBRE_CLINICA) = UPPER(P_NAME);

        IF P_CLINIC_ID IS NULL THEN
            INSERT INTO IMAJ_CLINICAS (
                ID, ADMINISTRADOR_ID, DIRECCION_ID, NOMBRE_CLINICA, CORREO_CLINICA
            )
            VALUES (
                SEQ_IMAJ_CLINICAS.NEXTVAL, P_ADMIN_ID, P_ADDRESS_ID, P_NAME, P_EMAIL
            )
            RETURNING ID INTO P_CLINIC_ID;
        ELSE
            UPDATE IMAJ_CLINICAS
            SET ADMINISTRADOR_ID = P_ADMIN_ID,
                DIRECCION_ID = P_ADDRESS_ID,
                CORREO_CLINICA = P_EMAIL
            WHERE ID = P_CLINIC_ID;
        END IF;
    END ENSURE_CLINIC;

    PROCEDURE ENSURE_BRANCH(
        P_ADMIN_ID IN NUMBER,
        P_CLINIC_ID IN NUMBER,
        P_EMAIL IN VARCHAR2,
        P_ADDRESS_ID IN NUMBER,
        P_BRANCH_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_BRANCH_ID
        FROM IMAJ_SUCURSALES
        WHERE UPPER(CORREO_SUCURSAL) = UPPER(P_EMAIL);

        IF P_BRANCH_ID IS NULL THEN
            INSERT INTO IMAJ_SUCURSALES (
                ID, ADMINISTRADOR_ID, CLINICA_ID, CORREO_SUCURSAL, DIRECCION_ID
            )
            VALUES (
                SEQ_IMAJ_SUCURSALES.NEXTVAL,
                P_ADMIN_ID,
                P_CLINIC_ID,
                P_EMAIL,
                P_ADDRESS_ID
            )
            RETURNING ID INTO P_BRANCH_ID;
        ELSE
            UPDATE IMAJ_SUCURSALES
            SET ADMINISTRADOR_ID = P_ADMIN_ID,
                CLINICA_ID = P_CLINIC_ID,
                DIRECCION_ID = P_ADDRESS_ID
            WHERE ID = P_BRANCH_ID;
        END IF;
    END ENSURE_BRANCH;

    PROCEDURE ENSURE_SERVICE(
        P_CLINIC_ID IN NUMBER,
        P_NAME IN VARCHAR2,
        P_PRICE IN NUMBER,
        P_SERVICE_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_SERVICE_ID
        FROM IMAJ_PRESTACIONES
        WHERE CLINICA_ID = P_CLINIC_ID
          AND UPPER(NOMBRE) = UPPER(P_NAME);

        IF P_SERVICE_ID IS NULL THEN
            INSERT INTO IMAJ_PRESTACIONES (
                ID, CLINICA_ID, NOMBRE, PRECIO_BASE, HABILITADA
            )
            VALUES (
                SEQ_IMAJ_PRESTACIONES.NEXTVAL, P_CLINIC_ID, P_NAME, P_PRICE, 1
            )
            RETURNING ID INTO P_SERVICE_ID;
        ELSE
            UPDATE IMAJ_PRESTACIONES
            SET PRECIO_BASE = P_PRICE,
                HABILITADA = 1
            WHERE ID = P_SERVICE_ID;
        END IF;
    END ENSURE_SERVICE;

    FUNCTION RUT_WITH_CHECK_DIGIT(P_BODY IN NUMBER) RETURN VARCHAR2 IS
        V_BODY_TEXT VARCHAR2(8);
        V_SUM NUMBER := 0;
        V_WEIGHT PLS_INTEGER := 2;
        V_REMAINDER NUMBER;
        V_CHECK_DIGIT VARCHAR2(1);
    BEGIN
        V_BODY_TEXT := TO_CHAR(P_BODY, 'FM00000000');
        FOR DIGIT_INDEX IN REVERSE 1 .. LENGTH(V_BODY_TEXT) LOOP
            V_SUM := V_SUM + TO_NUMBER(SUBSTR(V_BODY_TEXT, DIGIT_INDEX, 1)) * V_WEIGHT;
            V_WEIGHT := V_WEIGHT + 1;
            IF V_WEIGHT > 7 THEN
                V_WEIGHT := 2;
            END IF;
        END LOOP;

        V_REMAINDER := 11 - MOD(V_SUM, 11);
        V_CHECK_DIGIT := CASE V_REMAINDER
            WHEN 11 THEN '0'
            WHEN 10 THEN 'K'
            ELSE TO_CHAR(V_REMAINDER)
        END;
        RETURN V_BODY_TEXT || '-' || V_CHECK_DIGIT;
    END RUT_WITH_CHECK_DIGIT;

    PROCEDURE ENSURE_PATIENT(
        P_BRANCH_ID IN NUMBER,
        P_BIRTH_DATE IN DATE,
        P_NAME IN VARCHAR2,
        P_LAST_NAME IN VARCHAR2,
        P_SECOND_LAST_NAME IN VARCHAR2,
        P_PHONE IN NUMBER,
        P_RUT_BODY IN NUMBER,
        P_PATIENT_ID OUT NUMBER
    ) IS
        V_CANDIDATE_BODY NUMBER := P_RUT_BODY;
        V_CANDIDATE_RUT VARCHAR2(32);
    BEGIN
        SELECT MIN(ID)
        INTO P_PATIENT_ID
        FROM IMAJ_PACIENTES
        WHERE UPPER(NOMBRE) = UPPER(P_NAME)
          AND UPPER(APELLIDO_P) = UPPER(P_LAST_NAME)
          AND UPPER(APELLIDO_M) = UPPER(P_SECOND_LAST_NAME);

        IF P_PATIENT_ID IS NOT NULL THEN
            UPDATE IMAJ_PACIENTES
            SET SUCURSAL_ID = P_BRANCH_ID,
                FECHA_NACIMIENTO = P_BIRTH_DATE,
                TELEFONO = P_PHONE
            WHERE ID = P_PATIENT_ID;
            RETURN;
        END IF;

        LOOP
            IF V_CANDIDATE_BODY > 99999999 THEN
                RAISE_APPLICATION_ERROR(-20108, 'No hay RUT sinteticos disponibles para pacientes demo.');
            END IF;

            V_CANDIDATE_RUT := RUT_WITH_CHECK_DIGIT(V_CANDIDATE_BODY);
            SELECT MIN(ID)
            INTO P_PATIENT_ID
            FROM IMAJ_PACIENTES
            WHERE UPPER(RUT) = UPPER(V_CANDIDATE_RUT);

            IF P_PATIENT_ID IS NULL THEN
                INSERT INTO IMAJ_PACIENTES (
                    ID, SUCURSAL_ID, FECHA_NACIMIENTO, NOMBRE,
                    APELLIDO_P, APELLIDO_M, RUT, TELEFONO
                )
                VALUES (
                    SEQ_IMAJ_PACIENTES.NEXTVAL,
                    P_BRANCH_ID,
                    P_BIRTH_DATE,
                    P_NAME,
                    P_LAST_NAME,
                    P_SECOND_LAST_NAME,
                    V_CANDIDATE_RUT,
                    P_PHONE
                )
                RETURNING ID INTO P_PATIENT_ID;
                EXIT;
            END IF;

            V_CANDIDATE_BODY := V_CANDIDATE_BODY + 1;
        END LOOP;
    END ENSURE_PATIENT;

    PROCEDURE ENSURE_ORDER(
        P_PATIENT_ID IN NUMBER,
        P_BRANCH_ID IN NUMBER,
        P_DATE IN DATE,
        P_HOUR IN NUMBER,
        P_ORDER_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_ORDER_ID
        FROM IMAJ_ORDENES
        WHERE PACIENTE_ID = P_PATIENT_ID
          AND SUCURSAL_ID = P_BRANCH_ID
          AND TRUNC(FECHA_CITA) = TRUNC(P_DATE)
          AND HORA_CITA = P_HOUR;

        IF P_ORDER_ID IS NULL THEN
            INSERT INTO IMAJ_ORDENES (
                ID, PACIENTE_ID, SUCURSAL_ID, EJECUTADA, FECHA_CITA, HORA_CITA, ESTADO
            )
            VALUES (
                SEQ_IMAJ_ORDENES.NEXTVAL,
                P_PATIENT_ID,
                P_BRANCH_ID,
                1,
                P_DATE,
                P_HOUR,
                3
            )
            RETURNING ID INTO P_ORDER_ID;
        ELSE
            UPDATE IMAJ_ORDENES
            SET EJECUTADA = 1,
                ESTADO = 3
            WHERE ID = P_ORDER_ID;
        END IF;
    END ENSURE_ORDER;

    PROCEDURE ENSURE_BUDGET(P_ORDER_ID IN NUMBER, P_BUDGET_ID OUT NUMBER) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_BUDGET_ID
        FROM IMAJ_PRESUPUESTOS
        WHERE ORDEN_ID = P_ORDER_ID;

        IF P_BUDGET_ID IS NULL THEN
            INSERT INTO IMAJ_PRESUPUESTOS (ID, ESTADO, ORDEN_ID)
            VALUES (SEQ_IMAJ_PRESUPUESTOS.NEXTVAL, 0, P_ORDER_ID)
            RETURNING ID INTO P_BUDGET_ID;
        END IF;
    END ENSURE_BUDGET;

    PROCEDURE ENSURE_DETAIL(
        P_BUDGET_ID IN NUMBER,
        P_SERVICE_ID IN NUMBER,
        P_PRICE IN NUMBER
    ) IS
        V_DETAIL_ID NUMBER;
    BEGIN
        SELECT MIN(ID)
        INTO V_DETAIL_ID
        FROM IMAJ_DETALLE_PRESTACIONES
        WHERE PRESUPUESTO_ID = P_BUDGET_ID
          AND PRESTACION_ID = P_SERVICE_ID;

        IF V_DETAIL_ID IS NULL THEN
            INSERT INTO IMAJ_DETALLE_PRESTACIONES (
                ID, PRESUPUESTO_ID, PRESTACION_ID, PRECIO
            )
            VALUES (
                SEQ_IMAJ_DETALLE_PRESTACIONES.NEXTVAL,
                P_BUDGET_ID,
                P_SERVICE_ID,
                P_PRICE
            );
        ELSE
            UPDATE IMAJ_DETALLE_PRESTACIONES
            SET PRECIO = P_PRICE
            WHERE ID = V_DETAIL_ID;
        END IF;
    END ENSURE_DETAIL;

    PROCEDURE ENSURE_PAYMENT(
        P_BUDGET_ID IN NUMBER,
        P_AMOUNT IN NUMBER,
        P_METHOD IN VARCHAR2
    ) IS
        V_PAYMENT_ID NUMBER;
    BEGIN
        SELECT MIN(ID)
        INTO V_PAYMENT_ID
        FROM IMAJ_PAGOS
        WHERE PRESUPUESTO_ID = P_BUDGET_ID
          AND METODO_PAGO = P_METHOD;

        IF V_PAYMENT_ID IS NULL THEN
            INSERT INTO IMAJ_PAGOS (ID, PRESUPUESTO_ID, MONTO, METODO_PAGO)
            VALUES (SEQ_IMAJ_PAGOS.NEXTVAL, P_BUDGET_ID, P_AMOUNT, P_METHOD);
        ELSE
            UPDATE IMAJ_PAGOS
            SET MONTO = P_AMOUNT
            WHERE PRESUPUESTO_ID = P_BUDGET_ID
              AND METODO_PAGO = P_METHOD;
        END IF;
    END ENSURE_PAYMENT;

    PROCEDURE ENSURE_EXAM(
        P_ORDER_ID IN NUMBER,
        P_RADIOLOGO_ID IN NUMBER,
        P_URGENCY IN NUMBER,
        P_REQUEST_DATE IN DATE,
        P_EXAM_ID OUT NUMBER
    ) IS
    BEGIN
        SELECT MIN(ID)
        INTO P_EXAM_ID
        FROM IMAJ_EXAMENES
        WHERE ORDEN_ID = P_ORDER_ID;

        IF P_EXAM_ID IS NULL THEN
            INSERT INTO IMAJ_EXAMENES (
                ID, ORDEN_ID, RADIOLOGO_ID, URGENCIA, FECHA_SOLICITUD, ESTADO
            )
            VALUES (
                SEQ_IMAJ_EXAMENES.NEXTVAL,
                P_ORDER_ID,
                P_RADIOLOGO_ID,
                P_URGENCY,
                P_REQUEST_DATE,
                3
            )
            RETURNING ID INTO P_EXAM_ID;
        ELSE
            UPDATE IMAJ_EXAMENES
            SET RADIOLOGO_ID = P_RADIOLOGO_ID,
                URGENCIA = P_URGENCY,
                FECHA_SOLICITUD = P_REQUEST_DATE,
                ESTADO = 3
            WHERE ID = P_EXAM_ID;
        END IF;
    END ENSURE_EXAM;

    PROCEDURE ENSURE_REPORT(
        P_EXAM_ID IN NUMBER,
        P_TITLE IN VARCHAR2,
        P_BODY IN VARCHAR2,
        P_ADDENDUM IN NUMBER,
        P_PATH IN VARCHAR2
    ) IS
        V_REPORT_ID NUMBER;
    BEGIN
        SELECT MIN(ID)
        INTO V_REPORT_ID
        FROM IMAJ_INFORMES
        WHERE EXAMEN_ID = P_EXAM_ID
          AND UPPER(TITULO) = UPPER(P_TITLE)
          AND ADENDUM = P_ADDENDUM;

        IF V_REPORT_ID IS NULL THEN
            INSERT INTO IMAJ_INFORMES (
                ID, CUERPO, ADENDUM, TITULO, RUTA, EXAMEN_ID
            )
            VALUES (
                SEQ_IMAJ_INFORMES.NEXTVAL,
                P_BODY,
                P_ADDENDUM,
                P_TITLE,
                P_PATH,
                P_EXAM_ID
            );
        ELSE
            UPDATE IMAJ_INFORMES
            SET CUERPO = P_BODY,
                RUTA = P_PATH
            WHERE ID = V_REPORT_ID;
        END IF;
    END ENSURE_REPORT;

    PROCEDURE ENSURE_IMAGE(
        P_EXAM_ID IN NUMBER,
        P_SIZE IN NUMBER,
        P_NAME IN VARCHAR2,
        P_TYPE IN VARCHAR2,
        P_PATH IN VARCHAR2
    ) IS
        V_IMAGE_ID NUMBER;
    BEGIN
        SELECT MIN(ID)
        INTO V_IMAGE_ID
        FROM IMAJ_IMAGENES
        WHERE EXAMEN_ID = P_EXAM_ID
          AND UPPER(NOMBRE) = UPPER(P_NAME);

        IF V_IMAGE_ID IS NULL THEN
            INSERT INTO IMAJ_IMAGENES (
                ID, TAMANIO, NOMBRE, TIPO, RUTA, EXAMEN_ID, INFORME_ID
            )
            VALUES (
                SEQ_IMAJ_IMAGENES.NEXTVAL,
                P_SIZE,
                P_NAME,
                P_TYPE,
                P_PATH,
                P_EXAM_ID,
                NULL
            );
        ELSE
            UPDATE IMAJ_IMAGENES
            SET TAMANIO = P_SIZE,
                TIPO = P_TYPE,
                RUTA = P_PATH,
                INFORME_ID = NULL
            WHERE ID = V_IMAGE_ID;
        END IF;
    END ENSURE_IMAGE;
BEGIN
    IF V_PASSWORD_HASH IS NULL
       OR LENGTH(V_PASSWORD_HASH) <> 60
       OR SUBSTR(V_PASSWORD_HASH, 1, 4) NOT IN ('$2a$', '$2b$', '$2y$')
       OR NOT REGEXP_LIKE(SUBSTR(V_PASSWORD_HASH, 5, 2), '^[0-9]{2}$')
       OR SUBSTR(V_PASSWORD_HASH, 7, 1) <> '$'
       OR NOT REGEXP_LIKE(SUBSTR(V_PASSWORD_HASH, 8), '^[./A-Za-z0-9]{53}$') THEN
        RAISE_APPLICATION_ERROR(-20100, 'Se requiere un hash bcrypt valido de 60 caracteres.');
    END IF;

    V_PREVIOUS_CLIENT_ID := SYS_CONTEXT('USERENV', 'CLIENT_IDENTIFIER');
    DBMS_SESSION.SET_IDENTIFIER('IMAJ_DEMO_SEED');
    V_IDENTIFIER_SET := TRUE;

    SYNCHRONIZE_SEQUENCE('IMAJ_ROLES', 'SEQ_IMAJ_ROLES');
    SYNCHRONIZE_SEQUENCE('IMAJ_USUARIOS', 'SEQ_IMAJ_USUARIOS');
    SYNCHRONIZE_SEQUENCE('IMAJ_REGIONES', 'SEQ_IMAJ_REGIONES');
    SYNCHRONIZE_SEQUENCE('IMAJ_CIUDADES', 'SEQ_IMAJ_CIUDADES');
    SYNCHRONIZE_SEQUENCE('IMAJ_DIRECCIONES', 'SEQ_IMAJ_DIRECCIONES');
    SYNCHRONIZE_SEQUENCE('IMAJ_CLINICAS', 'SEQ_IMAJ_CLINICAS');
    SYNCHRONIZE_SEQUENCE('IMAJ_SUCURSALES', 'SEQ_IMAJ_SUCURSALES');
    SYNCHRONIZE_SEQUENCE('IMAJ_PACIENTES', 'SEQ_IMAJ_PACIENTES');
    SYNCHRONIZE_SEQUENCE('IMAJ_PRESTACIONES', 'SEQ_IMAJ_PRESTACIONES');
    SYNCHRONIZE_SEQUENCE('IMAJ_ORDENES', 'SEQ_IMAJ_ORDENES');
    SYNCHRONIZE_SEQUENCE('IMAJ_PRESUPUESTOS', 'SEQ_IMAJ_PRESUPUESTOS');
    SYNCHRONIZE_SEQUENCE('IMAJ_DETALLE_PRESTACIONES', 'SEQ_IMAJ_DETALLE_PRESTACIONES');
    SYNCHRONIZE_SEQUENCE('IMAJ_PAGOS', 'SEQ_IMAJ_PAGOS');
    SYNCHRONIZE_SEQUENCE('IMAJ_EXAMENES', 'SEQ_IMAJ_EXAMENES');
    SYNCHRONIZE_SEQUENCE('IMAJ_INFORMES', 'SEQ_IMAJ_INFORMES');
    SYNCHRONIZE_SEQUENCE('IMAJ_IMAGENES', 'SEQ_IMAJ_IMAGENES');
    SYNCHRONIZE_SEQUENCE('IMAJ_AUDITORIA', 'SEQ_IMAJ_AUDITORIA');

    FOR I IN 1 .. V_ROLE_NAMES.COUNT LOOP
        ENSURE_ROLE(V_ROLE_NAMES(I), L_ROLE_IDS(I));
    END LOOP;

    ENSURE_USER('superadmin', L_ROLE_IDS(1), V_USER_ID);
    ENSURE_USER('clinica_admin1', L_ROLE_IDS(2), L_CLINIC_ADMIN_IDS(1));
    ENSURE_USER('clinica_admin2', L_ROLE_IDS(2), L_CLINIC_ADMIN_IDS(2));
    ENSURE_USER('sucursal_admin1', L_ROLE_IDS(3), L_BRANCH_ADMIN_IDS(1));
    ENSURE_USER('sucursal_admin2', L_ROLE_IDS(3), L_BRANCH_ADMIN_IDS(2));
    ENSURE_USER('radiologo', L_ROLE_IDS(4), V_RADIOLOGO_ID);

    DELETE FROM IMAJ_ROLES
    WHERE UPPER(NOMBRE_ROL) IN ('ADMINISTRADOR', 'RECEPCIONISTA', 'TECNICO', 'FINANZAS')
    AND NOT EXISTS (
        SELECT 1
        FROM IMAJ_USUARIOS U
        WHERE U.ROL_ID = IMAJ_ROLES.ID
    );

    FOR I IN 1 .. V_REGION_NAMES.COUNT LOOP
        ENSURE_REGION(V_REGION_NAMES(I), L_REGION_IDS(I));
    END LOOP;

    FOR I IN 1 .. V_CITY_NAMES.COUNT LOOP
        V_REGION_INDEX := TRUNC((I - 1) / 3) + 1;
        ENSURE_CITY(L_REGION_IDS(V_REGION_INDEX), V_CITY_NAMES(I), L_CITY_IDS(I));
    END LOOP;

    FOR REGION_INDEX IN 1 .. 3 LOOP
        V_REGION_INDEX := REGION_INDEX;
        FOR CITY_INDEX IN 1 .. 3 LOOP
            V_CITY_INDEX := (REGION_INDEX - 1) * 3 + CITY_INDEX;
            FOR ADDRESS_INDEX IN 1 .. 3 LOOP
                V_ID := (V_REGION_INDEX - 1) * 9 + (V_CITY_INDEX - 1) * 3 + ADDRESS_INDEX;
                ENSURE_ADDRESS(
                    L_CITY_IDS(V_CITY_INDEX),
                    CASE ADDRESS_INDEX
                        WHEN 1 THEN 'Calle Demo 1'
                        WHEN 2 THEN 'Avenida Demo 2'
                        ELSE 'Pasaje Demo 3'
                    END,
                    L_ADDRESS_IDS(V_ID)
                );
            END LOOP;
        END LOOP;
    END LOOP;

    ENSURE_CLINIC(
        L_CLINIC_ADMIN_IDS(1),
        L_ADDRESS_IDS(1),
        'Clinica Demo Norte',
        'clinica.norte@imaj.cl',
        L_CLINIC_IDS(1)
    );

    ENSURE_CLINIC(
        L_CLINIC_ADMIN_IDS(2),
        L_ADDRESS_IDS(10),
        'Clinica Demo Sur',
        'clinica.sur@imaj.cl',
        L_CLINIC_IDS(2)
    );

    ENSURE_BRANCH(
        L_BRANCH_ADMIN_IDS(1),
        L_CLINIC_IDS(1),
        'sucursal.norte@imaj.cl',
        L_ADDRESS_IDS(4),
        L_BRANCH_IDS(1)
    );

    ENSURE_BRANCH(
        L_BRANCH_ADMIN_IDS(2),
        L_CLINIC_IDS(2),
        'sucursal.sur@imaj.cl',
        L_ADDRESS_IDS(13),
        L_BRANCH_IDS(2)
    );

    FOR CLINIC_INDEX IN 1 .. 2 LOOP
        FOR I IN 1 .. 5 LOOP
            IF CLINIC_INDEX = 1 THEN
                V_PRICE := CASE I
                    WHEN 1 THEN 32000
                    WHEN 2 THEN 28000
                    WHEN 3 THEN 85000
                    WHEN 4 THEN 135000
                    ELSE 48000
                END;
            ELSE
                V_PRICE := CASE I
                    WHEN 1 THEN 30000
                    WHEN 2 THEN 26000
                    WHEN 3 THEN 80000
                    WHEN 4 THEN 130000
                    ELSE 45000
                END;
            END IF;

            ENSURE_SERVICE(
                L_CLINIC_IDS(CLINIC_INDEX),
                V_SERVICE_NAMES(I),
                V_PRICE,
                L_SERVICE_IDS((CLINIC_INDEX - 1) * 5 + I)
            );
            L_SERVICE_PRICES((CLINIC_INDEX - 1) * 5 + I) := V_PRICE;
        END LOOP;
    END LOOP;

    FOR I IN 1 .. 10 LOOP
        IF I <= 5 THEN
            V_CLINIC_INDEX := 1;
        ELSE
            V_CLINIC_INDEX := 2;
        END IF;

        V_PATIENT_INDEX := I;
        ENSURE_PATIENT(
            L_BRANCH_IDS(V_CLINIC_INDEX),
            DATE '1980-01-15' + (I - 1) * 365,
            V_FIRST_NAMES(I),
            V_LAST_NAMES(I),
            V_SECOND_LAST_NAMES(I),
            56912345670 + I,
            99000000 + I,
            L_PATIENT_IDS(V_PATIENT_INDEX)
        );
    END LOOP;

    FOR CLINIC_INDEX IN 1 .. 2 LOOP
        FOR LOCAL_ORDER IN 1 .. 10 LOOP
            V_GLOBAL_ORDER := (CLINIC_INDEX - 1) * 10 + LOCAL_ORDER;
            V_PATIENT_INDEX := (CLINIC_INDEX - 1) * 5 + TRUNC((LOCAL_ORDER - 1) / 2) + 1;
            V_DETAIL_COUNT := MOD(LOCAL_ORDER - 1, 5) + 1;
            V_PAYMENT_COUNT := V_DETAIL_COUNT;
            ENSURE_ORDER(
                L_PATIENT_IDS(V_PATIENT_INDEX),
                L_BRANCH_IDS(CLINIC_INDEX),
                DATE '2026-11-10' + TRUNC((LOCAL_ORDER - 1) / 2),
                9 + MOD(LOCAL_ORDER - 1, 2) * 2,
                L_ORDER_IDS(V_GLOBAL_ORDER)
            );

            ENSURE_BUDGET(L_ORDER_IDS(V_GLOBAL_ORDER), L_BUDGET_IDS(V_GLOBAL_ORDER));

            FOR DETAIL_INDEX IN 1 .. V_DETAIL_COUNT LOOP
                V_SERVICE_INDEX := MOD(LOCAL_ORDER + DETAIL_INDEX - 2, 5) + 1;
                V_SERVICE_ID := L_SERVICE_IDS((CLINIC_INDEX - 1) * 5 + V_SERVICE_INDEX);
                V_PRICE := L_SERVICE_PRICES((CLINIC_INDEX - 1) * 5 + V_SERVICE_INDEX);
                ENSURE_DETAIL(
                    L_BUDGET_IDS(V_GLOBAL_ORDER),
                    V_SERVICE_ID,
                    V_PRICE
                );
            END LOOP;

            SELECT NVL(SUM(PRECIO), 0)
            INTO V_TOTAL
            FROM IMAJ_DETALLE_PRESTACIONES
            WHERE PRESUPUESTO_ID = L_BUDGET_IDS(V_GLOBAL_ORDER);

            SELECT NVL(SUM(MONTO), 0)
            INTO V_ACTUAL_TOTAL
            FROM IMAJ_PAGOS
            WHERE PRESUPUESTO_ID = L_BUDGET_IDS(V_GLOBAL_ORDER)
              AND (METODO_PAGO IS NULL OR METODO_PAGO NOT LIKE 'Demo pago %');

            IF V_ACTUAL_TOTAL < V_TOTAL THEN
                V_REMAINDER := V_TOTAL - V_ACTUAL_TOTAL;
                V_EFFECTIVE_PAYMENT_COUNT := LEAST(V_PAYMENT_COUNT, CEIL(V_REMAINDER));
                V_BASE_AMOUNT := V_REMAINDER / V_EFFECTIVE_PAYMENT_COUNT;

                FOR PAYMENT_INDEX IN 1 .. V_EFFECTIVE_PAYMENT_COUNT LOOP
                    IF PAYMENT_INDEX = V_EFFECTIVE_PAYMENT_COUNT THEN
                        V_AMOUNT := V_REMAINDER -
                            V_BASE_AMOUNT * (V_EFFECTIVE_PAYMENT_COUNT - 1);
                    ELSE
                        V_AMOUNT := V_BASE_AMOUNT;
                    END IF;
                    ENSURE_PAYMENT(
                        L_BUDGET_IDS(V_GLOBAL_ORDER),
                        V_AMOUNT,
                        'Demo pago ' || TO_CHAR(PAYMENT_INDEX)
                    );
                END LOOP;
            END IF;

            SELECT NVL(SUM(MONTO), 0)
            INTO V_ACTUAL_TOTAL
            FROM IMAJ_PAGOS
            WHERE PRESUPUESTO_ID = L_BUDGET_IDS(V_GLOBAL_ORDER);

            UPDATE IMAJ_PRESUPUESTOS
            SET ESTADO = CASE WHEN V_ACTUAL_TOTAL >= V_TOTAL THEN 1 ELSE 0 END
            WHERE ID = L_BUDGET_IDS(V_GLOBAL_ORDER);

            ENSURE_EXAM(
                L_ORDER_IDS(V_GLOBAL_ORDER),
                V_RADIOLOGO_ID,
                MOD(LOCAL_ORDER - 1, 3) + 1,
                DATE '2026-11-10' + TRUNC((LOCAL_ORDER - 1) / 2),
                L_EXAM_IDS(V_GLOBAL_ORDER)
            );

            V_REPORT_COUNT := MOD(LOCAL_ORDER - 1, 2) + 1;
            FOR REPORT_INDEX IN 1 .. V_REPORT_COUNT LOOP
                ENSURE_REPORT(
                    L_EXAM_IDS(V_GLOBAL_ORDER),
                    CASE WHEN REPORT_INDEX = 1 THEN 'Informe demo ' ELSE 'Adendum demo ' END ||
                        TO_CHAR(V_GLOBAL_ORDER, 'FM00'),
                    'Informe sintetico de demostracion para la orden ' ||
                        TO_CHAR(V_GLOBAL_ORDER, 'FM00') || '.',
                    CASE WHEN REPORT_INDEX = 1 THEN 0 ELSE 1 END,
                    'demo/informes/orden_' || TO_CHAR(V_GLOBAL_ORDER, 'FM00') ||
                        CASE WHEN REPORT_INDEX = 1 THEN '.txt' ELSE '_adendum.txt' END
                );
            END LOOP;

            V_IMAGE_COUNT := MOD(LOCAL_ORDER - 1, 3) + 1;
            FOR IMAGE_INDEX IN 1 .. V_IMAGE_COUNT LOOP
                ENSURE_IMAGE(
                    L_EXAM_IDS(V_GLOBAL_ORDER),
                    500000 + IMAGE_INDEX * 1000,
                    'orden_' || TO_CHAR(V_GLOBAL_ORDER, 'FM00') ||
                        '_imagen_' || TO_CHAR(IMAGE_INDEX) || '.dcm',
                    'DICOM',
                    'demo/imagenes/orden_' || TO_CHAR(V_GLOBAL_ORDER, 'FM00') ||
                        '/imagen_' || TO_CHAR(IMAGE_INDEX) || '.dcm'
                );
            END LOOP;

        END LOOP;
    END LOOP;

    COMMIT;
    IF V_PREVIOUS_CLIENT_ID IS NULL THEN
        DBMS_SESSION.CLEAR_IDENTIFIER;
    ELSE
        DBMS_SESSION.SET_IDENTIFIER(V_PREVIOUS_CLIENT_ID);
    END IF;
    V_IDENTIFIER_SET := FALSE;
    DBMS_OUTPUT.PUT_LINE('Seed de demostracion sincronizado; los datos existentes ajenos a sus claves se conservaron.');
    DBMS_OUTPUT.PUT_LINE('En una base vacia genera 4 perfiles, 6 cuentas, 3 regiones, 9 ciudades y 27 direcciones.');
    DBMS_OUTPUT.PUT_LINE('Tambien genera 2 clinicas/sucursales, 10 pacientes/prestaciones y 20 ordenes/presupuestos/examenes.');
    DBMS_OUTPUT.PUT_LINE('En una base vacia incluye 60 detalles, 60 pagos, 30 informes y 38 imagenes.');
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        IF V_IDENTIFIER_SET THEN
            IF V_PREVIOUS_CLIENT_ID IS NULL THEN
                DBMS_SESSION.CLEAR_IDENTIFIER;
            ELSE
                DBMS_SESSION.SET_IDENTIFIER(V_PREVIOUS_CLIENT_ID);
            END IF;
        END IF;
        RAISE;
END;
/

UNDEFINE DEMO_BCRYPT_HASH
