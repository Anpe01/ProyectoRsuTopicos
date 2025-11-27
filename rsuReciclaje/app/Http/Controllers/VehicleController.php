<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Brandmodel;
use App\Models\Color;
use App\Models\Vehicle;
use App\Models\Vehicleimage;
use App\Models\Vehicletype;
use App\Http\Requests\VehicleRequest;
use App\Http\Requests\VehicleimageRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['brand', 'model', 'brandmodel', 'type', 'color', 'logoBrand'])->latest()->paginate(10);
        
        // Datos para selects
        $brands = Brand::orderBy('name')->get();
        $types = Vehicletype::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        
        // Obtener todas las marcas con logos para el selector
        $brandsWithLogos = Brand::whereNotNull('logo')->orderBy('name')->get();
        
        return view('vehicles.index', compact('vehicles', 'brands', 'types', 'colors', 'brandsWithLogos'));
    }

    public function create()
    {
        return view('vehicles.create', [
            'brands' => Brand::orderBy('name')->get(),
            'models' => Brandmodel::orderBy('name')->get(),
            'types' => Vehicletype::orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    private function rules(?Vehicle $vehicle = null): array
    {
        $currentYear = (int) date('Y');
        $ignoreId = $vehicle?->id;
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', Rule::unique('vehicles', 'code')->ignore($ignoreId)],
            'plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($ignoreId), 'regex:/^(?:[A-Z0-9]{6}|[A-Z]{2}-\d{4}|[A-Z]{3}-\d{3})$/i'],
            'year' => ['required', 'integer', 'between:1950,'.$currentYear],
            'load_capacity' => ['nullable', 'numeric'],
            'description' => ['required', 'string'],
            'status' => ['required', 'integer'],
            'brand_id' => ['required', 'exists:brands,id'],
            'brandmodel_id' => ['required', 'exists:brandmodels,id'],
            'logo_id' => ['nullable', 'exists:brands,id'],
            'type_id' => ['required', 'exists:vehicletypes,id'],
            'color_id' => ['required', 'exists:colors,id'],
        ];
    }

    /**
     * Obtener logos disponibles (marcas con logos)
     */
    public function getAvailableLogos()
    {
        $brandsWithLogos = Brand::whereNotNull('logo')
            ->orderBy('name')
            ->get(['id', 'name', 'logo'])
            ->map(function($brand) {
                // Extraer solo el nombre del archivo de la ruta completa
                $filename = basename($brand->logo);
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo_url' => route('brands.logos.serve', ['filename' => $filename]),
                ];
            });
        
        return response()->json([
            'ok' => true,
            'logos' => $brandsWithLogos,
        ]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status') ? 1 : 0;

        try {
            DB::transaction(function () use ($data): void {
                Vehicle::create($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede crear por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Vehículo creado correctamente.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
            'brands' => Brand::orderBy('name')->get(),
            'models' => Brandmodel::orderBy('name')->get(),
            'types' => Vehicletype::orderBy('name')->get(),
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status') ? 1 : 0;

        try {
            DB::transaction(function () use ($vehicle, $data): void {
                $vehicle->update($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede actualizar por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        try {
            DB::transaction(function () use ($vehicle): void {
                $vehicle->delete();
            });
            return back()->with('success', 'Vehículo eliminado correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }

    public function imagesStore(VehicleimageRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($vehicle, $data, $request): void {
                $path = $request->file('image')->store('vehicles', 'public');
                $isProfile = (bool) ($data['profile'] ?? false);
                if ($isProfile) {
                    Vehicleimage::where('vehicle_id', $vehicle->id)->update(['profile' => false]);
                }
                Vehicleimage::create([
                    'vehicle_id' => $vehicle->id,
                    'image' => $path,
                    'profile' => $isProfile,
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede subir la imagen por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Imagen subida correctamente.');
    }

    public function imagesProfile(Vehicle $vehicle, Vehicleimage $image): RedirectResponse
    {
        if ($image->vehicle_id !== $vehicle->id) {
            return back()->with('error', 'La imagen no pertenece al vehículo.');
        }

        DB::transaction(function () use ($vehicle, $image): void {
            Vehicleimage::where('vehicle_id', $vehicle->id)->update(['profile' => false]);
            $image->update(['profile' => true]);
        });

        return back()->with('success', 'Imagen marcada como perfil.');
    }
}


