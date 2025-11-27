<?php

namespace App\Rules;

use App\Models\Employee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasEligibleVacationContract implements ValidationRule
{
    public function __construct(private int $employeeId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value será la fecha de fin; necesitamos también el inicio desde el validator
        $data = request()->all();
        $start = $data['start_date'] ?? $data['start'] ?? null;
        $end   = $data['end_date']   ?? $data['end']   ?? $value;

        if (!$start || !$end) {
            $fail('Debe indicar un rango de fechas.');
            return;
        }

        $employee = Employee::find($this->employeeId);
        if (!$employee) {
            $fail('Empleado no encontrado.');
            return;
        }

        if (!$employee->hasEligibleVacationContractOn($start, $end)) {
            $fail('El contrato del empleado no permite vacaciones en el rango elegido (solo Nombrado o a tiempo completo con contrato activo).');
        }
    }
}
