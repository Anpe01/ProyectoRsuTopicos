<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'district_id' => ['required', 'exists:districts,id'],
            'zonecoords' => ['sometimes', 'array', 'min:3'],
            'zonecoords.*.latitude' => ['required_with:zonecoords', 'numeric', 'between:-90,90'],
            'zonecoords.*.longitude' => ['required_with:zonecoords', 'numeric', 'between:-180,180'],
            'zonecoords.*.sequence' => ['required_with:zonecoords', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'district_id' => 'distrito',
            'zonecoords' => 'coordenadas',
            'zonecoords.*.latitude' => 'latitud',
            'zonecoords.*.longitude' => 'longitud',
            'zonecoords.*.sequence' => 'secuencia',
        ];
    }
}














