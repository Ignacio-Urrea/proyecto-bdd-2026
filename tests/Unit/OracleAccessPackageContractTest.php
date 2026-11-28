<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OracleAccessPackageContractTest extends TestCase
{
    #[Test]
    public function the_access_package_owns_scoped_reads_and_paginated_queries(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_ACCESS_PKG.sql'));

        foreach ([
            'FUNCTION BUILD_SCOPE_FILTER',
            'FUNCTION CAN_ACCESS',
            'PROCEDURE LIST_ROWS',
            'PROCEDURE FIND_ROW',
            'PROCEDURE LIST_ROWS_PAGE',
            'PROCEDURE COUNT_ROWS_PAGE',
            'PROCEDURE REPORT_ORDERS',
            'PROCEDURE REPORT_REVENUE',
            'PROCEDURE REPORT_EXAMS',
            'CLINICA_ADMIN',
            'SUCURSAL_ADMIN',
            'RADIOLOGO',
        ] as $contract) {
            $this->assertStringContainsString($contract, $package);
        }

        $this->assertStringNotContainsString('P_ALLOWED_IDS', $package);
    }

    #[Test]
    public function every_access_package_mutation_authorizes_before_calling_the_crud_package(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_ACCESS_PKG.sql'));
        $bodyStart = strpos($package, 'CREATE OR REPLACE PACKAGE BODY');

        foreach ([
            'INSERT_ROW' => 'IMAJ_APP_PKG.INSERT_ROW',
            'UPDATE_ROW' => 'IMAJ_APP_PKG.UPDATE_ROW',
            'DELETE_ROW' => 'IMAJ_APP_PKG.DELETE_ROW',
        ] as $operation => $delegate) {
            $wrapperStart = strpos($package, '    PROCEDURE '.$operation.'(', $bodyStart);
            $wrapperEnd = strpos($package, '    END '.$operation.';', $wrapperStart);
            $wrapper = substr($package, $wrapperStart, $wrapperEnd - $wrapperStart);
            $authorizationPosition = strpos($wrapper, 'ASSERT_WRITE_ACCESS');
            $delegatePosition = strpos($wrapper, $delegate);

            $this->assertNotFalse($bodyStart);
            $this->assertNotFalse($wrapperStart);
            $this->assertNotFalse($wrapperEnd);
            $this->assertNotFalse($authorizationPosition);
            $this->assertNotFalse($delegatePosition);
            $this->assertLessThan($delegatePosition, $authorizationPosition, $operation.' debe autorizar primero.');
        }
    }

    #[Test]
    public function managers_and_reports_are_delegated_through_database_scope_services(): void
    {
        $package = file_get_contents(database_path('oracle/IMAJ_ACCESS_PKG.sql'));
        $migration = file_get_contents(
            database_path('migrations/2026_11_27_202400_install_imaj_access_package.php'),
        );

        foreach ([
            'ASSERT_MANAGER_REFERENCE',
            'IMAJ_APP_PKG.CREATE_ORDER',
            'IMAJ_APP_PKG.BUILD_BUDGET',
            'IMAJ_APP_PKG.REGISTER_PAYMENT',
            'IMAJ_APP_PKG.COMPLETE_EXAM',
            'IMAJ_APP_PKG.PUBLISH_REPORT',
            'BUILD_SCOPE_FILTER',
        ] as $contract) {
            $this->assertStringContainsString($contract, $package);
        }

        $this->assertStringContainsString('IMAJ_ACCESS_PKG.sql', $migration);
        $this->assertStringContainsString('IMAJ_APP_PKG.sql', $migration);
    }
}
