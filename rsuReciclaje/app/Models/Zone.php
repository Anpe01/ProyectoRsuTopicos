<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'name','department_id','polygon','area_km2','avg_waste_tb','active','description'
    ];

    protected $casts = [
        'polygon' => 'array',
        'active'  => 'boolean',
        'area_km2'=> 'float',
        'avg_waste_tb'=>'float',
    ];

    // Relaciones
    public function department(){ 
        return $this->belongsTo(\App\Models\Department::class); 
    }

    // Mantener relaciones antiguas por compatibilidad
    public function zonecoords() {
        return $this->hasMany(Zonecoord::class)->orderBy('sequence');
    }

    public function programs() {
        return $this->hasMany(Program::class);
    }
}
