<?php

namespace App\Http\Requests;

use App\Rules\HasEligibleVacationContract;
use App\Rules\VacationYearCap;
use App\Rules\NoVacationOverlap;
use Illuminate\Foundation\Http\FormRequest;

class VacationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $employeeId = (int)($this->input('employee_id'));
        $vacationId = $this->route('vacation')?->id ?? null;

        $rules = [
            'employee_id' => ['required','exists:employees,id'],
            'start_date'  => ['required','date'],
            'end_date'    => ['required','date','after_or_equal:start_date'],
            'notes'       => ['nullable','string','max:500'],
        ];

        // Agregar reglas personalizadas solo si tenemos employee_id
        if ($employeeId) {
            $rules['end_date'][] = new HasEligibleVacationContract($employeeId);
            $rules['end_date'][] = new VacationYearCap($employeeId, $vacationId);
            $rules['end_date'][] = new NoVacationOverlap($employeeId, $vacationId);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Seleccione un empleado.',
            'end_date.after_or_equal' => 'La fecha fin debe ser igual o posterior al inicio.',
        ];
    }
}
