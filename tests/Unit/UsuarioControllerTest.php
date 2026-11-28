<?php

namespace Tests\Unit;

use App\Http\Controllers\UsuarioController;
use App\Services\OracleCrudService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    #[Test]
    public function imaj_configuration_only_references_existing_roles(): void
    {
        $roles = ['SUPERADMIN', 'CLINICA_ADMIN', 'SUCURSAL_ADMIN', 'RADIOLOGO'];

        $this->assertSame($roles, config('imaj.roles'));
        $this->assertSame(['CLINICA_ADMIN', 'SUCURSAL_ADMIN', 'RADIOLOGO'], config('imaj.assignable_user_roles'));

        foreach (config('imaj.permissions') as $allowedRoles) {
            $this->assertSame([], array_diff($allowedRoles, $roles));
        }
    }

    #[Test]
    public function user_role_options_only_include_clinic_admin_branch_admin_and_radiologist(): void
    {
        $service = Mockery::mock(OracleCrudService::class);
        $service->shouldReceive('rows')
            ->once()
            ->with('IMAJ_ROLES')
            ->andReturn([
                (object) ['ID' => 1, 'NOMBRE_ROL' => 'SUPERADMIN'],
                (object) ['ID' => 2, 'NOMBRE_ROL' => 'ADMINISTRADOR'],
                (object) ['ID' => 3, 'NOMBRE_ROL' => 'CLINICA_ADMIN'],
                (object) ['ID' => 4, 'NOMBRE_ROL' => 'SUCURSAL_ADMIN'],
                (object) ['ID' => 5, 'NOMBRE_ROL' => 'RADIOLOGO'],
            ]);

        $this->app->instance(OracleCrudService::class, $service);

        $this->assertSame([
            3 => 'CLINICA_ADMIN',
            4 => 'SUCURSAL_ADMIN',
            5 => 'RADIOLOGO',
        ], UsuarioController::roleOptions());
    }
}
