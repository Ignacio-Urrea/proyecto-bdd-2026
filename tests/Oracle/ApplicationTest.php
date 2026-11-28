<?php

namespace Tests\Oracle;

use App\Models\Usuario;
use App\Services\OracleCrudService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Opt-in tests against an installed, demo-seeded Oracle development schema.
 * Writes (including audit rows) are rolled back; Oracle sequences still advance.
 * No migrations, DDL, seed scripts or standalone committing procedures run here.
 */
class ApplicationTest extends TestCase
{
    private array $fixtures = [];

    private bool $transactionStarted = false;

    private const TABLES = [
        'roles' => 'IMAJ_ROLES', 'regiones' => 'IMAJ_REGIONES',
        'prestaciones' => 'IMAJ_PRESTACIONES', 'usuarios' => 'IMAJ_USUARIOS',
        'ciudades' => 'IMAJ_CIUDADES', 'direcciones' => 'IMAJ_DIRECCIONES',
        'clinicas' => 'IMAJ_CLINICAS', 'sucursales' => 'IMAJ_SUCURSALES',
        'pacientes' => 'IMAJ_PACIENTES', 'ordenes' => 'IMAJ_ORDENES',
        'examenes' => 'IMAJ_EXAMENES', 'presupuestos' => 'IMAJ_PRESUPUESTOS',
        'pagos' => 'IMAJ_PAGOS', 'detalle-prestaciones' => 'IMAJ_DETALLE_PRESTACIONES',
        'informes' => 'IMAJ_INFORMES', 'imagenes' => 'IMAJ_IMAGENES',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('IMAJ_ORACLE_TESTS') !== '1') {
            $this->markTestSkipped('Use phpunit.oracle.xml explicitly with a development Oracle schema.');
        }
        $this->assertSame('oracle', config('database.default'));
        $this->assertSame('array', config('session.driver'));
        DB::beginTransaction();
        $this->transactionStarted = true;
        $this->actingAs($this->userWithRole('SUPERADMIN'));
    }

    protected function tearDown(): void
    {
        try {
            if ($this->transactionStarted) {
                $this->assertSame(1, DB::transactionLevel(), 'A workflow leaked or committed its transaction.');
                DB::rollBack(0);
            }
        } finally {
            parent::tearDown();
        }
    }

    private function userWithRole(string $role): Usuario
    {
        return Usuario::whereIn('ROL_ID', DB::table('IMAJ_ROLES')->where('NOMBRE_ROL', $role)->select('ID'))->firstOrFail();
    }

    private function demoUser(string $name): Usuario
    {
        return Usuario::whereRaw('UPPER(NOMBRE_USUARIO) = ?', [strtoupper($name)])->firstOrFail();
    }

    private function currentId(string $table): int
    {
        return (int) DB::scalar('SELECT SEQ_'.$table.'.CURRVAL FROM DUAL');
    }

    private function fixture(string $resource): int
    {
        if (! isset($this->fixtures[$resource])) {
            $data = $this->payload($resource);
            if ($resource === 'usuarios') {
                $data['CONTRASEÑA'] = Hash::make($data['CONTRASEÑA']);
            }
            app(OracleCrudService::class)->insert(self::TABLES[$resource], $data);
            $this->fixtures[$resource] = $this->currentId(self::TABLES[$resource]);
        }

        return $this->fixtures[$resource];
    }

    private function payload(string $resource): array
    {
        $suffix = bin2hex(random_bytes(4));
        $branch = DB::table('IMAJ_SUCURSALES')->orderBy('ID')->first();
        $branch = array_change_key_case((array) $branch, CASE_UPPER);
        $patient = DB::table('IMAJ_PACIENTES')->where('SUCURSAL_ID', $branch['ID'])->value('ID');

        return match ($resource) {
            'roles' => ['NOMBRE_ROL' => 'QA_'.$suffix],
            'regiones' => ['NOMBRE_REGION' => 'QA_'.$suffix],
            'usuarios' => ['NOMBRE_USUARIO' => 'qa_'.$suffix, 'CONTRASEÑA' => 'Test-only-password-42!', 'ROL_ID' => $this->userWithRole('RADIOLOGO')->ROL_ID],
            'ciudades' => ['REGION_ID' => DB::table('IMAJ_REGIONES')->value('ID'), 'NOMBRE_CIUDAD' => 'QA_'.$suffix],
            'direcciones' => ['CIUDAD_ID' => DB::table('IMAJ_CIUDADES')->value('ID'), 'CALLE' => 'QA_'.$suffix],
            'clinicas' => ['ADMINISTRADOR_ID' => $this->userWithRole('CLINICA_ADMIN')->ID, 'DIRECCION_ID' => DB::table('IMAJ_DIRECCIONES')->value('ID'), 'NOMBRE_CLINICA' => 'QA_'.$suffix, 'CORREO_CLINICA' => 'qa'.$suffix.'@example.test'],
            'sucursales' => ['ADMINISTRADOR_ID' => $this->userWithRole('SUCURSAL_ADMIN')->ID, 'CLINICA_ID' => $branch['CLINICA_ID'], 'DIRECCION_ID' => $branch['DIRECCION_ID'], 'CORREO_SUCURSAL' => 'qa'.$suffix.'@example.test'],
            'prestaciones' => ['CLINICA_ID' => $branch['CLINICA_ID'], 'NOMBRE' => 'QA_'.$suffix, 'PRECIO_BASE' => 12500, 'HABILITADA' => 1],
            'pacientes' => ['SUCURSAL_ID' => $branch['ID'], 'FECHA_NACIMIENTO' => '1990-01-10', 'NOMBRE' => 'QA_'.$suffix, 'APELLIDO_P' => 'Prueba', 'APELLIDO_M' => 'Prueba', 'RUT' => random_int(80000000, 99999999).'-K', 'TELEFONO' => 912345678],
            'ordenes' => ['PACIENTE_ID' => $patient, 'SUCURSAL_ID' => $branch['ID'], 'EJECUTADA' => 0, 'FECHA_CITA' => now()->toDateString(), 'HORA_CITA' => 1100, 'ESTADO' => 0],
            'examenes' => ['ORDEN_ID' => $this->fixture('ordenes'), 'RADIOLOGO_ID' => $this->userWithRole('RADIOLOGO')->ID, 'URGENCIA' => 0, 'FECHA_SOLICITUD' => now()->toDateString(), 'ESTADO' => 0],
            'presupuestos' => ['ESTADO' => 0, 'ORDEN_ID' => $this->fixture('ordenes')],
            'pagos' => ['PRESUPUESTO_ID' => $this->fixture('presupuestos'), 'MONTO' => 100, 'METODO_PAGO' => 'QA'],
            'detalle-prestaciones' => ['PRESUPUESTO_ID' => $this->fixture('presupuestos'), 'PRESTACION_ID' => $this->fixture('prestaciones'), 'PRECIO' => 12500],
            'informes' => ['EXAMEN_ID' => $this->fixture('examenes'), 'TITULO' => 'QA_'.$suffix, 'CUERPO' => 'Contenido de prueba', 'ADENDUM' => 0, 'RUTA' => '/qa/report.pdf'],
            'imagenes' => ['EXAMEN_ID' => $this->fixture('examenes'), 'INFORME_ID' => $this->fixture('informes'), 'NOMBRE' => 'QA_'.$suffix, 'TIPO' => 'PNG', 'TAMANIO' => 1024, 'RUTA' => '/qa/image.png'],
        };
    }

    public static function resources(): iterable
    {
        foreach (array_keys(self::TABLES) as $resource) {
            yield $resource => [$resource];
        }
    }

    #[DataProvider('resources')]
    public function test_every_crud_resource_can_be_created_read_updated_and_deleted(string $resource): void
    {
        $table = self::TABLES[$resource];
        $this->get('/'.$resource)->assertOk();
        $this->get('/'.$resource.'/create')->assertOk();
        $this->postJson('/'.$resource, [])->assertUnprocessable();
        $data = $this->payload($resource);
        $this->postJson('/'.$resource, $data)->assertRedirect('/'.$resource);
        $id = $this->currentId($table);
        $this->assertDatabaseHas($table, ['ID' => $id]);
        foreach (['', '/edit', '/delete'] as $suffix) {
            $this->get('/'.$resource.'/'.$id.$suffix)->assertOk();
        }

        [$field, $value] = match ($resource) {
            'roles' => ['NOMBRE_ROL', 'QA_UPDATED'], 'regiones' => ['NOMBRE_REGION', 'QA_UPDATED'],
            'usuarios' => ['NOMBRE_USUARIO', 'qa_updated'], 'ciudades' => ['NOMBRE_CIUDAD', 'QA_UPDATED'],
            'direcciones' => ['CALLE', 'QA_UPDATED'], 'clinicas' => ['NOMBRE_CLINICA', 'QA_UPDATED'],
            'sucursales' => ['CORREO_SUCURSAL', 'updated@example.test'], 'prestaciones' => ['PRECIO_BASE', 15000],
            'pacientes' => ['NOMBRE', 'QA_UPDATED'], 'ordenes' => ['HORA_CITA', 1200],
            'examenes' => ['URGENCIA', 1], 'presupuestos' => ['ESTADO', 1],
            'pagos' => ['MONTO', 200], 'detalle-prestaciones' => ['PRECIO', 15000],
            'informes' => ['TITULO', 'QA_UPDATED'], 'imagenes' => ['TAMANIO', 2048],
        };
        $data[$field] = $value;
        if ($resource === 'usuarios') {
            $hash = DB::table($table)->where('ID', $id)->value('CONTRASEÑA');
            $this->assertTrue(Hash::check($data['CONTRASEÑA'], $hash));
            $this->get('/usuarios/'.$id)->assertDontSee($hash)->assertDontSee($data['CONTRASEÑA']);
            $data['CONTRASEÑA'] = '';
        }
        $this->putJson('/'.$resource.'/'.$id, $data)->assertRedirect('/'.$resource);
        $this->assertDatabaseHas($table, ['ID' => $id, $field => $resource === 'usuarios' ? strtoupper($value) : $value]);
        if ($resource === 'usuarios') {
            $this->assertSame($hash, DB::table($table)->where('ID', $id)->value('CONTRASEÑA'));
        }
        $this->deleteJson('/'.$resource.'/'.$id)->assertRedirect('/'.$resource);
        $this->assertDatabaseMissing($table, ['ID' => $id]);
        $this->get('/'.$resource.'/'.$id)->assertNotFound();
        foreach (['INSERT', 'UPDATE', 'DELETE'] as $operation) {
            $this->assertDatabaseHas('IMAJ_AUDITORIA', ['TABLA' => $table, 'REGISTRO_ID' => $id, 'OPERACION' => $operation]);
        }
    }

    public static function users(): iterable
    {
        yield 'superadmin' => [null, 'SUPERADMIN'];
        yield 'clinic 1' => ['clinica_admin1', 'CLINICA_ADMIN'];
        yield 'clinic 2' => ['clinica_admin2', 'CLINICA_ADMIN'];
        yield 'branch 1' => ['sucursal_admin1', 'SUCURSAL_ADMIN'];
        yield 'branch 2' => ['sucursal_admin2', 'SUCURSAL_ADMIN'];
        yield 'radiologist' => ['radiologo', 'RADIOLOGO'];
    }

    #[DataProvider('users')]
    public function test_all_module_pages_and_permission_boundaries_for_each_demo_user(?string $username, string $role): void
    {
        $this->actingAs($username === null ? $this->userWithRole($role) : $this->demoUser($username));
        $this->get('/dashboard')->assertOk();
        $allowed = match ($role) {
            'SUPERADMIN' => array_keys(self::TABLES),
            'CLINICA_ADMIN' => ['usuarios', 'clinicas', 'sucursales', 'pacientes', 'ordenes', 'examenes', 'presupuestos', 'pagos', 'detalle-prestaciones', 'informes', 'imagenes'],
            'SUCURSAL_ADMIN' => ['sucursales', 'pacientes', 'ordenes', 'examenes', 'presupuestos', 'pagos', 'detalle-prestaciones', 'informes', 'imagenes'],
            'RADIOLOGO' => ['ordenes', 'examenes', 'informes', 'imagenes'],
        };
        foreach (self::TABLES as $resource => $table) {
            $canAccess = in_array($resource, $allowed, true);
            $this->get('/'.$resource)->assertStatus($canAccess ? 200 : 403);
            $this->get('/'.$resource.'/create')->assertStatus($canAccess ? 200 : 403);
            if (! $canAccess) {
                $this->postJson('/'.$resource, [])->assertForbidden();
                $this->putJson('/'.$resource.'/1', [])->assertForbidden();
                $this->deleteJson('/'.$resource.'/1')->assertForbidden();

                continue;
            }
            $records = app(OracleCrudService::class)->rows($table);
            if ($records !== []) {
                foreach (['', '/edit', '/delete'] as $suffix) {
                    $this->get('/'.$resource.'/'.$records[0]->ID.$suffix)->assertOk();
                }
            }
        }
        foreach (['ordenes', 'presupuestos', 'pagos', 'examenes', 'informes'] as $manager) {
            $canAccess = $role !== 'RADIOLOGO' || in_array($manager, ['examenes', 'informes'], true);
            $this->get('/gestores/'.$manager)->assertStatus($canAccess ? 200 : 403);
            $this->postJson('/gestores/'.$manager, [])->assertStatus($canAccess ? 422 : 403);
        }
        foreach (['ordenes', 'ingresos', 'examenes'] as $report) {
            $this->get('/reportes/'.$report)->assertStatus($role !== 'RADIOLOGO' || $report === 'examenes' ? 200 : 403);
        }
    }

    public static function workflowRoles(): iterable
    {
        foreach (['SUPERADMIN', 'CLINICA_ADMIN', 'SUCURSAL_ADMIN'] as $role) {
            yield $role => [$role];
        }
    }

    #[DataProvider('workflowRoles')]
    public function test_complete_clinical_workflow_and_report_totals(string $role): void
    {
        $serviceId = $this->fixture('prestaciones');
        $this->actingAs($this->userWithRole($role));
        $order = $this->payload('ordenes');
        $this->postJson('/gestores/ordenes', $order)->assertRedirect('/dashboard');
        $orderId = $this->currentId('IMAJ_ORDENES');
        $this->fixtures['ordenes'] = $orderId;
        $this->assertDatabaseHas('IMAJ_ORDENES', ['ID' => $orderId, 'EJECUTADA' => 0]);
        $this->postJson('/gestores/presupuestos', ['ORDEN_ID' => $orderId, 'PRESTACION_IDS' => [$serviceId]])->assertRedirect('/dashboard');
        $budgetId = $this->currentId('IMAJ_PRESUPUESTOS');
        $this->assertDatabaseHas('IMAJ_DETALLE_PRESTACIONES', ['PRESUPUESTO_ID' => $budgetId, 'PRESTACION_ID' => $serviceId, 'PRECIO' => 12500]);
        $revenueBefore = array_sum(array_column(app(OracleCrudService::class)->report('REPORT_REVENUE'), 'INGRESOS'));
        foreach ([5000 => 0, 7500 => 1] as $amount => $state) {
            $this->postJson('/gestores/pagos', ['PRESUPUESTO_ID' => $budgetId, 'MONTO' => $amount, 'METODO_PAGO' => 'QA'])->assertRedirect('/dashboard');
            $this->assertDatabaseHas('IMAJ_PRESUPUESTOS', ['ID' => $budgetId, 'ESTADO' => $state]);
        }
        $this->assertEquals($revenueBefore + 12500, array_sum(array_column(app(OracleCrudService::class)->report('REPORT_REVENUE'), 'INGRESOS')));
        $examId = $this->fixture('examenes');
        $this->postJson('/gestores/examenes', ['EXAMEN_ID' => $examId, 'ESTADO' => 3, 'EJECUTADA' => 1])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('IMAJ_EXAMENES', ['ID' => $examId, 'ESTADO' => 3]);
        $this->assertDatabaseHas('IMAJ_ORDENES', ['ID' => $orderId, 'EJECUTADA' => 1]);
        $this->postJson('/gestores/informes', ['EXAMEN_ID' => $examId, 'TITULO' => 'QA informe', 'CUERPO' => 'Diagnóstico de prueba', 'ADENDUM' => 0])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('IMAJ_INFORMES', ['EXAMEN_ID' => $examId, 'TITULO' => 'QA informe']);
        $oracle = app(OracleCrudService::class);
        $this->assertEquals(count($oracle->rows('IMAJ_ORDENES')), array_sum(array_column($oracle->report('REPORT_ORDERS'), 'TOTAL_ORDENES')));
        $this->assertEquals(count($oracle->rows('IMAJ_EXAMENES')), array_sum(array_column($oracle->report('REPORT_EXAMS'), 'TOTAL_EXAMENES')));
        $actor = DB::table('IMAJ_AUDITORIA')->where('TABLA', 'IMAJ_ORDENES')->where('REGISTRO_ID', $orderId)->where('OPERACION', 'INSERT')->value('USUARIO_BD');
        $this->assertSame(Auth::id().'|'.Auth::user()->NOMBRE_USUARIO, $actor);
        $this->assertNull(DB::scalar("SELECT SYS_CONTEXT('USERENV', 'CLIENT_IDENTIFIER') FROM DUAL"));
    }

    public function test_failed_budget_rolls_back_and_duplicate_budget_is_a_validation_error(): void
    {
        $orderId = $this->fixture('ordenes');
        $serviceId = $this->fixture('prestaciones');
        DB::table('IMAJ_PRESTACIONES')->where('ID', $serviceId)->update(['HABILITADA' => 0]);
        $this->postJson('/gestores/presupuestos', ['ORDEN_ID' => $orderId, 'PRESTACION_IDS' => [$serviceId]])->assertUnprocessable();
        $this->assertDatabaseMissing('IMAJ_PRESUPUESTOS', ['ORDEN_ID' => $orderId]);
        DB::table('IMAJ_PRESTACIONES')->where('ID', $serviceId)->update(['HABILITADA' => 1]);
        $data = ['ORDEN_ID' => $orderId, 'PRESTACION_IDS' => [$serviceId]];
        $this->postJson('/gestores/presupuestos', $data)->assertRedirect('/dashboard');
        $this->postJson('/gestores/presupuestos', $data)->assertUnprocessable();
        $this->assertEquals(1, DB::table('IMAJ_PRESUPUESTOS')->where('ORDEN_ID', $orderId)->count());
    }

    public function test_related_records_cannot_be_deleted_and_invalid_foreign_keys_are_validated(): void
    {
        $exam = $this->fixture('examenes');
        $this->deleteJson('/ordenes/'.$this->fixtures['ordenes'])->assertUnprocessable();
        $this->assertDatabaseHas('IMAJ_EXAMENES', ['ID' => $exam]);
        $this->postJson('/ciudades', ['REGION_ID' => -1, 'NOMBRE_CIUDAD' => 'QA_INVALID'])->assertUnprocessable();
    }

    public function test_clinics_and_branches_cannot_read_or_modify_each_others_records(): void
    {
        $oracle = app(OracleCrudService::class);
        $this->actingAs($this->demoUser('clinica_admin2'));
        $foreignPatient = $oracle->rows('IMAJ_PACIENTES')[0];
        $foreignOrder = $oracle->rows('IMAJ_ORDENES')[0];
        $foreignOrders = array_column($oracle->rows('IMAJ_ORDENES'), 'ID');
        foreach (['clinica_admin1', 'sucursal_admin1'] as $username) {
            $this->actingAs($this->demoUser($username));
            $this->assertSame([], array_intersect($foreignOrders, array_column($oracle->rows('IMAJ_ORDENES'), 'ID')));
            $this->get('/pacientes/'.$foreignPatient->ID)->assertNotFound();
            $this->get('/pacientes/'.$foreignPatient->ID.'/edit')->assertNotFound();
            $this->deleteJson('/pacientes/'.$foreignPatient->ID)->assertForbidden();
            $this->postJson('/gestores/ordenes', ['PACIENTE_ID' => $foreignPatient->ID, 'SUCURSAL_ID' => $foreignPatient->SUCURSAL_ID, 'FECHA_CITA' => now()->toDateString(), 'HORA_CITA' => 1000])->assertForbidden();
            $this->postJson('/gestores/presupuestos', ['ORDEN_ID' => $foreignOrder->ID, 'PRESTACION_IDS' => [1]])->assertForbidden();
            $this->assertEquals(count($oracle->rows('IMAJ_ORDENES')), array_sum(array_column($oracle->report('REPORT_ORDERS'), 'TOTAL_ORDENES')));
            $this->assertEqualsWithDelta(array_sum(array_column($oracle->rows('IMAJ_PAGOS'), 'MONTO')), array_sum(array_column($oracle->report('REPORT_REVENUE'), 'INGRESOS')), 0.005);
        }
    }

    public function test_filters_pagination_and_empty_pages(): void
    {
        $this->fixture('examenes');
        foreach (['ordenes' => 'FECHA_CITA', 'examenes' => 'FECHA_SOLICITUD'] as $resource => $dateField) {
            $table = self::TABLES[$resource];
            $response = $this->get('/'.$resource.'?hoy=1')->assertOk();
            $records = $response->viewData('records');
            $this->assertEquals(DB::table($table)->whereDate($dateField, now()->toDateString())->count(), $records->total());
            $empty = $this->get('/'.$resource.'?desde=1900-01-01&hasta=1900-01-02')->assertOk()->viewData('records');
            $this->assertSame(0, $empty->total());
            $last = $this->get('/'.$resource.'?page=99999')->assertOk()->viewData('records');
            $this->assertCount(0, $last->items());
            $this->assertEquals(DB::table($table)->count(), $last->total());
            $this->getJson('/'.$resource.'?desde=invalid')->assertUnprocessable();
        }
    }

    public function test_login_password_validation_case_insensitivity_logout_and_guest_protection(): void
    {
        $id = $this->fixture('usuarios');
        $user = Usuario::findOrFail($id);
        Auth::logout();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk();
        $credentials = ['NOMBRE_USUARIO' => strtolower($user->NOMBRE_USUARIO), 'password' => 'Test-only-password-42!'];
        $this->postJson('/login', array_replace($credentials, ['password' => 'wrong']))->assertUnprocessable();
        $this->assertGuest();
        $this->post('/login', $credentials)->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/login')->assertRedirect('/dashboard');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_rate_limiting(): void
    {
        Auth::logout();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['NOMBRE_USUARIO' => 'qa_missing_user', 'password' => 'invalid'])->assertUnprocessable();
        }
        $this->postJson('/login', ['NOMBRE_USUARIO' => 'qa_missing_user', 'password' => 'invalid'])
            ->assertUnprocessable()->assertJsonPath('errors.NOMBRE_USUARIO.0', 'Demasiados intentos. Espera un minuto y vuelve a intentar.');
    }

    public function test_multiple_updated_fields_dates_zero_values_and_password_changes_are_saved(): void
    {
        $orderId = $this->fixture('ordenes');
        $data = $this->payload('ordenes');
        $data['FECHA_CITA'] = '2030-06-15';
        $data['HORA_CITA'] = 1500;
        $data['ESTADO'] = 2;
        $this->putJson('/ordenes/'.$orderId, $data)->assertRedirect('/ordenes');
        $this->assertDatabaseHas('IMAJ_ORDENES', ['ID' => $orderId, 'HORA_CITA' => 1500, 'ESTADO' => 2]);
        $this->assertSame('2030-06-15', DB::scalar("SELECT TO_CHAR(FECHA_CITA, 'YYYY-MM-DD') FROM IMAJ_ORDENES WHERE ID = ?", [$orderId]));
        $data['ESTADO'] = 0;
        $this->putJson('/ordenes/'.$orderId, $data)->assertRedirect('/ordenes');
        $this->assertDatabaseHas('IMAJ_ORDENES', ['ID' => $orderId, 'ESTADO' => 0]);

        $userId = $this->fixture('usuarios');
        $user = Usuario::findOrFail($userId);
        $this->putJson('/usuarios/'.$userId, ['NOMBRE_USUARIO' => $user->NOMBRE_USUARIO, 'ROL_ID' => $user->ROL_ID, 'CONTRASEÑA' => 'New-test-password-42!'])->assertRedirect('/usuarios');
        $hash = Usuario::findOrFail($userId)->getAuthPassword();
        $this->assertTrue(Hash::check('New-test-password-42!', $hash));
        $this->assertFalse(Hash::check('Test-only-password-42!', $hash));
    }

    public function test_radiologist_can_complete_assigned_exam_and_publish_but_cannot_delete_it(): void
    {
        $examId = $this->fixture('examenes');
        $this->actingAs($this->userWithRole('RADIOLOGO'));
        $this->postJson('/gestores/examenes', ['EXAMEN_ID' => $examId, 'ESTADO' => 3, 'EJECUTADA' => 1])->assertRedirect('/dashboard');
        $this->postJson('/gestores/informes', ['EXAMEN_ID' => $examId, 'TITULO' => 'QA radiologist', 'CUERPO' => 'Test report', 'ADENDUM' => 1])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('IMAJ_EXAMENES', ['ID' => $examId, 'ESTADO' => 3]);
        $this->assertDatabaseHas('IMAJ_INFORMES', ['EXAMEN_ID' => $examId, 'ADENDUM' => 1]);
        $this->deleteJson('/examenes/'.$examId)->assertForbidden();
        $this->actingAs($this->userWithRole('SUPERADMIN'));
        $otherRadiologist = $this->fixture('usuarios');
        $this->actingAs(Usuario::findOrFail($otherRadiologist));
        $this->get('/examenes/'.$examId)->assertNotFound();
        $this->postJson('/gestores/examenes', ['EXAMEN_ID' => $examId, 'ESTADO' => 0, 'EJECUTADA' => 0])->assertForbidden();
        $this->assertDatabaseHas('IMAJ_EXAMENES', ['ID' => $examId, 'ESTADO' => 3]);
    }

    public function test_dashboard_revenue_is_limited_to_the_current_month(): void
    {
        $paymentId = $this->fixture('pagos');
        DB::update("UPDATE IMAJ_AUDITORIA SET FECHA_EVENTO = ADD_MONTHS(SYSTIMESTAMP, -2) WHERE TABLA = 'IMAJ_PAGOS' AND OPERACION = 'INSERT' AND REGISTRO_ID = ?", [$paymentId]);
        $monthly = collect(app(OracleCrudService::class)->report('REPORT_REVENUE'))->where('PERIODO', now()->format('Y-m'))->sum('INGRESOS');
        $kpis = $this->get('/dashboard')->assertOk()->viewData('kpis');
        $revenue = collect($kpis)->firstWhere('label', 'Ingresos del mes');
        $this->assertSame('$ '.number_format($monthly, 0, ',', '.'), $revenue['value']);
    }
}
