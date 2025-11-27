<?php

namespace App\Http\Controllers;

use App\Http\Requests\VacationRequest;
use App\Models\Vacation;
use App\Models\Employee;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('last_name')->orderBy('first_name')->get(['id','first_name','last_name']);
        return view('vacations.index', compact('employees'));
    }

    // Data para DataTables
    public function datatable()
    {
        $rows = Vacation::with('employee')->latest('start_date')->get()->map(function($v){
            return [
                'employee'   => $v->employee ? ($v->employee->last_name.', '.$v->employee->first_name) : '—',
                'start_date' => optional($v->start_date)->format('Y-m-d'),
                'end_date'   => optional($v->end_date)->format('Y-m-d'),
                'days'       => $v->days ?? '',
                'notes'      => $v->notes ?? '',
                'actions'    => view('vacations.partials.actions', compact('v'))->render(),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(VacationRequest $request)
    {
        try {
            $data = $request->validated();

            $start = Carbon::parse($data['start_date']);
            $end   = Carbon::parse($data['end_date']);
            $days  = $start->diffInDays($end) + 1; // Inclusivo

            // Validar mínimo 1 día
            if ($days < 1) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Las vacaciones deben tener al menos 1 día.'
                ], 422);
            }

            // La validación de máximo 30 días anuales se realiza en VacationYearCap
            // No hay límite por mes, solo el límite anual de 30 días

            $vac = Vacation::create([
                'employee_id' => $data['employee_id'],
                'start_date'  => $start->toDateString(),
                'end_date'    => $end->toDateString(),
                'days'        => $days,
                'year'        => $start->year,
                'notes'       => $data['notes'] ?? null,
            ]);

            return response()->json(['ok'=>true, 'id'=>$vac->id, 'msg'=>'Vacación registrada correctamente.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear vacación: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Vacation $vacation)
    {
        return response()->json([
            'id' => $vacation->id,
            'employee_id' => $vacation->employee_id,
            'start_date' => optional($vacation->start_date)->format('Y-m-d'),
            'end_date' => optional($vacation->end_date)->format('Y-m-d'),
            'days' => $vacation->days,
            'notes' => $vacation->notes
        ]);
    }

    public function update(VacationRequest $request, Vacation $vacation)
    {
        try {
            $data = $request->validated();

            $start = Carbon::parse($data['start_date']);
            $end   = Carbon::parse($data['end_date']);
            $days  = $start->diffInDays($end) + 1; // Inclusivo

            // Validar mínimo 1 día
            if ($days < 1) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Las vacaciones deben tener al menos 1 día.'
                ], 422);
            }

            // La validación de máximo 30 días anuales se realiza en VacationYearCap
            // No hay límite por mes, solo el límite anual de 30 días

            $vacation->update([
                'employee_id' => $data['employee_id'],
                'start_date'  => $start->toDateString(),
                'end_date'    => $end->toDateString(),
                'days'        => $days,
                'year'        => $start->year,
                'notes'       => $data['notes'] ?? null,
            ]);

            return response()->json(['ok'=>true, 'msg'=>'Vacación actualizada correctamente.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar vacación: ' . $e->getMessage(), [
                'vacation_id' => $vacation->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Vacation $vacation)
    {
        $vacation->delete();
        return response()->json(['ok'=>true]);
    }

    private function countDays(string $start, string $end): int
    {
        $s = Carbon::parse($start)->startOfDay();
        $e = Carbon::parse($end)->startOfDay();
        return $s->diffInDays($e) + 1; // inclusivo
    }

}
