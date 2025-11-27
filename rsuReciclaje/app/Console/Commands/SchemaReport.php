<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchemaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera reporte del esquema de base de datos desde MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Analizando esquema de base de datos...');
        
        try {
            $database = config('database.connections.mysql.database');
            $this->info("📊 Base de datos: {$database}");
            
            // Consultar todas las tablas y columnas
            $tables = DB::select("
                SELECT 
                    TABLE_NAME,
                    COLUMN_NAME,
                    DATA_TYPE,
                    IS_NULLABLE,
                    COLUMN_DEFAULT,
                    COLUMN_KEY,
                    EXTRA
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                ORDER BY TABLE_NAME, ORDINAL_POSITION
            ", [$database]);
            
            // Organizar datos por tabla
            $schema = [];
            foreach ($tables as $column) {
                $tableName = $column->TABLE_NAME;
                if (!isset($schema[$tableName])) {
                    $schema[$tableName] = [];
                }
                
                $schema[$tableName][] = [
                    'name' => $column->COLUMN_NAME,
                    'type' => $column->DATA_TYPE,
                    'nullable' => $column->IS_NULLABLE === 'YES',
                    'default' => $column->COLUMN_DEFAULT,
                    'key' => $column->COLUMN_KEY,
                    'extra' => $column->EXTRA
                ];
            }
            
            // Guardar en archivo JSON
            Storage::put('schema.json', json_encode($schema, JSON_PRETTY_PRINT));
            $this->info('💾 Esquema guardado en storage/app/schema.json');
            
            // Mostrar resumen
            $this->displaySummary($schema);
            
            // Crear mapa de sinónimos
            $this->createSchemaMap($schema);
            
        } catch (\Exception $e) {
            $this->error('❌ Error al leer el esquema: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function displaySummary($schema)
    {
        $this->info("\n📋 RESUMEN DEL ESQUEMA:");
        $this->info("=====================");
        
        $expectedTables = [
            'departments', 'provinces', 'districts',
            'brands', 'brandmodels', 'vehicletypes', 'colors', 'vehicles', 'vehicleimages',
            'staff', 'functions', 'staff_function', 'contracts', 'attendances', 'vacations',
            'zones', 'zonecoords',
            'shifts', 'programs', 'program_personnel', 'runs', 'run_personnel'
        ];
        
        $foundTables = array_keys($schema);
        $missingTables = array_diff($expectedTables, $foundTables);
        $extraTables = array_diff($foundTables, $expectedTables);
        
        $this->info("✅ Tablas encontradas: " . count($foundTables));
        foreach ($foundTables as $table) {
            $columnCount = count($schema[$table]);
            $this->line("   • {$table} ({$columnCount} columnas)");
        }
        
        if (!empty($missingTables)) {
            $this->warn("\n⚠️  Tablas esperadas no encontradas:");
            foreach ($missingTables as $table) {
                $this->line("   • {$table}");
            }
        }
        
        if (!empty($extraTables)) {
            $this->info("\n➕ Tablas adicionales encontradas:");
            foreach ($extraTables as $table) {
                $this->line("   • {$table}");
            }
        }
        
        // Detectar posibles sinónimos
        $this->detectSynonyms($foundTables);
    }
    
    private function detectSynonyms($foundTables)
    {
        $synonymMap = [
            'staff' => ['employees', 'personal'],
            'brandmodels' => ['models', 'vehicle_models'],
            'vehicletypes' => ['types', 'vehicle_types'],
            'vehicleimages' => ['vehicle_images'],
            'zonecoords' => ['zone_coords'],
            'program_personnel' => ['program_staff'],
            'run_personnel' => ['run_staff']
        ];
        
        $detectedSynonyms = [];
        
        foreach ($synonymMap as $expected => $possible) {
            foreach ($possible as $synonym) {
                if (in_array($synonym, $foundTables)) {
                    $detectedSynonyms[$expected] = $synonym;
                    break;
                }
            }
        }
        
        if (!empty($detectedSynonyms)) {
            $this->warn("\n🔄 Sinónimos detectados:");
            foreach ($detectedSynonyms as $expected => $actual) {
                $this->line("   • {$expected} → {$actual}");
            }
        }
    }
    
    private function createSchemaMap($schema)
    {
        $synonymMap = [
            'staff' => ['employees', 'personal'],
            'brandmodels' => ['models', 'vehicle_models'],
            'vehicletypes' => ['types', 'vehicle_types'],
            'vehicleimages' => ['vehicle_images'],
            'zonecoords' => ['zone_coords'],
            'program_personnel' => ['program_staff'],
            'run_personnel' => ['run_staff']
        ];
        
        $foundTables = array_keys($schema);
        $actualMap = [];
        
        foreach ($synonymMap as $expected => $possible) {
            foreach ($possible as $synonym) {
                if (in_array($synonym, $foundTables)) {
                    $actualMap[$expected] = $synonym;
                    break;
                }
            }
            // Si no se encuentra sinónimo, usar el nombre esperado
            if (!isset($actualMap[$expected])) {
                $actualMap[$expected] = $expected;
            }
        }
        
        // Agregar todas las tablas encontradas
        foreach ($foundTables as $table) {
            if (!in_array($table, array_values($actualMap))) {
                $actualMap[$table] = $table;
            }
        }
        
        $configContent = "<?php\n\nreturn " . var_export($actualMap, true) . ";\n";
        file_put_contents(config_path('schema_map.php'), $configContent);
        
        $this->info('🗺️  Mapa de sinónimos creado en config/schema_map.php');
    }
}
