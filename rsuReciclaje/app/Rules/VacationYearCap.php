<?php

namespace App\Rules;

use App\Models\Vacation;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VacationYearCap implements ValidationRule
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

        try {
            $start = Carbon::parse($start);
            $end = Carbon::parse($end);
        } catch (\Exception $e) {
            $fail('Rango de fechas inválido.');
            return;
        }

        if ($end->lt($start)) {
            $fail('La fecha fin debe ser igual o posterior a la fecha inicio.');
            return;
        }

        $year = (int) $start->year;
        
        // No requerimos que sea el mismo año, pero validamos por año
        $newDays = $start->diffInDays($end) + 1; // Inclusivo

        $query = Vacation::where('employee_id', $this->employeeId)
            ->whereYear('start_date', $year);

        if ($this->excludeVacationId) {
            $query->where('id', '!=', $this->excludeVacationId);
        }

        $usedDays = (int) $query->sum('days');

        if ($usedDays + $newDays > 30) {
            $remaining = 30 - $usedDays;
            $fail("Excede el tope anual de 30 días. Ya usados: {$usedDays}, solicitados: {$newDays}. Máximo disponible: {$remaining} días.");
        }
    }
}
