<?php

namespace App\Listeners;

use App\Events\OracleOperationCompleted;
use Illuminate\Support\Facades\Log;

class LogOracleOperation
{
    public function handle(OracleOperationCompleted $event): void
    {
        Log::info('Oracle operation completed', [
            'operation' => $event->operation,
            'resource' => $event->resource,
            'actor_id' => $event->actorId,
        ]);
    }
}
