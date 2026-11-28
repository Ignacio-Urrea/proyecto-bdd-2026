<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OracleInstallScriptTest extends TestCase
{
    #[Test]
    public function the_master_installer_runs_schema_objects_in_dependency_order(): void
    {
        $installer = file_get_contents(database_path('oracle/IMAJ_INSTALL.sql'));
        $steps = [
            '@@..\\..\\clinicas_tablas.sql',
            '@@IMAJ_SEQUENCES.sql',
            '@@IMAJ_FUNCTIONS.sql',
            '@@IMAJ_PROCEDURES.sql',
            '@@IMAJ_CURSORS.sql',
            '@@IMAJ_APP_PKG.sql',
            '@@IMAJ_ACCESS_PKG.sql',
        ];
        $previousPosition = -1;

        foreach ($steps as $step) {
            $position = strpos($installer, $step);

            $this->assertNotFalse($position, "Falta incluir {$step}.");
            $this->assertGreaterThan($previousPosition, $position, "{$step} debe instalarse en orden.");
            $previousPosition = $position;
        }
    }

    #[Test]
    public function the_master_installer_includes_each_individual_trigger_and_checks_the_installation(): void
    {
        $installer = file_get_contents(database_path('oracle/IMAJ_INSTALL.sql'));
        $triggerFiles = glob(database_path('oracle/triggers/*.sql')) ?: [];

        $this->assertCount(34, $triggerFiles);

        foreach ($triggerFiles as $triggerFile) {
            $this->assertStringContainsString(
                '@@triggers\\'.basename($triggerFile),
                $installer,
                basename($triggerFile).' debe quedar incluido en el instalador.',
            );
        }

        $this->assertStringContainsString('WHENEVER SQLERROR EXIT SQL.SQLCODE ROLLBACK', $installer);
        $this->assertStringContainsString('V_EXISTING_OBJECTS', $installer);
        $this->assertStringContainsString("V_TABLES <> 17", $installer);
        $this->assertStringContainsString("V_SUPPORT_TABLES <> 8", $installer);
        $this->assertStringContainsString("V_TRIGGERS <> 34", $installer);
        $this->assertStringContainsString("V_DISABLED_TRIGGERS <> 0", $installer);
        $this->assertStringContainsString("STATUS = 'INVALID'", $installer);
        $this->assertStringNotContainsString('@@IMAJ_TRIGGERS.sql', $installer);
    }

    #[Test]
    public function the_cursor_package_exposes_only_the_three_strategic_reports(): void
    {
        $cursors = file_get_contents(database_path('oracle/IMAJ_CURSORS.sql'));

        $this->assertSame(6, substr_count($cursors, 'PROCEDURE REPORT_'));
        $this->assertStringNotContainsString('REPORT_PAID_BUDGETS', $cursors);
    }
}
