<?php

namespace App\Http\Controllers;

use App\Services\OracleCrudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;

class ManagerController extends Controller
{
    public function createOrder(): View
    {
        return $this->form('Agendar una orden', 'gestores.ordenes.store', [
            ['name' => 'PACIENTE_ID', 'label' => 'Paciente', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_PACIENTES', 'RUT')],
            ['name' => 'SUCURSAL_ID', 'label' => 'Sucursal', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_SUCURSALES', 'CORREO_SUCURSAL')],
            ['name' => 'FECHA_CITA', 'label' => 'Fecha de cita', 'type' => 'date'],
            ['name' => 'HORA_CITA', 'label' => 'Hora de cita', 'type' => 'number'],
        ]);
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'PACIENTE_ID' => ['required', 'integer'],
            'SUCURSAL_ID' => ['required', 'integer'],
            'FECHA_CITA' => ['required', 'date_format:Y-m-d'],
            'HORA_CITA' => ['required', 'integer'],
        ]);

        app(OracleCrudService::class)->runManager('CREATE_ORDER', [
            'p_patient_id' => $data['PACIENTE_ID'],
            'p_branch_id' => $data['SUCURSAL_ID'],
            'p_date' => $data['FECHA_CITA'],
            'p_hour' => $data['HORA_CITA'],
        ]);

        return $this->complete('Orden creada.');
    }

    public function createBudget(): View
    {
        return $this->form('Generar presupuesto', 'gestores.presupuestos.store', [
            ['name' => 'ORDEN_ID', 'label' => 'Orden', 'type' => 'select', 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_ORDENES', 'ID')],
            ['name' => 'PRESTACION_IDS', 'label' => 'Prestaciones (puedes seleccionar varias)', 'type' => 'select', 'multiple' => true, 'options' => fn () => app(OracleCrudService::class)->options('IMAJ_PRESTACIONES', 'NOMBRE')],
        ]);
    }

    /**
     * @throws JsonException
     */
    public function storeBudget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ORDEN_ID' => ['required', 'integer'],
            'PRESTACION_IDS' => ['required', 'array', 'min:1'],
            'PRESTACION_IDS.*' => ['required', 'integer'],
        ]);

        app(OracleCrudService::class)->runManager('BUILD_BUDGET', [
            'p_order_id' => $data['ORDEN_ID'],
            'p_services' => json_encode(array_map('intval', $data['PRESTACION_IDS']), JSON_THROW_ON_ERROR),
        ]);

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

    public function storePayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'PRESUPUESTO_ID' => ['required', 'integer'],
            'MONTO' => ['required', 'numeric', 'gt:0'],
            'METODO_PAGO' => ['required', 'string', 'max:32'],
        ]);

        app(OracleCrudService::class)->runManager('REGISTER_PAYMENT', [
            'p_budget_id' => $data['PRESUPUESTO_ID'],
            'p_amount' => $data['MONTO'],
            'p_method' => $data['METODO_PAGO'],
        ]);

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

    public function storeExam(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'EXAMEN_ID' => ['required', 'integer'],
            'ESTADO' => ['required', 'integer'],
            'EJECUTADA' => ['required', 'integer', 'in:0,1'],
        ]);

        app(OracleCrudService::class)->runManager('COMPLETE_EXAM', [
            'p_exam_id' => $data['EXAMEN_ID'],
            'p_status' => $data['ESTADO'],
            'p_completed' => $data['EJECUTADA'],
        ]);

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

    public function storeReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'EXAMEN_ID' => ['required', 'integer'],
            'TITULO' => ['required', 'string', 'max:32'],
            'CUERPO' => ['required', 'string', 'max:1028'],
            'ADENDUM' => ['required', 'integer', 'in:0,1'],
            'RUTA' => ['nullable', 'string', 'max:64'],
        ]);

        app(OracleCrudService::class)->runManager('PUBLISH_REPORT', [
            'p_exam_id' => $data['EXAMEN_ID'],
            'p_title' => $data['TITULO'],
            'p_body' => $data['CUERPO'],
            'p_addendum' => $data['ADENDUM'],
            'p_path' => $data['RUTA'] ?? null,
        ]);

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
