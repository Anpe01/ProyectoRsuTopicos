<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end   = $request->query('end', now()->toDateString());

        if ($request->ajax()) {
            $rows = Attendance::with('employee')
                ->whereBetween('attendance_date', [$start, $end])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($a) => [
                    'dni'      => $a->employee->dni ?? '',
                    'employee' => $a->employee->fullname ?? '',
                    'date'     => $a->created_at->format('d/m/Y H:i:s'),
                    'type'     => '<span class="badge badge-' . ($a->period === Attendance::PERIOD_OUT ? 'info' : 'primary') . '">' . $a->period_label . '</span>',
                    'status'   => '<span class="badge badge-' . ($a->status === Attendance::STATUS_PRESENT ? 'success' : ($a->status === Attendance::STATUS_ABSENT ? 'danger' : 'warning')) . '">' . $a->status_label . '</span>',
                    'notes'    => e($a->notes ?? ''),
                    'actions'  => view('attendances.partials.actions', compact('a'))->render(),
                ]);

            return response()->json(['data' => $rows]);
        }

        return view('attendances.index', [
            'start' => $start,
            'end'   => $end,
        ]);
    }

    public function store(AttendanceRequest $request)
    {
        try {
            $att = Attendance::create($request->validated());
            return response()->json([
                'ok' => true,
                'msg' => 'Asistencia registrada',
                'row' => [
                    'dni'      => $att->employee->dni,
                    'employee' => $att->employee->fullname,
                    'date'     => $att->created_at->format('d/m/Y H:i:s'),
                    'type'     => '<span class="badge badge-' . ($att->period === Attendance::PERIOD_OUT ? 'info' : 'primary') . '">' . $att->period_label . '</span>',
                    'status'   => '<span class="badge badge-' . ($att->status === Attendance::STATUS_PRESENT ? 'success' : ($att->status === Attendance::STATUS_ABSENT ? 'danger' : 'warning')) . '">' . $att->status_label . '</span>',
                    'notes'    => e($att->notes ?? ''),
                    'actions'  => view('attendances.partials.actions', ['a'=>$att])->render(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear asistencia: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Attendance $attendance)
    {
        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                'period' => $attendance->period,
                'status' => $attendance->status,
                'notes' => $attendance->notes,
            ]
        ]);
    }

    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        try {
            $attendance->update($request->validated());
            return response()->json([
                'ok' => true,
                'msg' => 'Asistencia actualizada',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar asistencia: ' . $e->getMessage(), [
                'attendance_id' => $attendance->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Attendance $attendance)
    {
        try {
            $attendance->delete();
            return response()->json([
                'ok' => true,
                'msg' => 'Asistencia eliminada',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar asistencia: ' . $e->getMessage(), [
                'attendance_id' => $attendance->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    // Kiosco: vista pública
    public function kiosk()
    {
        return view('attendances.kiosk');
    }

    // Kiosco: registrar (DNI + password o PIN)
    public function kioskStore(Request $request)
    {
        $request->validate([
            'dni'       => ['required','digits:8'],
            'secret'    => ['required','string','min:4'],
            'period'    => ['nullable','in:1,2'],
            'notes'     => ['nullable','string','max:2000'],
        ]);

        $emp = Employee::where('dni', $request->dni)->first();
        if (!$emp) {
            return response()->json(['ok'=>false,'msg'=>'DNI no registrado'], 422);
        }

        $ok = false;
        if (!empty($emp->pin) && hash_equals((string)$emp->pin, (string)$request->secret)) {
            $ok = true;
        }
        if (!$ok && !empty($emp->password) && Hash::check($request->secret, $emp->password)) {
            $ok = true;
        }
        if (!$ok) {
            return response()->json(['ok'=>false,'msg'=>'Credenciales inválidas'], 422);
        }

        try {
            $att = Attendance::create([
                'employee_id'     => $emp->id,
                'attendance_date' => Carbon::today()->toDateString(),
                'period'          => (int)($request->period ?? Attendance::PERIOD_IN),
                'status'          => Attendance::STATUS_PRESENT,
                'notes'           => $request->notes,
            ]);

            return response()->json(['ok'=>true,'msg'=>'Asistencia registrada','id'=>$att->id]);
        } catch (\Exception $e) {
            \Log::error('Error al crear asistencia desde kiosco: ' . $e->getMessage(), [
                'employee_id' => $emp->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['ok'=>false,'msg'=>'Error al registrar: '.$e->getMessage()], 500);
        }
    }
}
