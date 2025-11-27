<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = (int) date('Y');
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', Rule::unique('vehicles', 'code')->ignore($vehicleId)],
            'plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($vehicleId), 'regex:/^(?:[A-Z0-9]{6}|[A-Z]{2}-\d{4}|[A-Z]{3}-\d{3})$/i'],
            'year' => ['required', 'integer', 'min:1950', 'max:' . $currentYear],
            'type_id' => ['required', 'exists:vehicletypes,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'brandmodel_id' => ['required', 'exists:brandmodels,id'],
            'logo_id' => ['nullable', 'exists:brands,id'],
            'color_id' => ['required', 'exists:colors,id'],
            'load_capacity' => ['nullable', 'numeric', 'min:0'],
            'fuel_capacity_l' => ['nullable', 'numeric', 'min:0'],
            'compaction_capacity_kg' => ['nullable', 'numeric', 'min:0'],
            'people_capacity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'plate' => 'placa',
            'year' => 'año',
            'type_id' => 'tipo',
            'brand_id' => 'marca',
            'brandmodel_id' => 'modelo',
            'color_id' => 'color',
            'load_capacity' => 'capacidad de carga',
            'fuel_capacity_l' => 'capacidad de combustible',
            'compaction_capacity_kg' => 'capacidad de compactación',
            'people_capacity' => 'capacidad de personas',
            'status' => 'estado',
            'description' => 'descripción',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingrese el nombre del vehículo.',
            'code.required' => 'Ingrese el código del vehículo.',
            'code.unique' => 'Este código ya está registrado.',
            'plate.required' => 'Ingrese la placa del vehículo.',
            'plate.unique' => 'Esta placa ya está registrada.',
            'plate.regex' => 'El formato de la placa no es válido.',
            'year.required' => 'Ingrese el año del vehículo.',
            'year.integer' => 'El año debe ser un número entero.',
            'year.min' => 'El año debe ser mayor o igual a 1950.',
            'year.max' => 'El año no puede ser mayor al año actual.',
            'type_id.required' => 'Seleccione un tipo de vehículo.',
            'type_id.exists' => 'El tipo de vehículo seleccionado no existe.',
            'brand_id.required' => 'Seleccione una marca.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'brandmodel_id.required' => 'Seleccione un modelo.',
            'brandmodel_id.exists' => 'El modelo seleccionado no existe.',
            'color_id.required' => 'Seleccione un color.',
            'color_id.exists' => 'El color seleccionado no existe.',
            'load_capacity.numeric' => 'La capacidad de carga debe ser un número.',
            'load_capacity.min' => 'La capacidad de carga debe ser mayor o igual a 0.',
            'fuel_capacity_l.numeric' => 'La capacidad de combustible debe ser un número.',
            'fuel_capacity_l.min' => 'La capacidad de combustible debe ser mayor o igual a 0.',
            'compaction_capacity_kg.numeric' => 'La capacidad de compactación debe ser un número.',
            'compaction_capacity_kg.min' => 'La capacidad de compactación debe ser mayor o igual a 0.',
            'people_capacity.integer' => 'La capacidad de personas debe ser un número entero.',
            'people_capacity.min' => 'La capacidad de personas debe ser mayor o igual a 1.',
            'people_capacity.max' => 'La capacidad de personas debe ser menor o igual a 50.',
        ];
    }
}




