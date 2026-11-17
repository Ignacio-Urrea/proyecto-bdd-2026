<?php

namespace App\Http\Controllers;

use App\Models\Imagen;
use App\Services\OracleCrudService;

class ImagenController extends CrudController
{
    protected string $modelClass = Imagen::class;

    protected string $title = 'Imágenes';

    protected string $routeName = 'imagenes';

    protected array $fields = [
        ['name' => 'TAMANIO', 'label' => 'Tamaño (bytes)', 'type' => 'number'],
        ['name' => 'NOMBRE', 'label' => 'Nombre', 'type' => 'text'],
        ['name' => 'TIPO', 'label' => 'Tipo', 'type' => 'text'],
        ['name' => 'RUTA', 'label' => 'Ruta', 'type' => 'text'],
        ['name' => 'EXAMEN_ID', 'label' => 'Examen', 'type' => 'select', 'options' => [self::class, 'examOptions']],
        ['name' => 'INFORME_ID', 'label' => 'Informe', 'type' => 'select', 'options' => [self::class, 'reportOptions']],
    ];

    public static function examOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_EXAMENES', 'ID');
    }

    public static function reportOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_INFORMES', 'TITULO');
    }
}
