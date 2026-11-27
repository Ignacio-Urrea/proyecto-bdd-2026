<?php

namespace Tests\Unit;

use App\Models\Examen;
use App\Models\Paciente;
use App\Models\Prestacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OraclePackageContractTest extends TestCase
{
    #[Test]
    public function it_uses_the_existing_password_column_for_hashed_authentication(): void
    {
        $user = new Usuario;

        $this->assertSame('IMAJ_USUARIOS', $user->getTable());
        $this->assertSame('CONTRASEÑA', $user->getAuthPasswordName());
        $this->assertContains('CONTRASEÑA', $user->getFillable());
        $this->assertNotContains('PASSWORD_HASH', $user->getFillable());

        $user->CONTRASEÑA = 'correct-horse-battery-staple';

        $this->assertTrue(Hash::check('correct-horse-battery-staple', $user->getAuthPassword()));
        $this->assertNotSame('correct-horse-battery-staple', $user->getAuthPassword());
    }

    #[Test]
    public function the_oracle_crud_contract_uses_only_the_existing_password_column(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));
        $schema = file_get_contents(base_path('clinicas_tablas.sql'));

        $this->assertStringContainsString(
            "P_COLUMNS := 'NOMBRE_USUARIO, CONTRASEÑA, ROL_ID'",
            $package,
        );
        $this->assertStringContainsString(
            "P_COLUMNS := 'CLINICA_ID, NOMBRE, PRECIO_BASE, HABILITADA'",
            $package,
        );
        $this->assertStringNotContainsString('PASSWORD_HASH', $package);
        $this->assertStringContainsString('CONTRASEÑA VARCHAR2(255)', $schema);
        $this->assertStringContainsString('CLINICA_ID NUMBER NOT NULL', $schema);
        $this->assertStringContainsString('REFERENCES IMAJ_CLINICAS(ID)', $schema);
        $this->assertStringNotContainsString('PASSWORD_HASH', $schema);
    }

    #[Test]
    public function the_oracle_package_exposes_the_five_managers_and_three_cursor_reports(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));

        foreach ([
            'CREATE_ORDER',
            'BUILD_BUDGET',
            'REGISTER_PAYMENT',
            'COMPLETE_EXAM',
            'PUBLISH_REPORT',
            'LIST_ROWS_PAGE',
            'COUNT_ROWS_PAGE',
            'REPORT_ORDERS',
            'REPORT_REVENUE',
            'REPORT_EXAMS',
            'FUNCTION IMAJ_TOTAL_BUDGET',
            'FUNCTION IMAJ_AMOUNT_PAID',
            'FUNCTION IMAJ_PATIENT_NAME',
            'SYS_REFCURSOR',
        ] as $contract) {
            $this->assertStringContainsString($contract, $package);
        }

        $this->assertSame(6, preg_match_all('/PROCEDURE REPORT_(?:ORDERS|REVENUE|EXAMS)\\(P_ALLOWED_IDS IN CLOB, P_CURSOR OUT SYS_REFCURSOR\\)/', $package));
        $this->assertSame(3, substr_count($package, 'JSON_TABLE(P_ALLOWED_IDS'));
        $this->assertStringContainsString('OFFSET :row_offset ROWS FETCH NEXT :row_limit ROWS ONLY', $package);
    }

    #[Test]
    public function the_application_package_uses_business_functions_and_an_explicit_service_cursor(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));

        $this->assertStringContainsString('V_PATIENT_NAME := IMAJ_PATIENT_NAME(P_PATIENT_ID)', $package);
        $this->assertStringContainsString('V_PAID := IMAJ_AMOUNT_PAID(P_BUDGET_ID)', $package);
        $this->assertStringContainsString('FUNCTION IMAJ_TOTAL_BUDGET(P_BUDGET_ID IN NUMBER)', $package);
        $this->assertStringContainsString('CURSOR C_SERVICES(P_CLINIC_ID IN NUMBER)', $package);
        $this->assertStringContainsString('OPEN C_SERVICES(V_CLINICA_ID)', $package);
        $this->assertStringContainsString('FETCH C_SERVICES INTO V_SERVICE_ID, V_SERVICE_PRICE', $package);
        $this->assertStringContainsString('CLOSE C_SERVICES', $package);
        $this->assertStringContainsString('C_SERVICES%NOTFOUND', $package);
    }

    #[Test]
    public function package_mutations_roll_back_to_savepoints_without_committing_the_outer_transaction(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));
        $savepoints = [
            'SP_IMAJ_INSERT_ROW',
            'SP_IMAJ_UPDATE_ROW',
            'SP_IMAJ_DELETE_ROW',
            'SP_IMAJ_CREATE_ORDER',
            'SP_IMAJ_BUILD_BUDGET',
            'SP_IMAJ_REGISTER_PAYMENT',
            'SP_IMAJ_COMPLETE_EXAM',
            'SP_IMAJ_PUBLISH_REPORT',
        ];

        foreach ($savepoints as $savepoint) {
            $this->assertStringContainsString('SAVEPOINT '.$savepoint, $package);
            $this->assertStringContainsString('ROLLBACK TO '.$savepoint, $package);
        }

        $this->assertStringNotContainsString('COMMIT;', $package);
    }

    #[Test]
    public function exams_are_assigned_to_a_radiologist_in_the_oracle_crud_contract(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));

        $this->assertStringContainsString('ORDEN_ID, RADIOLOGO_ID, URGENCIA', $package);
        $this->assertStringContainsString("RADIOLOGO_ID NUMBER PATH '$.RADIOLOGO_ID'", $package);
        $this->assertContains('RADIOLOGO_ID', (new Examen)->getFillable());
    }

    #[Test]
    public function patients_belong_to_a_branch_in_the_oracle_crud_contract(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));
        $patient = new Paciente;

        $this->assertStringContainsString("'SUCURSAL_ID, FECHA_NACIMIENTO, NOMBRE", $package);
        $this->assertContains('SUCURSAL_ID', $patient->getFillable());
        $this->assertNotContains('CLINICA_ID', $patient->getFillable());
    }

    #[Test]
    public function budget_prestations_are_limited_to_the_order_clinic(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_APP_PKG.sql'));
        $prestation = new Prestacion;

        $this->assertStringContainsString('LIST_ORDER_PRESTATIONS', $package);
        $this->assertStringContainsString('P.CLINICA_ID = S.CLINICA_ID', $package);
        $this->assertContains('CLINICA_ID', $prestation->getFillable());
        $this->assertSame('IMAJ_CLINICAS', $prestation->clinica()->getRelated()->getTable());
    }

    #[Test]
    public function budget_details_require_a_unique_budget_and_prestation_pair(): void
    {
        $schema = file_get_contents(base_path('clinicas_tablas.sql'));
        $detailTableStart = strpos($schema, 'CREATE TABLE IMAJ_DETALLE_PRESTACIONES(');
        $detailTable = substr($schema, $detailTableStart);
        $migration = file_get_contents(
            database_path('migrations/2026_11_27_180000_enforce_imaj_budget_prestation_pair.php'),
        );

        $this->assertStringContainsString('PRESUPUESTO_ID NUMBER NOT NULL', $detailTable);
        $this->assertStringContainsString('PRESTACION_ID NUMBER NOT NULL', $detailTable);
        $this->assertStringContainsString(
            'UNIQUE(PRESUPUESTO_ID, PRESTACION_ID)',
            $detailTable,
        );
        $this->assertStringContainsString('havingRaw(\'COUNT(*) > 1\')', $migration);
        $this->assertStringContainsString('UQ_IMAJ_DET_PREST_PAIR', $migration);
    }

    #[Test]
    public function base_schema_includes_the_post_creation_table_changes(): void
    {
        $schema = file_get_contents(base_path('clinicas_tablas.sql'));
        $patientTableStart = strpos($schema, 'CREATE TABLE IMAJ_PACIENTES(');
        $patientTableEnd = strpos($schema, ');', $patientTableStart);
        $patientTable = substr($schema, $patientTableStart, $patientTableEnd - $patientTableStart);

        foreach ([
            'UQ_IMAJ_USUARIOS_NOMBRE UNIQUE(NOMBRE_USUARIO)',
            'RADIOLOGO_ID NUMBER',
            'FK_IMAJ_EXAMENES_RADIOLOGO',
            'IDX_IMAJ_EXAMENES_RADIOLOGO',
            'SUCURSAL_ID NUMBER NOT NULL',
            'IDX_IMAJ_PACIENTES_SUCURSAL',
            'CLINICA_ID NUMBER NOT NULL',
            'IDX_IMAJ_PREST_CLINICA',
            'CREATE TABLE IMAJ_AUDITORIA',
            'FECHA_EVENTO TIMESTAMP WITH TIME ZONE NOT NULL',
        ] as $schemaContract) {
            $this->assertStringContainsString($schemaContract, $schema);
        }

        $this->assertStringNotContainsString('PASSWORD_HASH', $schema);
        $this->assertStringNotContainsString('CLINICA_ID', $patientTable);
    }
}
