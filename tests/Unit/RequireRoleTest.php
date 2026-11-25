<?php

namespace Tests\Unit;

use App\Http\Middleware\RequireRole;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RequireRoleTest extends TestCase
{
    #[Test]
    public function it_allows_users_whose_profile_is_on_the_route_allowlist(): void
    {
        $request = Request::create('/examenes');
        $request->setUserResolver(fn () => (object) [
            'rol' => (object) ['NOMBRE_ROL' => 'TECNICO'],
        ]);

        $response = (new RequireRole)->handle(
            $request,
            fn () => new Response('ok'),
            'ADMINISTRADOR',
            'TECNICO',
        );

        $this->assertSame('ok', $response->getContent());
    }

    #[Test]
    public function it_rejects_users_without_an_allowed_profile(): void
    {
        $request = Request::create('/usuarios');
        $request->setUserResolver(fn () => (object) [
            'rol' => (object) ['NOMBRE_ROL' => 'RECEPCIONISTA'],
        ]);

        try {
            (new RequireRole)->handle($request, fn () => new Response('ok'), 'ADMINISTRADOR');
            $this->fail('Se esperaba una respuesta 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
