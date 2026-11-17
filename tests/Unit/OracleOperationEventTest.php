<?php

namespace Tests\Unit;

use App\Events\OracleOperationCompleted;
use App\Listeners\LogOracleOperation;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OracleOperationEventTest extends TestCase
{
    #[Test]
    public function it_logs_only_operation_metadata_without_record_payloads(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Oracle operation completed', [
                'operation' => 'manager',
                'resource' => 'CREATE_ORDER',
                'actor_id' => 7,
            ]);

        (new LogOracleOperation)->handle(new OracleOperationCompleted('manager', 'CREATE_ORDER', 7));
    }
}
