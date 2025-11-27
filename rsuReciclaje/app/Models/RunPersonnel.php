<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunPersonnel extends Model
{
    protected $table = 'run_personnel';
    protected $fillable = ['run_id','staff_id','function_id','role'];

    public function run()      { return $this->belongsTo(Run::class); }
    public function employee() { return $this->belongsTo(Employee::class, 'staff_id'); }
    public function functionModel() { 
        return $this->belongsTo(FunctionModel::class, 'function_id'); 
    }
    
    // Alias para mantener compatibilidad
    public function function() {
        return $this->functionModel();
    }
}








