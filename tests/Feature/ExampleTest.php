<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_is_sent_to_the_login_page_before_accessing_the_dashboard(): void
    {
        $this->get('/')
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesión');
    }

    public function test_all_imaj_crud_routes_include_detail_and_delete_confirmation_routes(): void
    {
        foreach (config('imaj.modules') as $module) {
            $route = $module['route'];

            $this->assertTrue(Route::has($route.'.index'));
            $this->assertTrue(Route::has($route.'.create'));
            $this->assertTrue(Route::has($route.'.store'));
            $this->assertTrue(Route::has($route.'.show'));
            $this->assertTrue(Route::has($route.'.edit'));
            $this->assertTrue(Route::has($route.'.update'));
            $this->assertTrue(Route::has($route.'.destroy'));
            $this->assertTrue(Route::has($route.'.delete'));
        }
    }

    public function test_each_model_has_its_own_crud_view_files(): void
    {
        $models = [
            'ciudad',
            'clinica',
            'detalle-prestacion',
            'direccion',
            'examen',
            'imagen',
            'informe',
            'orden',
            'paciente',
            'pago',
            'prestacion',
            'presupuesto',
            'region',
            'rol',
            'sucursal',
            'usuario',
        ];

        foreach ($models as $model) {
            foreach (['index', 'create', 'update', 'delete', 'form', 'view'] as $page) {
                $this->assertTrue(View::exists($model.'.'.$page), "Falta la vista {$model}/{$page}.blade.php.");
            }
        }
    }

    public function test_model_create_view_renders_its_form(): void
    {
        $this->withViewErrors([]);

        $html = view('ciudad.create', [
            'title' => 'Ciudades',
            'routeName' => 'ciudades',
            'fields' => [],
            'record' => null,
            'action' => 'Crear',
        ])->render();

        $this->assertStringContainsString('Crear: Ciudades', $html);
        $this->assertStringContainsString('Guardar', $html);
    }
}
