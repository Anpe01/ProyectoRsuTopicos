<?php

/**
 * Script para generar un diagrama de la estructura de la base de datos
 * 
 * Uso: php database/generar_diagrama.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Generando diagrama de estructura de base de datos...\n\n";

$tables = [
    'departments' => 'Departamentos',
    'provinces' => 'Provincias',
    'districts' => 'Distritos',
    'zones' => 'Zonas',
    'zonecoords' => 'Coordenadas de Zonas',
    'brands' => 'Marcas',
    'brandmodels' => 'Modelos',
    'vehicletypes' => 'Tipos de Vehículos',
    'colors' => 'Colores',
    'vehicles' => 'Vehículos',
    'vehicleimages' => 'Imágenes de Vehículos',
    'employeetypes' => 'Tipos de Empleados',
    'employees' => 'Empleados',
    'functions' => 'Funciones',
    'staff_function' => 'Funciones de Personal',
    'contracts' => 'Contratos',
    'attendances' => 'Asistencias',
    'vacations' => 'Vacaciones',
    'shifts' => 'Turnos',
    'personnel_groups' => 'Grupos de Personal',
    'programs' => 'Programaciones',
    'runs' => 'Recorridos',
    'run_personnel' => 'Personal de Recorridos',
    'run_changes' => 'Cambios de Recorridos',
];

$output = "# Estructura de Base de Datos - Sistema RSU Reciclaje\n\n";
$output .= "## Resumen de Tablas\n\n";
$output .= "| Tabla | Descripción | Registros |\n";
$output .= "|-------|--------------|----------|\n";

foreach ($tables as $table => $description) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        $output .= "| `$table` | $description | $count |\n";
    }
}

$output .= "\n## Relaciones Principales\n\n";
$output .= "### Gestión de Personal\n";
$output .= "- **employees** → **contracts** (Contratos de empleados)\n";
$output .= "- **employees** → **attendances** (Asistencias)\n";
$output .= "- **employees** → **vacations** (Vacaciones)\n";
$output .= "- **employees** → **personnel_groups** (Grupos de personal)\n";
$output .= "- **employees** → **run_personnel** (Asignación a recorridos)\n\n";

$output .= "### Gestión de Vehículos\n";
$output .= "- **brands** → **brandmodels** → **vehicles**\n";
$output .= "- **vehicletypes** → **vehicles**\n";
$output .= "- **colors** → **vehicles**\n";
$output .= "- **vehicles** → **vehicleimages**\n\n";

$output .= "### Programación y Recorridos\n";
$output .= "- **zones** → **programs** → **runs**\n";
$output .= "- **personnel_groups** → **runs**\n";
$output .= "- **runs** → **run_personnel** (Personal asignado)\n";
$output .= "- **runs** → **run_changes** (Historial de cambios)\n\n";

$output .= "### Ubicación Geográfica\n";
$output .= "- **departments** → **provinces** → **districts**\n";
$output .= "- **zones** → **districts**\n";
$output .= "- **zones** → **zonecoords** (Coordenadas)\n\n";

file_put_contents(__DIR__ . '/estructura_bd.md', $output);

echo "✓ Diagrama generado en: database/estructura_bd.md\n";
echo "✓ Diagrama Mermaid disponible en: database/diagrama_er.md\n\n";
echo "Para visualizar el diagrama Mermaid, puedes:\n";
echo "1. Usar https://mermaid.live/ (copiar el contenido de diagrama_er.md)\n";
echo "2. Usar extensiones de VS Code como 'Markdown Preview Mermaid Support'\n";
echo "3. Usar herramientas como mermaid-cli para generar imágenes\n";




