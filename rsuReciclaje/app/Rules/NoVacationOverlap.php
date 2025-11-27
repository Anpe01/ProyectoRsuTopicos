<?php

namespace App\Rules;

use App\Models\Vacation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoVacationOverlap implements ValidationRule
{
    public function __construct(private int $employeeId, private ?int $excludeVacationId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data  = request()->all();
        $start = $data['start_date'] ?? $data['start'] ?? null;
        $end   = $data['end_date']   ?? $data['end']   ?? $value;

        if (!$start || !$end) {
            return;
        }

        $query = Vacation::where('employee_id', $this->employeeId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($qq) use ($start, $end) {
                      $qq->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            });

        if ($this->excludeVacationId) {
            $query->where('id', '!=', $this->excludeVacationId);
        }

        if ($query->exists()) {
            $fail('El rango solicitado se solapa con vacaciones ya registradas.');
        }
    }
}
