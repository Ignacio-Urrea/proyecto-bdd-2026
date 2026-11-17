<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Services\OracleCrudService;

class InformeController extends CrudController
{
    protected string $modelClass = Informe::class;

    protected string $title = 'Informes';

    protected string $routeName = 'informes';

    protected array $fields = [
        ['name' => 'CUERPO', 'label' => 'Cuerpo del informe', 'type' => 'textarea'],
        ['name' => 'ADENDUM', 'label' => 'Adendum', 'type' => 'select', 'options' => [1 => 'Sí', 0 => 'No']],
        ['name' => 'TITULO', 'label' => 'Título', 'type' => 'text'],
        ['name' => 'RUTA', 'label' => 'Ruta', 'type' => 'text'],
        ['name' => 'EXAMEN_ID', 'label' => 'Examen', 'type' => 'select', 'options' => [self::class, 'examOptions']],
    ];

    public static function examOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_EXAMENES', 'ID');
    }
}
