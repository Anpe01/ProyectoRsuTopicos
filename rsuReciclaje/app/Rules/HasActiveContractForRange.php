<?php

namespace App\Rules;

use App\Models\Employee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasActiveContractForRange implements ValidationRule
{
    public function __construct(private int $employeeId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data  = request()->all();
        $start = $data['date'] ?? $data['start_date'] ?? $value;
        $end   = $data['end_date'] ?? $start;

        if (!$start) {
            $fail('Fecha requerida.');
            return;
        }

        $emp = Employee::find($this->employeeId);
        if (!$emp) {
            $fail('Empleado no encontrado.');
            return;
        }

        // Si es rango:
        $checkDates = [$start];
        if ($end && $end !== $start) {
            $checkDates[] = $end;
        }

        foreach ($checkDates as $d) {
            if (!$emp->activeContractOn($d)) {
                $fail("El empleado no tiene contrato activo en {$d}.");
                return;
            }
        }
    }
}
