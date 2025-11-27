<?php
namespace App\Http\Controllers;

use App\Models\Run;
use App\Models\RunPersonnel;
use App\Models\Contract;
use App\Models\Vacation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RunPersonnelController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'run_id'     => ['required','exists:runs,id'],
            'staff_id'   => ['required','exists:employees,id'],
            'function_id'=> ['required','exists:functions,id'],
        ]);

        // Obtener el recorrido con su fecha
        $run = Run::findOrFail($data['run_id']);
        $runDate = Carbon::parse($run->run_date ?? now());

        // Validar que el empleado tenga contrato activo usando el helper
        $employee = Employee::find($data['staff_id']);
        if (!$employee) {
            return back()->with('error','Empleado no encontrado.');
        }

        $contract = $employee->activeContractOn($runDate->toDateString());
        if (!$contract) {
            return back()->with('error','El empleado no tiene un contrato activo vigente para la fecha del recorrido.');
        }

        // Validar que el empleado no esté en vacaciones
        $onVacation = Vacation::where('employee_id', $data['staff_id'])
            ->whereDate('start_date', '<=', $runDate)
            ->whereDate('end_date', '>=', $runDate)
            ->exists();

        if ($onVacation) {
            return back()->with('error','El empleado está en vacaciones durante la fecha del recorrido.');
        }

        // Evitar duplicados por (run_id, staff_id) - ya hay unique a nivel BD
        try {
            RunPersonnel::create($data);
            return back()->with('success','Personal asignado.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error','Ese empleado ya está asignado al recorrido.');
        }
    }

    public function destroy(RunPersonnel $runpersonnel)
    {
        $runpersonnel->delete();
        return back()->with('success','Personal retirado del recorrido.');
    }
}








