<?php

namespace App\Http\Requests\Managers;

use App\Http\Requests\BaseFormRequest;

class StoreOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'PACIENTE_ID' => ['required', 'integer'],
            'SUCURSAL_ID' => ['required', 'integer'],
            'FECHA_CITA' => ['required', 'date_format:Y-m-d'],
            'HORA_CITA' => ['required', 'integer'],
        ];
    }
}
