<?php

namespace App\Http\Requests\Managers;

use App\Http\Requests\BaseFormRequest;

class StoreBudgetRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ORDEN_ID' => ['required', 'integer'],
            'PRESTACION_IDS' => ['required', 'array', 'min:1'],
            'PRESTACION_IDS.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
