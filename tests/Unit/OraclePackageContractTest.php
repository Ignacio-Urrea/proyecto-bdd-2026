<?php

namespace Tests\Unit;

use App\Models\Examen;
use App\Models\Paciente;
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
        $this->assertStringNotContainsString('PASSWORD_HASH', $package);
        $this->assertStringContainsString('CONTRASEÑA VARCHAR2(255)', $schema);
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
            'REPORT_ORDERS',
            'REPORT_REVENUE',
            'REPORT_EXAMS',
            'FUNCTION TOTAL_BUDGET',
            'SYS_REFCURSOR',
        ] as $contract) {
            $this->assertStringContainsString($contract, $package);
        }
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
}
