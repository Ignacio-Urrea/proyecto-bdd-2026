<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetOracleActor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $actor = $user->getAuthIdentifier().'|'.$user->NOMBRE_USUARIO;
        $connection = DB::connection();
        $connection->executeProcedure('DBMS_SESSION.SET_IDENTIFIER', ['client_id' => $actor]);

        try {
            return $next($request);
        } finally {
            $connection->executeProcedure('DBMS_SESSION.CLEAR_IDENTIFIER');
        }
    }
}
