<?php

namespace App\Http\Requests\Crud;

use InvalidArgumentException;

class CrudRequestRules
{
    private const RULES = [
        'roles' => [
            'store' => ['NOMBRE_ROL' => ['required', 'string', 'max:16']],
        ],
        'regiones' => [
            'store' => ['NOMBRE_REGION' => ['required', 'string', 'max:22']],
        ],
        'prestaciones' => [
            'store' => [
                'NOMBRE' => ['required', 'string', 'max:32'],
                'PRECIO_BASE' => ['required', 'numeric', 'min:0'],
                'HABILITADA' => ['required', 'integer', 'in:0,1'],
            ],
        ],
        'usuarios' => [
            'store' => [
                'NOMBRE_USUARIO' => ['required', 'string', 'max:16'],
                'PASSWORD_HASH' => ['required', 'string', 'min:12', 'max:128'],
                'ROL_ID' => ['required', 'integer'],
            ],
            'update' => [
                'NOMBRE_USUARIO' => ['required', 'string', 'max:16'],
                'PASSWORD_HASH' => ['nullable', 'string', 'min:12', 'max:128'],
                'ROL_ID' => ['required', 'integer'],
            ],
        ],
        'ciudades' => [
            'store' => [
                'REGION_ID' => ['required', 'integer'],
                'NOMBRE_CIUDAD' => ['required', 'string', 'max:32'],
            ],
        ],
        'direcciones' => [
            'store' => [
                'CIUDAD_ID' => ['required', 'integer'],
                'CALLE' => ['required', 'string', 'max:22'],
            ],
        ],
        'clinicas' => [
            'store' => [
                'ADMINISTRADOR_ID' => ['required', 'integer'],
                'DIRECCION_ID' => ['required', 'integer'],
                'NOMBRE_CLINICA' => ['required', 'string', 'max:32'],
                'CORREO_CLINICA' => ['required', 'email', 'max:32'],
            ],
        ],
        'sucursales' => [
            'store' => [
                'ADMINISTRADOR_ID' => ['required', 'integer'],
                'CLINICA_ID' => ['required', 'integer'],
                'CORREO_SUCURSAL' => ['required', 'email', 'max:32'],
                'DIRECCION_ID' => ['required', 'integer'],
            ],
        ],
        'pacientes' => [
            'store' => [
                'CLINICA_ID' => ['required', 'integer'],
                'FECHA_NACIMIENTO' => ['required', 'date'],
                'NOMBRE' => ['required', 'string', 'max:32'],
                'APELLIDO_P' => ['required', 'string', 'max:32'],
                'APELLIDO_M' => ['required', 'string', 'max:32'],
                'RUT' => ['required', 'string', 'regex:/^(?:\d{7,8}|\d{1,2}.\d{3}.\d{3})-?[\dkK]$/'],
                'TELEFONO' => ['nullable', 'integer'],
            ],
        ],
        'ordenes' => [
            'store' => [
                'PACIENTE_ID' => ['required', 'integer'],
                'SUCURSAL_ID' => ['required', 'integer'],
                'EJECUTADA' => ['required', 'integer', 'in:0,1'],
                'FECHA_CITA' => ['required', 'date'],
                'HORA_CITA' => ['required', 'integer'],
                'ESTADO' => ['required', 'integer'],
            ],
        ],
        'examenes' => [
            'store' => [
                'ORDEN_ID' => ['required', 'integer'],
                'URGENCIA' => ['required', 'integer'],
                'FECHA_SOLICITUD' => ['required', 'date'],
                'ESTADO' => ['required', 'integer'],
            ],
        ],
        'presupuestos' => [
            'store' => [
                'ESTADO' => ['required', 'integer'],
                'ORDEN_ID' => ['required', 'integer'],
            ],
        ],
        'pagos' => [
            'store' => [
                'PRESUPUESTO_ID' => ['required', 'integer'],
                'MONTO' => ['required', 'numeric', 'min:0'],
                'METODO_PAGO' => ['required', 'string', 'max:32'],
            ],
        ],
        'detalle-prestaciones' => [
            'store' => [
                'PRESUPUESTO_ID' => ['required', 'integer'],
                'PRESTACION_ID' => ['required', 'integer'],
                'PRECIO' => ['required', 'numeric', 'min:0'],
            ],
        ],
        'informes' => [
            'store' => [
                'CUERPO' => ['required', 'string', 'max:1028'],
                'ADENDUM' => ['required', 'integer', 'in:0,1'],
                'TITULO' => ['required', 'string', 'max:32'],
                'RUTA' => ['nullable', 'string', 'max:64'],
                'EXAMEN_ID' => ['required', 'integer'],
            ],
        ],
        'imagenes' => [
            'store' => [
                'TAMANIO' => ['required', 'integer', 'min:0'],
                'NOMBRE' => ['required', 'string', 'max:64'],
                'TIPO' => ['required', 'string', 'max:16'],
                'RUTA' => ['nullable', 'string', 'max:64'],
                'EXAMEN_ID' => ['nullable', 'integer'],
                'INFORME_ID' => ['nullable', 'integer'],
            ],
        ],
    ];

    public static function for(string $resource, bool $updating = false): array
    {
        $action = $updating ? 'update' : 'store';
        $resourceRules = self::RULES[$resource] ?? null;

        if ($resourceRules === null) {
            throw new InvalidArgumentException("No existen reglas CRUD para {$resource}.");
        }

        return $resourceRules[$action] ?? $resourceRules['store'];
    }

    public static function forRoute(string $routeName, bool $updating = false): array
    {
        $separator = strrpos($routeName, '.');

        if ($separator === false) {
            throw new InvalidArgumentException("La ruta {$routeName} no identifica un recurso CRUD.");
        }

        return self::for(substr($routeName, 0, $separator), $updating);
    }
}
