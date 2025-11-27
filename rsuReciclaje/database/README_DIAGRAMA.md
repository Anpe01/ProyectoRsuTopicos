# Diagrama de Estructura de Base de Datos

Este directorio contiene los archivos necesarios para visualizar la estructura completa de la base de datos del Sistema RSU Reciclaje.

## Archivos Disponibles

### 1. `visualizar_diagrama.html`
**Archivo principal para visualizar el diagrama ER**

Este archivo HTML contiene un diagrama interactivo que puedes abrir directamente en tu navegador.

**Cómo usar:**
1. Abre el archivo `visualizar_diagrama.html` en tu navegador web (Chrome, Firefox, Edge, etc.)
2. Espera a que el diagrama se cargue completamente
3. Puedes hacer zoom con la rueda del mouse
4. Usa los botones "Exportar como PNG" o "Exportar como SVG" para guardar una imagen

**Ventajas:**
- ✅ No requiere instalación de software adicional
- ✅ Interactivo y fácil de usar
- ✅ Permite exportar directamente a PNG o SVG
- ✅ Funciona en cualquier navegador moderno

### 2. `diagrama_er.md`
**Diagrama en formato Mermaid**

Este archivo contiene el código del diagrama en formato Mermaid, que puedes usar en:
- GitHub (se renderiza automáticamente)
- GitLab (se renderiza automáticamente)
- VS Code con extensión "Markdown Preview Mermaid Support"
- https://mermaid.live/ (copiar y pegar el contenido)

### 3. `estructura_bd.md`
**Resumen textual de la estructura**

Este archivo contiene:
- Tabla resumen con todas las tablas y cantidad de registros
- Descripción de las relaciones principales
- Organizado por módulos funcionales

## Métodos para Generar Imagen

### Método 1: Usar el HTML (Recomendado)
1. Abre `visualizar_diagrama.html` en tu navegador
2. Espera a que cargue el diagrama
3. Haz clic en "Exportar como PNG"
4. La imagen se descargará automáticamente

### Método 2: Usar Mermaid Live Editor
1. Ve a https://mermaid.live/
2. Abre el archivo `diagrama_er.md`
3. Copia el contenido del bloque de código Mermaid
4. Pégalo en el editor
5. Haz clic en "Actions" → "Download PNG" o "Download SVG"

### Método 3: Usar mermaid-cli (Línea de comandos)
Si tienes Node.js instalado:

```bash
npm install -g @mermaid-js/mermaid-cli
mmdc -i database/diagrama_er.md -o diagrama_er.png
```

### Método 4: Usar VS Code
1. Instala la extensión "Markdown Preview Mermaid Support"
2. Abre `diagrama_er.md`
3. Abre la vista previa (Ctrl+Shift+V)
4. Captura de pantalla del diagrama renderizado

## Estructura de la Base de Datos

### Módulos Principales

#### 1. Gestión de Personal
- `employees` - Empleados del sistema
- `employeetypes` - Tipos de empleados
- `functions` - Funciones/cargos
- `contracts` - Contratos laborales
- `attendances` - Registro de asistencias
- `vacations` - Vacaciones
- `staff_function` - Relación muchos a muchos entre empleados y funciones

#### 2. Gestión de Vehículos
- `brands` - Marcas de vehículos
- `brandmodels` - Modelos de vehículos
- `vehicletypes` - Tipos de vehículos
- `colors` - Colores
- `vehicles` - Vehículos del sistema
- `vehicleimages` - Imágenes de vehículos

#### 3. Gestión de Ubicaciones
- `departments` - Departamentos
- `provinces` - Provincias
- `districts` - Distritos
- `zones` - Zonas de recolección
- `zonecoords` - Coordenadas de zonas

#### 4. Programación y Recorridos
- `shifts` - Turnos de trabajo
- `personnel_groups` - Grupos de personal predefinidos
- `programs` - Programaciones masivas
- `runs` - Recorridos individuales
- `run_personnel` - Personal asignado a recorridos
- `run_changes` - Historial de cambios en recorridos

## Relaciones Clave

### Relaciones Uno a Muchos
- `departments` → `provinces` → `districts`
- `brands` → `brandmodels` → `vehicles`
- `employees` → `contracts`, `attendances`, `vacations`
- `zones` → `programs` → `runs`
- `runs` → `run_personnel`, `run_changes`

### Relaciones Muchos a Muchos
- `employees` ↔ `functions` (a través de `staff_function`)
- `runs` ↔ `employees` (a través de `run_personnel`)

## Notas Importantes

- Todas las tablas incluyen `created_at` y `updated_at` (timestamps)
- Las claves foráneas están definidas con restricciones de integridad referencial
- Algunas relaciones permiten valores NULL para flexibilidad
- El sistema usa `employees` como tabla principal de personal (anteriormente `staff`)

## Actualización del Diagrama

Si necesitas actualizar el diagrama después de cambios en la base de datos:

```bash
php database/generar_diagrama.php
```

Este comando regenerará el archivo `estructura_bd.md` con los datos actuales.




