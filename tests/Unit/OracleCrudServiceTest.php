<?php

namespace Tests\Unit;

use App\Services\OracleCrudService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

    #[Test]
    public function it_filters_order_dates_before_paginating(): void
    {
        $cursorRows = [];
        for ($id = 1; $id <= 20; $id++) {
            $cursorRows[] = (object) ['ID' => $id, 'FECHA_CITA' => '2026-11-26 00:00:00'];
        }
        $cursorRows[] = (object) ['ID' => 21, 'FECHA_CITA' => '2026-11-27 00:00:00'];

        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_APP_PKG.LIST_ROWS', ['p_table' => 'IMAJ_ORDENES'], ':p_cursor')
            ->andReturn($cursorRows);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $page = (new OracleCrudService)->paginate(
            'IMAJ_ORDENES',
            dateFrom: '2026-11-27',
            dateTo: '2026-11-27',
        );

        $this->assertSame(1, $page->total());
        $this->assertSame(21, $page->items()[0]->ID);
    }

    #[Test]
    public function it_filters_exam_request_dates_before_paginating(): void
    {
        $cursorRows = [];
        for ($id = 1; $id <= 20; $id++) {
            $cursorRows[] = (object) ['ID' => $id, 'FECHA_SOLICITUD' => '2026-11-26 00:00:00'];
        }
        $cursorRows[] = (object) ['ID' => 21, 'FECHA_SOLICITUD' => '2026-11-27 00:00:00'];

        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_APP_PKG.LIST_ROWS', ['p_table' => 'IMAJ_EXAMENES'], ':p_cursor')
            ->andReturn($cursorRows);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $page = (new OracleCrudService)->paginate(
            'IMAJ_EXAMENES',
            dateFrom: '2026-11-27',
            dateTo: '2026-11-27',
        );

        $this->assertSame(1, $page->total());
        $this->assertSame(21, $page->items()[0]->ID);
    }

    #[Test]
    public function it_omits_empty_values_from_update_payloads_but_preserves_zero(): void
    {
        Event::fake();

        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedure')
            ->once()
            ->with('IMAJ_APP_PKG.UPDATE_ROW', [
                'p_table' => 'IMAJ_ORDENES',
                'p_id' => 201,
                'p_payload' => '{"ESTADO":2,"EJECUTADA":0}',
            ]);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static fn (callable $operation) => $operation());
        DB::shouldReceive('connection')->once()->andReturn($connection);

        (new OracleCrudService)->update('IMAJ_ORDENES', 201, [
            'ESTADO' => 2,
            'HORA_CITA' => '',
            'SUCURSAL_ID' => null,
            'EJECUTADA' => 0,
        ]);
    }
}
