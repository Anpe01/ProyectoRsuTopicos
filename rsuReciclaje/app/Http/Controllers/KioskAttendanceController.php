<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class KioskAttendanceController extends Controller
{
    public function show() {
        return view('kiosk.attendance'); // formulario simple con DNI + contraseña
    }

    public function store(Request $request) {
        $request->validate([
            'dni' => ['required','digits_between:8,12'],
            'password' => ['required','min:4'],
        ]);

        $employee = Employee::where('dni', $request->dni)->first();

        if (!$employee) {
            return response()->json(['ok'=>false,'msg'=>'DNI no registrado.'], 422);
        }
        if (!Hash::check($request->password, $employee->password)) {
            return response()->json(['ok'=>false,'msg'=>'Credenciales inválidas.'], 422);
        }

        $now   = Carbon::now();
        $today = $now->toDateString();

        // Buscar último registro del día sin salida
        $open = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->orderByDesc('id')
            ->first();

        if ($open) {
            // Registrar SALIDA
            try {
                $open->update([
                    'check_out' => $now->format('H:i'),
                    'present'   => true,
                    'method'    => 'Kiosk',
                ]);
                return response()->json([
                    'ok'=>true,
                    'type'=>'Salida',
                    'msg'=>"Salida registrada a las ".$now->format('H:i')
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al actualizar asistencia desde kiosco: ' . $e->getMessage(), [
                    'attendance_id' => $open->id,
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'ok'=>false,
                    'msg'=>'Error al registrar salida: '.$e->getMessage()
                ], 500);
            }
        }

        // Registrar ENTRADA
        try {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date'        => $today,
                'check_in'    => $now->format('H:i'),
                'present'     => true,
                'method'      => 'Kiosk',
                'notes'       => null,
            ]);

            return response()->json([
                'ok'=>true,
                'type'=>'Entrada',
                'msg'=>"Entrada registrada a las ".$now->format('H:i'),
                'id'=>$attendance->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear asistencia desde kiosco: ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'date' => $today,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok'=>false,
                'msg'=>'Error al registrar: '.$e->getMessage()
            ], 500);
        }
    }
}

