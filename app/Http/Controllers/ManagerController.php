<?php

namespace App\Http\Controllers;

use App\Http\Requests\Managers\StoreBudgetRequest;
use App\Http\Requests\Managers\StoreExamRequest;
use App\Http\Requests\Managers\StoreOrderRequest;
use App\Http\Requests\Managers\StorePaymentRequest;
use App\Http\Requests\Managers\StoreReportRequest;
use App\Services\ManagerWorkflowService;
use App\Services\OracleCrudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function __construct(private readonly ManagerWorkflowService $manager) {}

    public function createOrder(): View
    {
        return $this->form('Agendar una orden', 'gestores.ordenes.store', [
            ['name' => 'PACIENTE_ID', 'label' => 'Paciente', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_PACIENTES', 'RUT')],
            ['name' => 'SUCURSAL_ID', 'label' => 'Sucursal', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_SUCURSALES', 'CORREO_SUCURSAL')],
            ['name' => 'FECHA_CITA', 'label' => 'Fecha de cita', 'type' => 'date'],
            ['name' => 'HORA_CITA', 'label' => 'Hora de cita', 'type' => 'number'],
        ]);
    }

    public function storeOrder(StoreOrderRequest $request): RedirectResponse
    {
        $this->manager->createOrder($request->validated());

        return $this->complete('Orden creada.');
    }

    public function createBudget(): View
    {
        return $this->form('Generar presupuesto', 'gestores.presupuestos.store', [
            ['name' => 'ORDEN_ID', 'label' => 'Orden', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_ORDENES', 'ID')],
            ['name' => 'PRESTACION_IDS', 'label' => 'Prestaciones (puedes seleccionar varias)', 'type' => 'select', 'multiple' => true, 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_PRESTACIONES', 'NOMBRE')],
        ]);
    }

    public function storeBudget(StoreBudgetRequest $request): RedirectResponse
    {
        $this->manager->buildBudget($request->validated());

        return $this->complete('Presupuesto generado con los precios vigentes.');
    }

    public function createPayment(): View
    {
        return $this->form('Registrar pago', 'gestores.pagos.store', [
            ['name' => 'PRESUPUESTO_ID', 'label' => 'Presupuesto', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_PRESUPUESTOS', 'ID')],
            ['name' => 'MONTO', 'label' => 'Monto', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'METODO_PAGO', 'label' => 'Método de pago', 'type' => 'text'],
        ]);
    }

    public function storePayment(StorePaymentRequest $request): RedirectResponse
    {
        $this->manager->registerPayment($request->validated());

        return $this->complete('Pago registrado y estado del presupuesto actualizado.');
    }

    public function createExam(): View
    {
        return $this->form('Actualizar examen', 'gestores.examenes.store', [
            ['name' => 'EXAMEN_ID', 'label' => 'Examen', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_EXAMENES', 'ID')],
            ['name' => 'ESTADO', 'label' => 'Nuevo estado', 'type' => 'number'],
            ['name' => 'EJECUTADA', 'label' => 'Orden ejecutada', 'type' => 'select', 'options' => [1 => 'Sí', 0 => 'No']],
        ]);
    }

    public function storeExam(StoreExamRequest $request): RedirectResponse
    {
        $this->manager->completeExam($request->validated());

        return $this->complete('Estado del examen y de la orden actualizado.');
    }

    public function createReport(): View
    {
        return $this->form('Publicar informe', 'gestores.informes.store', [
            ['name' => 'EXAMEN_ID', 'label' => 'Examen', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_EXAMENES', 'ID')],
            ['name' => 'TITULO', 'label' => 'Título', 'type' => 'text'],
            ['name' => 'CUERPO', 'label' => 'Informe', 'type' => 'textarea'],
            ['name' => 'ADENDUM', 'label' => 'Adendum', 'type' => 'select', 'options' => [0 => 'No', 1 => 'Sí']],
            ['name' => 'RUTA', 'label' => 'Ruta del archivo (opcional)', 'type' => 'text'],
        ]);
    }

    public function storeReport(StoreReportRequest $request): RedirectResponse
    {
        $this->manager->publishReport($request->validated());

        return $this->complete('Informe publicado.');
    }

    private function form(string $title, string $routeName, array $fields): View
    {
        foreach ($fields as &$field) {
            if (isset($field['options']) && is_callable($field['options'])) {
                $field['options'] = $field['options']();
            }
        }

        return view('gestores.form', [
            'title' => $title,
            'routeName' => $routeName,
            'fields' => $fields,
        ]);
    }

    private function complete(string $message): RedirectResponse
    {
        return redirect()->route('dashboard')->with('status', $message);
    }
}
