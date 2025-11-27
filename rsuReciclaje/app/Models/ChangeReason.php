<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeReason extends Model
{
    protected $fillable = [
        'name',
        'description',
        'active',
        'is_predefined',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_predefined' => 'boolean',
    ];

    /**
     * Scope para obtener solo motivos activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para obtener motivos predefinidos
     */
    public function scopePredefined($query)
    {
        return $query->where('is_predefined', true);
    }
}
