<?php

namespace Tests\Unit;

use App\Services\OracleCrudService;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OracleCrudServiceTest extends TestCase
{
    #[Test]
    public function it_normalizes_report_column_names_to_uppercase(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_APP_PKG.REPORT_ORDERS', [], ':p_cursor')
            ->andReturn([(object) ['sucursal_id' => 7, 'total_ordenes' => 3]]);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $rows = (new OracleCrudService)->report('REPORT_ORDERS');

        $this->assertSame(7, $rows[0]->SUCURSAL_ID);
        $this->assertSame(3, $rows[0]->TOTAL_ORDENES);
    }
}
