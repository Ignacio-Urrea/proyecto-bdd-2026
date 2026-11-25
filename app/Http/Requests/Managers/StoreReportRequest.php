<?php

namespace App\Http\Requests\Managers;

use App\Http\Requests\BaseFormRequest;

class StoreReportRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'EXAMEN_ID' => ['required', 'integer'],
            'TITULO' => ['required', 'string', 'max:32'],
            'CUERPO' => ['required', 'string', 'max:1028'],
            'ADENDUM' => ['required', 'integer', 'in:0,1'],
            'RUTA' => ['nullable', 'string', 'max:64'],
        ];
    }
}
