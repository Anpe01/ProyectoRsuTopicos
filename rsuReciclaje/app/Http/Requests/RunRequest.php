<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id' => ['required', 'exists:programs,id'],
            'date' => ['required', 'date'],
            'status' => ['sometimes', 'in:pending,started,completed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'program_id' => 'programación',
            'date' => 'fecha',
            'status' => 'estado',
        ];
    }
}












