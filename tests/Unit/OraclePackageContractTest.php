<?php

namespace Tests\Unit;

use App\Models\Usuario;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OraclePackageContractTest extends TestCase
{
    #[Test]
    public function it_uses_the_imaj_password_hash_for_authentication(): void
    {
        $user = new Usuario;

        $this->assertSame('IMAJ_USUARIOS', $user->getTable());
        $this->assertSame('PASSWORD_HASH', $user->getAuthPasswordName());
        $this->assertContains('PASSWORD_HASH', $user->getFillable());
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
}
