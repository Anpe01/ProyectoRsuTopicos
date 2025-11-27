<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunctionModel extends Model
{
    protected $table = 'functions'; // tabla real
    protected $fillable = ['name', 'description', 'protected'];
    protected $casts = ['protected' => 'boolean'];

    // Relación con empleados mediante pivot staff_function
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'staff_function', 'function_id', 'staff_id');
    }
}










