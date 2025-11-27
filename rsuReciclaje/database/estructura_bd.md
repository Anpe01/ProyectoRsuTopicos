# Estructura de Base de Datos - Sistema RSU Reciclaje

## Resumen de Tablas

| Tabla | Descripción | Registros |
|-------|--------------|----------|
| `departments` | Departamentos | 25 |
| `provinces` | Provincias | 9 |
| `districts` | Distritos | 43 |
| `zones` | Zonas | 1 |
| `zonecoords` | Coordenadas de Zonas | 0 |
| `brands` | Marcas | 3 |
| `brandmodels` | Modelos | 1 |
| `vehicletypes` | Tipos de Vehículos | 1 |
| `colors` | Colores | 1 |
| `vehicles` | Vehículos | 2 |
| `vehicleimages` | Imágenes de Vehículos | 0 |
| `employeetypes` | Tipos de Empleados | 0 |
| `employees` | Empleados | 7 |
| `functions` | Funciones | 3 |
| `staff_function` | Funciones de Personal | 0 |
| `contracts` | Contratos | 7 |
| `attendances` | Asistencias | 6 |
| `vacations` | Vacaciones | 0 |
| `shifts` | Turnos | 4 |
| `personnel_groups` | Grupos de Personal | 3 |
| `programs` | Programaciones | 4 |
| `runs` | Recorridos | 11 |
| `run_personnel` | Personal de Recorridos | 33 |
| `run_changes` | Cambios de Recorridos | 22 |

## Relaciones Principales

### Gestión de Personal
- **employees** → **contracts** (Contratos de empleados)
- **employees** → **attendances** (Asistencias)
- **employees** → **vacations** (Vacaciones)
- **employees** → **personnel_groups** (Grupos de personal)
- **employees** → **run_personnel** (Asignación a recorridos)

### Gestión de Vehículos
- **brands** → **brandmodels** → **vehicles**
- **vehicletypes** → **vehicles**
- **colors** → **vehicles**
- **vehicles** → **vehicleimages**

### Programación y Recorridos
- **zones** → **programs** → **runs**
- **personnel_groups** → **runs**
- **runs** → **run_personnel** (Personal asignado)
- **runs** → **run_changes** (Historial de cambios)

### Ubicación Geográfica
- **departments** → **provinces** → **districts**
- **zones** → **districts**
- **zones** → **zonecoords** (Coordenadas)

