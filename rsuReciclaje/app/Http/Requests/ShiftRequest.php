<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('shift')?->id ?? null;

        return [
            'name'        => ['required','string','max:100', Rule::unique('shifts','name')->ignore($id)],
            'start_time'  => ['required','date_format:H:i'],
            'end_time'    => ['required','date_format:H:i'],
            'description' => ['nullable','string','max:500'],
            'active'      => ['nullable','boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'       => 'nombre del turno',
            'start_time' => 'hora de entrada',
            'end_time'   => 'hora de salida',
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.date_format' => 'Hora de entrada inválida (use HH:mm).',
            'end_time.date_format'   => 'Hora de salida inválida (use HH:mm).',
        ];
    }
}












