<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Province;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas si están vacías (solo si están completamente vacías)
        if (Department::count() === 0) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                District::truncate();
                Province::truncate();
                Department::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e) {
                // Si es SQLite u otra BD, intentar sin FOREIGN_KEY_CHECKS
                try {
                    District::truncate();
                    Province::truncate();
                    Department::truncate();
                } catch (\Exception $e2) {
                    // Ignorar si no se puede truncar
                }
            }
        }

            // Departamentos del Perú (24 departamentos)
            $departments = [
                ['code' => '01', 'name' => 'Amazonas'],
                ['code' => '02', 'name' => 'Ancash'],
                ['code' => '03', 'name' => 'Apurímac'],
                ['code' => '04', 'name' => 'Arequipa'],
                ['code' => '05', 'name' => 'Ayacucho'],
                ['code' => '06', 'name' => 'Cajamarca'],
                ['code' => '07', 'name' => 'Callao'],
                ['code' => '08', 'name' => 'Cusco'],
                ['code' => '09', 'name' => 'Huancavelica'],
                ['code' => '10', 'name' => 'Huánuco'],
                ['code' => '11', 'name' => 'Ica'],
                ['code' => '12', 'name' => 'Junín'],
                ['code' => '13', 'name' => 'La Libertad'],
                ['code' => '14', 'name' => 'Lambayeque'],
                ['code' => '15', 'name' => 'Lima'],
                ['code' => '16', 'name' => 'Loreto'],
                ['code' => '17', 'name' => 'Madre de Dios'],
                ['code' => '18', 'name' => 'Moquegua'],
                ['code' => '19', 'name' => 'Pasco'],
                ['code' => '20', 'name' => 'Piura'],
                ['code' => '21', 'name' => 'Puno'],
                ['code' => '22', 'name' => 'San Martín'],
                ['code' => '23', 'name' => 'Tacna'],
                ['code' => '24', 'name' => 'Tumbes'],
                ['code' => '25', 'name' => 'Ucayali'],
            ];

            // Crear departamentos
            foreach ($departments as $dept) {
                Department::firstOrCreate(
                    ['code' => $dept['code']],
                    ['name' => $dept['name']]
                );
            }

            // Provincias principales (al menos una por cada departamento)
            $provinces = [
                // Lima
                ['code' => '1501', 'department_code' => '15', 'name' => 'Lima'],
                ['code' => '1507', 'department_code' => '15', 'name' => 'Huaura'],
                ['code' => '1508', 'department_code' => '15', 'name' => 'Huaral'],
                
                // Arequipa
                ['code' => '0401', 'department_code' => '04', 'name' => 'Arequipa'],
                
                // Cusco
                ['code' => '0801', 'department_code' => '08', 'name' => 'Cusco'],
                
                // La Libertad
                ['code' => '1301', 'department_code' => '13', 'name' => 'Trujillo'],
                
                // Piura
                ['code' => '2001', 'department_code' => '20', 'name' => 'Piura'],
                
                // Lambayeque
                ['code' => '1401', 'department_code' => '14', 'name' => 'Chiclayo'],
                
                // Callao
                ['code' => '0701', 'department_code' => '07', 'name' => 'Callao'],
            ];

            // Crear provincias
            foreach ($provinces as $prov) {
                $department = Department::where('code', $prov['department_code'])->first();
                if ($department) {
                    Province::firstOrCreate(
                        [
                            'code' => $prov['code'],
                            'department_id' => $department->id
                        ],
                        ['name' => $prov['name']]
                    );
                }
            }

            // Distritos principales de Lima
            $districts = [
                ['code' => '150101', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Lima'],
                ['code' => '150102', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Ancón'],
                ['code' => '150103', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Ate'],
                ['code' => '150104', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Barranco'],
                ['code' => '150105', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Breña'],
                ['code' => '150106', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Carabayllo'],
                ['code' => '150107', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Chaclacayo'],
                ['code' => '150108', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Chorrillos'],
                ['code' => '150109', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Cieneguilla'],
                ['code' => '150110', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Comas'],
                ['code' => '150111', 'department_code' => '15', 'province_code' => '1501', 'name' => 'El Agustino'],
                ['code' => '150112', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Independencia'],
                ['code' => '150113', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Jesús María'],
                ['code' => '150114', 'department_code' => '15', 'province_code' => '1501', 'name' => 'La Molina'],
                ['code' => '150115', 'department_code' => '15', 'province_code' => '1501', 'name' => 'La Victoria'],
                ['code' => '150116', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Lince'],
                ['code' => '150117', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Los Olivos'],
                ['code' => '150118', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Lurigancho'],
                ['code' => '150119', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Lurín'],
                ['code' => '150120', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Magdalena del Mar'],
                ['code' => '150121', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Miraflores'],
                ['code' => '150122', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Pachacámac'],
                ['code' => '150123', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Pucusana'],
                ['code' => '150124', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Pueblo Libre'],
                ['code' => '150125', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Puente Piedra'],
                ['code' => '150126', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Punta Hermosa'],
                ['code' => '150127', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Punta Negra'],
                ['code' => '150128', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Rímac'],
                ['code' => '150129', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Bartolo'],
                ['code' => '150130', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Borja'],
                ['code' => '150131', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Isidro'],
                ['code' => '150132', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Juan de Lurigancho'],
                ['code' => '150133', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Juan de Miraflores'],
                ['code' => '150134', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Luis'],
                ['code' => '150135', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Martín de Porres'],
                ['code' => '150136', 'department_code' => '15', 'province_code' => '1501', 'name' => 'San Miguel'],
                ['code' => '150137', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Santa Anita'],
                ['code' => '150138', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Santa María del Mar'],
                ['code' => '150139', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Santa Rosa'],
                ['code' => '150140', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Santiago de Surco'],
                ['code' => '150141', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Surquillo'],
                ['code' => '150142', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Villa El Salvador'],
                ['code' => '150143', 'department_code' => '15', 'province_code' => '1501', 'name' => 'Villa María del Triunfo'],
            ];

            // Crear distritos
            foreach ($districts as $dist) {
                $department = Department::where('code', $dist['department_code'])->first();
                $province = Province::where('code', $dist['province_code'])->first();
                
                if ($department && $province) {
                    District::firstOrCreate(
                        [
                            'code' => $dist['code'],
                            'department_id' => $department->id,
                            'province_id' => $province->id
                        ],
                        ['name' => $dist['name']]
                    );
                }
            }

        $this->command->info('✅ UbigeoSeeder ejecutado exitosamente.');
        $this->command->info('   - Departamentos: ' . Department::count());
        $this->command->info('   - Provincias: ' . Province::count());
        $this->command->info('   - Distritos: ' . District::count());
    }
}
