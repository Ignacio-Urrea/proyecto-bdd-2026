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
    public function it_lists_only_services_available_to_the_selected_order(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.LIST_ORDER_PRESTATIONS', [
                'p_order_id' => 42,
                'p_user_id' => null,
            ], ':p_cursor')
            ->andReturn([(object) ['id' => 8, 'nombre' => 'Radiografía de tórax']]);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $options = (new OracleCrudService)->prestationsForOrder(42);

        $this->assertSame([8 => 'Radiografía de tórax'], $options);
    }

    #[Test]
    public function it_normalizes_report_column_names_to_uppercase(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.REPORT_ORDERS', ['p_user_id' => null], ':p_cursor')
            ->andReturn([(object) ['sucursal_id' => 7, 'total_ordenes' => 3]]);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $rows = (new OracleCrudService)->report('REPORT_ORDERS');

        $this->assertSame(7, $rows[0]->SUCURSAL_ID);
        $this->assertSame(3, $rows[0]->TOTAL_ORDENES);
    }

    #[Test]
    public function it_delegates_report_scope_to_the_oracle_access_package(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with(
                'IMAJ_ACCESS_PKG.REPORT_ORDERS',
                ['p_user_id' => null],
                ':p_cursor',
            )
            ->andReturn([]);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $this->assertSame([], (new OracleCrudService)->report('REPORT_ORDERS'));
    }

    #[Test]
    public function it_filters_order_dates_before_paginating(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.LIST_ROWS_PAGE', [
                'p_table' => 'IMAJ_ORDENES',
                'p_date_from' => '2026-11-27',
                'p_date_to' => '2026-11-27',
                'p_user_id' => null,
                'p_offset' => 0,
                'p_limit' => 20,
            ], ':p_cursor')
            ->andReturn([(object) [
                'id' => 21,
                'fecha_cita' => '2026-11-27 00:00:00',
                'total_count' => 1,
            ]]);

        DB::shouldReceive('connection')->once()->andReturn($connection);

        $page = (new OracleCrudService)->paginate(
            'IMAJ_ORDENES',
            dateFrom: '2026-11-27',
            dateTo: '2026-11-27',
        );

        $this->assertSame(1, $page->total());
        $this->assertSame(21, $page->items()[0]->ID);
        $this->assertObjectNotHasProperty('TOTAL_COUNT', $page->items()[0]);
    }

    #[Test]
    public function it_filters_exam_request_dates_before_paginating(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.LIST_ROWS_PAGE', [
                'p_table' => 'IMAJ_EXAMENES',
                'p_date_from' => '2026-11-27',
                'p_date_to' => '2026-11-27',
                'p_user_id' => null,
                'p_offset' => 0,
                'p_limit' => 20,
            ], ':p_cursor')
            ->andReturn([(object) [
                'ID' => 21,
                'FECHA_SOLICITUD' => '2026-11-27 00:00:00',
                'TOTAL_COUNT' => 1,
            ]]);

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
    public function it_counts_matching_rows_when_the_requested_page_is_empty(): void
    {
        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.LIST_ROWS_PAGE', [
                'p_table' => 'IMAJ_PACIENTES',
                'p_date_from' => null,
                'p_date_to' => null,
                'p_user_id' => null,
                'p_offset' => 0,
                'p_limit' => 20,
            ], ':p_cursor')
            ->andReturn([]);
        $connection->shouldReceive('executeProcedureWithCursor')
            ->once()
            ->with('IMAJ_ACCESS_PKG.COUNT_ROWS_PAGE', [
                'p_table' => 'IMAJ_PACIENTES',
                'p_date_from' => null,
                'p_date_to' => null,
                'p_user_id' => null,
            ], ':p_cursor')
            ->andReturn([(object) ['total_count' => 0]]);
        DB::shouldReceive('connection')->once()->andReturn($connection);

        $page = (new OracleCrudService)->paginate('IMAJ_PACIENTES');

        $this->assertSame(0, $page->total());
        $this->assertSame([], $page->items());
    }

    #[Test]
    public function it_rejects_page_sizes_outside_the_supported_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new OracleCrudService)->paginate('IMAJ_ORDENES', 101);
    }

    #[Test]
    public function it_omits_empty_values_from_update_payloads_but_preserves_zero(): void
    {
        Event::fake();

        $connection = Mockery::mock();
        $connection->shouldReceive('executeProcedure')
            ->once()
            ->with('IMAJ_ACCESS_PKG.UPDATE_ROW', [
                'p_table' => 'IMAJ_ORDENES',
                'p_id' => 201,
                'p_payload' => '{"ESTADO":2,"EJECUTADA":0}',
                'p_user_id' => null,
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
