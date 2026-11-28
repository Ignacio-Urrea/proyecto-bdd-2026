<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Services\OracleCrudService;

class ExamenController extends CrudController
{
    protected string $modelClass = Examen::class;

    protected string $title = 'Exámenes';

    protected string $routeName = 'examenes';

    protected array $fields = [
        ['name' => 'ORDEN_ID', 'label' => 'Orden', 'type' => 'select', 'options' => [self::class, 'orderOptions']],
        ['name' => 'RADIOLOGO_ID', 'label' => 'Radiólogo', 'type' => 'select', 'options' => [self::class, 'radiologistOptions']],
        ['name' => 'URGENCIA', 'label' => 'Urgencia', 'type' => 'number'],
        ['name' => 'FECHA_SOLICITUD', 'label' => 'Fecha de solicitud', 'type' => 'date'],
        ['name' => 'ESTADO', 'label' => 'Estado', 'type' => 'number'],
    ];

    public static function orderOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_ORDENES', 'ID');
    }

    public static function radiologistOptions(): array
    {
        return app(OracleCrudService::class)->radiologistOptions();
    }
}
