<?php

namespace Tests\Unit;

use App\Services\ManagerWorkflowService;
use App\Services\OracleCrudService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManagerWorkflowServiceTest extends TestCase
{
    #[Test]
    public function it_encodes_selected_services_before_calling_the_budget_procedure(): void
    {
        $oracle = Mockery::mock(OracleCrudService::class);
        $oracle->shouldReceive('runManager')
            ->once()
            ->with('BUILD_BUDGET', [
                'p_order_id' => 42,
                'p_services' => '[3,8]',
            ]);

        (new ManagerWorkflowService($oracle))->buildBudget([
            'ORDEN_ID' => 42,
            'PRESTACION_IDS' => ['3', '8'],
        ]);
    }
}
