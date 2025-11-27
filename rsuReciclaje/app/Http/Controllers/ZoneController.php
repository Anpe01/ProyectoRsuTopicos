<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $r)
    {
        if ($r->ajax()){
            $zones = Zone::with(['department'])
                ->orderBy('id','desc')->get();

            $data = $zones->map(function($z) {
                $ubigeo = $z->department ? $z->department->name : '—';

                $activeBadge = $z->active 
                    ? '<span class="badge bg-success">Activo</span>' 
                    : '<span class="badge bg-secondary">Inactivo</span>';

                $actions = view('zones._actions', ['z' => $z])->render();

                return [
                    'id' => $z->id,
                    'name' => $z->name,
                    'ubigeo' => $ubigeo,
                    'area_km2' => number_format($z->area_km2 ?? 0, 3),
                    'avg_waste_tb' => $z->avg_waste_tb ? number_format($z->avg_waste_tb, 2) : '—',
                    'active_badge' => $activeBadge,
                    'actions' => $actions,
                ];
            });

            return response()->json(['data' => $data]);
        }
        
        // Pasar el ID de Lambayeque a la vista
        $lambayeque = \App\Models\Department::where('name', 'Lambayeque')->first();
        return view('zones.index', [
            'lambayeque_id' => $lambayeque ? $lambayeque->id : config('app.default_department_id', 1)
        ]);
    }

    public function store(Request $r)
    {
        try {
            // Validar y preparar datos
            $data = $r->validate([
                'name'          => 'required|string|max:120',
                'department_id' => 'nullable|integer',
                'polygon'       => 'required|string',     // Viene como string JSON
                'area_km2'      => 'required',             // Validaremos manualmente
                'avg_waste_tb'  => 'nullable',
                'active'        => 'nullable',
                'description'   => 'nullable|string',
            ]);
            
            // Si no se envía department_id o es inválido, buscar Lambayeque por nombre
            if (empty($data['department_id'])) {
                $lambayeque = \App\Models\Department::where('name', 'Lambayeque')->first();
                $data['department_id'] = $lambayeque ? $lambayeque->id : null;
            }
            
            // Asegurar que district_id y province_id sean null si no se envían
            $data['district_id'] = null;
            $data['province_id'] = null;
            
            // Convertir area_km2 a float
            $areaKm2 = (float) str_replace(',', '.', $data['area_km2']);
            if ($areaKm2 <= 0 || is_nan($areaKm2)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El área calculada no es válida. Por favor, dibuje un polígono válido en el mapa.'
                ], 422);
            }
            // Validación más flexible: solo debe ser > 0
            $data['area_km2'] = $areaKm2;
            
            // Convertir avg_waste_tb a float si existe
            if (!empty($data['avg_waste_tb'])) {
                $data['avg_waste_tb'] = (float) str_replace(',', '.', $data['avg_waste_tb']);
            } else {
                $data['avg_waste_tb'] = null;
            }
            
            // Convertir active a boolean
            $data['active'] = $r->has('active') && ($r->input('active') == '1' || $r->input('active') === true || $r->input('active') === 'on');
            
            // Decodificar polygon
            $polygonJson = json_decode($data['polygon'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El polígono no es un JSON válido: ' . json_last_error_msg()
                ], 422);
            }
            $data['polygon'] = $polygonJson;

            Zone::create($data);
            return response()->json(['ok'=>true,'msg'=>'Zona creada']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear zona: ' . $e->getMessage(), [
                'request' => $r->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Zone $zone)
    {
        $zone->load(['department']);
        return response()->json([
            'id' => $zone->id,
            'name' => $zone->name,
            'department_id' => $zone->department_id,
            'polygon' => $zone->polygon,
            'area_km2' => $zone->area_km2,
            'avg_waste_tb' => $zone->avg_waste_tb,
            'active' => $zone->active,
            'description' => $zone->description,
        ]);
    }

    public function edit(Zone $zone)
    {
        $zone->load(['department']);
        return response()->json([
            'id' => $zone->id,
            'name' => $zone->name,
            'department_id' => $zone->department_id,
            'polygon' => $zone->polygon,
            'area_km2' => $zone->area_km2,
            'avg_waste_tb' => $zone->avg_waste_tb,
            'active' => $zone->active,
            'description' => $zone->description,
        ]);
    }

    public function update(Request $r, Zone $zone)
    {
        try {
            // Validar y preparar datos
            $data = $r->validate([
                'name'          => 'required|string|max:120',
                'department_id' => 'nullable|integer',
                'polygon'       => 'required|json',
                'area_km2'      => 'required|numeric|min:0.001',
                'avg_waste_tb'  => 'nullable|numeric|min:0',
                'active'        => 'sometimes|boolean',
                'description'   => 'nullable|string',
            ]);
            
            // Si no se envía department_id o es inválido, buscar Lambayeque por nombre
            if (empty($data['department_id'])) {
                $lambayeque = \App\Models\Department::where('name', 'Lambayeque')->first();
                $data['department_id'] = $lambayeque ? $lambayeque->id : null;
            }
            
            // Asegurar que district_id y province_id sean null si no se envían
            $data['district_id'] = null;
            $data['province_id'] = null;
            
            // Convertir active a boolean
            $data['active'] = $r->boolean('active');
            
            // Decodificar polygon (importante: viene como string JSON)
            $data['polygon'] = json_decode($data['polygon'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El polígono no es un JSON válido: ' . json_last_error_msg()
                ], 422);
            }

            $zone->update($data);
            return response()->json(['ok'=>true,'msg'=>'Zona actualizada']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar zona: ' . $e->getMessage(), [
                'zone_id' => $zone->id,
                'request' => $r->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(['ok'=>true]);
    }

    public function map()
    {
        // Obtener todas las zonas activas con sus polígonos y relaciones
        $zones = Zone::with('department')
            ->where('active', true)
            ->whereNotNull('polygon')
            ->get(['id', 'name', 'polygon', 'area_km2', 'description', 'department_id', 'avg_waste_tb']);
        
        return view('zones.map', compact('zones'));
    }
}
