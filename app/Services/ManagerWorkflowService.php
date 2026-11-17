<?php

namespace App\Services;

use JsonException;

class ManagerWorkflowService
{
    public function __construct(private readonly OracleCrudService $oracle) {}

    public function createOrder(array $data): void
    {
        $this->oracle->runManager('CREATE_ORDER', [
            'p_patient_id' => $data['PACIENTE_ID'],
            'p_branch_id' => $data['SUCURSAL_ID'],
            'p_date' => $data['FECHA_CITA'],
            'p_hour' => $data['HORA_CITA'],
        ]);
    }

    /**
     * @throws JsonException
     */
    public function buildBudget(array $data): void
    {
        $this->oracle->runManager('BUILD_BUDGET', [
            'p_order_id' => $data['ORDEN_ID'],
            'p_services' => json_encode(array_map('intval', $data['PRESTACION_IDS']), JSON_THROW_ON_ERROR),
        ]);
    }

    public function registerPayment(array $data): void
    {
        $this->oracle->runManager('REGISTER_PAYMENT', [
            'p_budget_id' => $data['PRESUPUESTO_ID'],
            'p_amount' => $data['MONTO'],
            'p_method' => $data['METODO_PAGO'],
        ]);
    }

    public function completeExam(array $data): void
    {
        $this->oracle->runManager('COMPLETE_EXAM', [
            'p_exam_id' => $data['EXAMEN_ID'],
            'p_status' => $data['ESTADO'],
            'p_completed' => $data['EJECUTADA'],
        ]);
    }

    public function publishReport(array $data): void
    {
        $this->oracle->runManager('PUBLISH_REPORT', [
            'p_exam_id' => $data['EXAMEN_ID'],
            'p_title' => $data['TITULO'],
            'p_body' => $data['CUERPO'],
            'p_addendum' => $data['ADENDUM'],
            'p_path' => $data['RUTA'] ?? null,
        ]);
    }
}
