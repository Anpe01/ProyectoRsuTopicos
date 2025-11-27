<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'plate', 'year', 'load_capacity', 'fuel_capacity_l', 
        'compaction_capacity_kg', 'people_capacity', 'description', 'status',
        'brand_id', 'model_id', 'brandmodel_id', 'type_id', 'color_id', 'logo_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'year' => 'integer',
        'load_capacity' => 'double',
        'fuel_capacity_l' => 'double',
        'compaction_capacity_kg' => 'double',
        'people_capacity' => 'integer',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Brandmodel::class, 'model_id');
    }

    public function brandmodel(): BelongsTo
    {
        return $this->belongsTo(Brandmodel::class, 'brandmodel_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Vehicletype::class, 'type_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function logoBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'logo_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Vehicleimage::class);
    }
}




