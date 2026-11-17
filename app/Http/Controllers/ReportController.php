<?php

namespace App\Http\Controllers;

use App\Services\OracleCrudService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function orders(): View
    {
        return $this->show('Reporte de órdenes por sucursal y estado', 'REPORT_ORDERS', [
            'SUCURSAL_ID' => 'Sucursal',
            'CORREO_SUCURSAL' => 'Correo',
            'ESTADO' => 'Estado',
            'TOTAL_ORDENES' => 'Órdenes',
        ]);
    }

    public function revenue(): View
    {
        return $this->show('Ingresos mensuales', 'REPORT_REVENUE', [
            'PERIODO' => 'Periodo',
            'INGRESOS' => 'Ingresos',
            'TOTAL_PAGOS' => 'Pagos',
        ]);
    }

    public function exams(): View
    {
        return $this->show('Exámenes por urgencia y estado', 'REPORT_EXAMS', [
            'URGENCIA' => 'Urgencia',
            'ESTADO' => 'Estado',
            'TOTAL_EXAMENES' => 'Exámenes',
        ]);
    }

    private function show(string $title, string $procedure, array $columns): View
    {
        return view('reportes.index', [
            'title' => $title,
            'columns' => $columns,
            'rows' => app(OracleCrudService::class)->report($procedure),
        ]);
    }
}
