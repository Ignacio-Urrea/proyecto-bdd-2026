<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\DetallePrestacionController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\ImagenController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'oracle.actor'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::view('/dashboard', 'crud.home')->name('dashboard');

    $resources = [
        'roles' => RolController::class,
        'regiones' => RegionController::class,
        'prestaciones' => PrestacionController::class,
        'usuarios' => UsuarioController::class,
        'ciudades' => CiudadController::class,
        'direcciones' => DireccionController::class,
        'clinicas' => ClinicaController::class,
        'sucursales' => SucursalController::class,
        'pacientes' => PacienteController::class,
        'ordenes' => OrdenController::class,
        'examenes' => ExamenController::class,
        'presupuestos' => PresupuestoController::class,
        'pagos' => PagoController::class,
        'detalle-prestaciones' => DetallePrestacionController::class,
        'informes' => InformeController::class,
        'imagenes' => ImagenController::class,
    ];

    foreach ($resources as $uri => $controller) {
        $allowedRoles = implode(',', config('imaj.permissions.'.$uri, []));

        Route::middleware('role:'.$allowedRoles)->group(function () use ($uri, $controller): void {
            Route::resource($uri, $controller);
            Route::get($uri.'/{id}/delete', [$controller, 'confirmDelete'])
                ->whereNumber('id')
                ->name($uri.'.delete');
        });
    }

    $managers = [
        'ordenes' => ['createOrder', 'storeOrder', 'gestor-ordenes'],
        'presupuestos' => ['createBudget', 'storeBudget', 'gestor-presupuestos'],
        'pagos' => ['createPayment', 'storePayment', 'gestor-pagos'],
        'examenes' => ['createExam', 'storeExam', 'gestor-examenes'],
        'informes' => ['createReport', 'storeReport', 'gestor-informes'],
    ];

    foreach ($managers as $key => [$createMethod, $storeMethod, $permission]) {
        $roles = implode(',', config('imaj.permissions.'.$permission, []));
        $uri = 'gestores/'.$key;

        Route::middleware('role:'.$roles)->group(function () use ($uri, $key, $createMethod, $storeMethod): void {
            Route::get($uri, [ManagerController::class, $createMethod])->name('gestores.'.$key.'.create');
            Route::post($uri, [ManagerController::class, $storeMethod])->name('gestores.'.$key.'.store');
        });
    }

    $reports = [
        'ordenes' => ['orders', 'reporte-ordenes'],
        'ingresos' => ['revenue', 'reporte-ingresos'],
        'examenes' => ['exams', 'reporte-examenes'],
    ];

    foreach ($reports as $key => [$method, $permission]) {
        $roles = implode(',', config('imaj.permissions.'.$permission, []));

        Route::get('/reportes/'.$key, [ReportController::class, $method])
            ->middleware('role:'.$roles)
            ->name('reportes.'.$key);
    }
});
