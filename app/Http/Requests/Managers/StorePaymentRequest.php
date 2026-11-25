<?php

namespace App\Http\Requests\Managers;

use App\Http\Requests\BaseFormRequest;

class StorePaymentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'PRESUPUESTO_ID' => ['required', 'integer'],
            'MONTO' => ['required', 'numeric', 'gt:0'],
            'METODO_PAGO' => ['required', 'string', 'max:32'],
        ];
    }
}
