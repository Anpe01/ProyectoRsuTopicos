<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vehicleimage;
use Illuminate\Foundation\Http\FormRequest;

class VehicleimageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehicleId = (int) ($this->route('vehicle')?->id ?? $this->input('vehicle_id'));
        $profile = (bool) $this->input('profile', false);

        $rules = [
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'profile' => ['sometimes', 'boolean'],
        ];

        // Validar única imagen de perfil por vehículo
        $this->after(function () use ($vehicleId, $profile): void {
            if ($profile) {
                $exists = Vehicleimage::where('vehicle_id', $vehicleId)->where('profile', true)->exists();
                if ($exists) {
                    $this->validator->errors()->add('profile', 'Ya existe una imagen de perfil para este vehículo.');
                }
            }
        });

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'image' => 'imagen',
            'profile' => 'perfil',
        ];
    }
}














