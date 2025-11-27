<?php

namespace App\Rules;

use App\Models\Vacation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotOnVacation implements ValidationRule
{
    public function __construct(private int $employeeId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data  = request()->all();
        $start = $data['date'] ?? $data['start_date'] ?? $value;
        $end   = $data['end_date'] ?? $start;

        if (!$start) {
            return;
        }

        $overlap = Vacation::where('employee_id', $this->employeeId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($qq) use ($start, $end) {
                      $qq->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->exists();

        if ($overlap) {
            $fail('No se puede programar porque el rango se cruza con vacaciones del empleado.');
        }
    }
}
