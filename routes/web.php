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
use App\Services\OracleCrudService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'oracle.actor'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', function () {
        $hoy = now()->toDateString();
        $oracle = app(OracleCrudService::class);
        $orderIds = array_column($oracle->rows('IMAJ_ORDENES'), 'ID');
        $examIds = array_column($oracle->rows('IMAJ_EXAMENES'), 'ID');
        $paymentIds = array_column($oracle->rows('IMAJ_PAGOS'), 'ID');

        $ordenesHoy = DB::table('IMAJ_ORDENES')
            ->when($orderIds !== null, fn ($query) => $query->whereIn('ID', $orderIds))
            ->whereDate('FECHA_CITA', $hoy)
            ->count();

        $pendientes = DB::table('IMAJ_ORDENES')
            ->when($orderIds !== null, fn ($query) => $query->whereIn('ID', $orderIds))
            ->whereDate('FECHA_CITA', $hoy)
            ->where('EJECUTADA', 0)
            ->count();

        $ejecutadas = DB::table('IMAJ_ORDENES')
            ->when($orderIds !== null, fn ($query) => $query->whereIn('ID', $orderIds))
            ->whereDate('FECHA_CITA', $hoy)
            ->where('EJECUTADA', 1)
            ->count();

        $ingresosMes = DB::table('IMAJ_PAGOS')
            ->when($paymentIds !== null, fn ($query) => $query->whereIn('ID', $paymentIds))
            ->sum('MONTO');

        $urgentesCnt = DB::table('IMAJ_EXAMENES')
            ->when($examIds !== null, fn ($query) => $query->whereIn('ID', $examIds))
            ->where('URGENCIA', 1)
            ->whereIn('ESTADO', [1, 2])
            ->count();

        $kpis = [
            ['label' => 'Órdenes de hoy',          'value' => $ordenesHoy,                                      'tone' => 'primary', 'go' => 'rep:orden'],
            ['label' => 'Pendientes / ejecutadas', 'value' => "{$pendientes} / {$ejecutadas}",                 'tone' => 'warning', 'go' => 'rep:orden'],
            ['label' => 'Ingresos del mes',        'value' => '$ '.number_format($ingresosMes, 0, ',', '.'), 'tone' => 'success', 'go' => 'rep:ingreso'],
            ['label' => 'Exámenes urgentes',       'value' => $urgentesCnt,                                     'tone' => 'danger',  'go' => 'rep:examen'],
        ];

        $queue = DB::table('IMAJ_ORDENES as o')
            ->join('IMAJ_PACIENTES as p', 'p.ID', '=', 'o.PACIENTE_ID')
            ->leftJoin('IMAJ_EXAMENES as e', 'e.ORDEN_ID', '=', 'o.ID')
            ->leftJoin('IMAJ_PRESUPUESTOS as pr', 'pr.ORDEN_ID', '=', 'o.ID')
            ->leftJoin('IMAJ_DETALLE_PRESTACIONES as dp', 'dp.PRESUPUESTO_ID', '=', 'pr.ID')
            ->leftJoin('IMAJ_PRESTACIONES as prest', 'prest.ID', '=', 'dp.PRESTACION_ID')
            ->when($orderIds !== null, fn ($query) => $query->whereIn('o.ID', $orderIds))
            ->whereRaw('NVL(o.EJECUTADA, 0) = 0')
            ->whereRaw('NVL(o.ESTADO, 0) <> 3')
            ->select(
                'o.ID as id',
                DB::raw("'ORD-' || o.ID as orden"),
                DB::raw("p.NOMBRE || ' ' || p.APELLIDO_P as paciente"),
                DB::raw("NVL(prest.NOMBRE, 'Examen radiológico') as examen"),
                'o.HORA_CITA as hora_cita',
                DB::raw("CASE o.ESTADO WHEN 0 THEN 'AGENDADA' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'EN_PROCESO' WHEN 3 THEN 'EJECUTADA' ELSE 'PENDIENTE' END as estado"),
                DB::raw('CASE WHEN NVL(e.URGENCIA, 0) = 1 AND e.ESTADO IN (1, 2) THEN 1 ELSE 0 END as urgente')
            )
            ->orderByDesc('urgente')
            ->orderBy('o.ID')
            ->limit(20)
            ->get()
            ->map(fn ($r) => array_change_key_case((array) $r, CASE_LOWER))
            ->all();

        return view('crud.home', compact('kpis', 'queue'));
    })->name('dashboard');

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
