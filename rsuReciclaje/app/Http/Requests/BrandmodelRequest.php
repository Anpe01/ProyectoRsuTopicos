<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandmodelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('brandmodel')?->id;
        $brandId = $this->input('brand_id');

        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('brandmodels')->where(fn ($q) => $q->where('brand_id', $brandId))->ignore($id),
            ],
            'description' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'brand_id' => 'marca',
            'name' => 'nombre',
            'description' => 'descripción',
        ];
    }
}














