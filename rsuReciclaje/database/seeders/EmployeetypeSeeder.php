<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeetypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Operario','Chofer','Supervisor','Administrativo'] as $n) {
            \App\Models\Employeetype::firstOrCreate(['name'=>$n], ['active'=>true, 'description' => ucfirst(strtolower($n)) . ' del sistema de reciclaje']);
        }
    }
}
