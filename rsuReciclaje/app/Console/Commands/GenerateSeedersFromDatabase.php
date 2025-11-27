<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateSeedersFromDatabase extends Command
{
    protected $signature = 'db:generate-seeders {table?}';
    protected $description = 'Genera seeders basados en los datos existentes en la base de datos';

    public function handle()
    {
        $tables = [
            'employees' => 'EmployeeSeeder',
            'shifts' => 'ShiftSeeder',
            'zones' => 'ZoneSeeder',
            'vehicles' => 'VehicleSeeder',
            'personnel_groups' => 'PersonnelGroupSeeder',
            'contracts' => 'ContractSeeder',
            'vacations' => 'VacationSeeder',
            'attendances' => 'AttendanceSeeder',
            'programs' => 'ProgramSeeder',
            'runs' => 'RunSeeder',
            'run_personnel' => 'RunPersonnelSeeder',
            'run_changes' => 'RunChangeSeeder',
        ];

        $table = $this->argument('table');

        if ($table) {
            if (!isset($tables[$table])) {
                $this->error("Tabla '{$table}' no está configurada para generar seeder.");
                return 1;
            }
            $this->generateSeeder($table, $tables[$table]);
        } else {
            foreach ($tables as $tableName => $seederName) {
                $this->info("Generando seeder para tabla: {$tableName}");
                $this->generateSeeder($tableName, $seederName);
            }
        }

        $this->info('¡Seeders generados exitosamente!');
        return 0;
    }

    private function generateSeeder($tableName, $seederName)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                $this->warn("La tabla '{$tableName}' no existe. Saltando...");
                return;
            }

            $data = DB::table($tableName)->get();
            
            if ($data->isEmpty()) {
                $this->warn("La tabla '{$tableName}' está vacía. Saltando...");
                return;
            }

            $seederPath = database_path("seeders/{$seederName}.php");
            $content = $this->buildSeederContent($tableName, $seederName, $data);
            
            File::put($seederPath, $content);
            $this->info("✓ Seeder generado: {$seederName}.php ({$data->count()} registros)");
        } catch (\Exception $e) {
            $this->error("Error al generar seeder para {$tableName}: " . $e->getMessage());
        }
    }

    private function buildSeederContent($tableName, $seederName, $data)
    {
        $records = [];
        
        foreach ($data as $row) {
            $record = [];
            foreach ((array)$row as $key => $value) {
                if ($key === 'id') continue; // Saltar ID para que se auto-generen
                
                if ($value === null) {
                    $record[$key] = 'null';
                } elseif (is_numeric($value)) {
                    $record[$key] = $value;
                } elseif (is_bool($value)) {
                    $record[$key] = $value ? 'true' : 'false';
                } else {
                    $record[$key] = "'" . addslashes($value) . "'";
                }
            }
            $records[] = $record;
        }

        $recordsString = '';
        foreach ($records as $record) {
            $fields = [];
            foreach ($record as $key => $value) {
                $fields[] = "            '{$key}' => {$value}";
            }
            $recordsString .= "        [\n" . implode(",\n", $fields) . "\n        ],\n";
        }

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$seederName} extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \$records = [
{$recordsString}        ];

        foreach (\$records as \$record) {
            DB::table('{$tableName}')->insert(\$record);
        }
    }
}

PHP;
    }
}
