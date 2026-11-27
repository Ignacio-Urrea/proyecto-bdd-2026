<?php

namespace Tests\Unit;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ImajAccessScope;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ImajAccessScopeTest extends TestCase
{
    #[Test]
    public function superadmin_has_an_unrestricted_scope(): void
    {
        $this->authenticateAs('SUPERADMIN');
        $scope = new ImajAccessScope;

        $this->assertFalse($scope->restricted());
        $this->assertNull($scope->ids('IMAJ_ORDENES'));
    }

    #[Test]
    #[DataProvider('restrictedRoles')]
    public function clinic_branch_and_radiologist_roles_are_restricted(string $role): void
    {
        $this->authenticateAs($role);

        $this->assertTrue((new ImajAccessScope)->restricted());
    }

    #[Test]
    public function a_radiologist_cannot_create_an_unassigned_exam(): void
    {
        $this->authenticateAs('RADIOLOGO');

        try {
            (new ImajAccessScope)->assertCanCreate('IMAJ_EXAMENES', []);
            $this->fail('Se esperaba una respuesta 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    #[Test]
    public function a_radiologist_cannot_create_a_patient(): void
    {
        $this->authenticateAs('RADIOLOGO');

        try {
            (new ImajAccessScope)->assertCanCreate('IMAJ_PACIENTES', ['SUCURSAL_ID' => 3]);
            $this->fail('Se esperaba una respuesta 403.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public static function restrictedRoles(): array
    {
        return [
            'clinic administrator' => ['CLINICA_ADMIN'],
            'branch administrator' => ['SUCURSAL_ADMIN'],
            'radiologist' => ['RADIOLOGO'],
        ];
    }

    private function authenticateAs(string $roleName): void
    {
        $user = new Usuario;
        $user->setAttribute('ID', 7);
        $role = new Rol;
        $role->setAttribute('NOMBRE_ROL', $roleName);
        $user->setRelation('rol', $role);

        Auth::setUser($user);
    }
}
