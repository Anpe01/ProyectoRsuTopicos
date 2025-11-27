<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFunction extends Model
{
    protected $table = 'functions';
    
    protected $fillable = ['name', 'description', 'active'];
    
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
