<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_id' => ['required', 'exists:zones,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'conductor_id' => ['required', 'exists:staff,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'zone_id' => 'zona',
            'vehicle_id' => 'vehículo',
            'conductor_id' => 'conductor',
            'shift_id' => 'turno',
            'weekdays' => 'días de semana',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
        ];
    }
}












