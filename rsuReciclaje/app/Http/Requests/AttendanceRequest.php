<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id'      => ['required','exists:employees,id'],
            'attendance_date'  => ['required','date'],
            'period'           => ['required','in:1,2'],      // 1 Entrada, 2 Salida
            'status'           => ['required','in:0,1,2'],    // 0 Falta, 1 Presente, 2 Justificado
            'notes'            => ['nullable','string','max:2000'],
        ];
    }
}
