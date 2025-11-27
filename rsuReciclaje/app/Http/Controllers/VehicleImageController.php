<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Vehicleimage;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleImageController extends Controller
{
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'profile' => ['nullable', 'boolean'],
        ], [], [
            'image' => 'imagen',
            'profile' => 'perfil',
        ]);

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

    public function destroy(Vehicle $vehicle, Vehicleimage $image): RedirectResponse
    {
        if ($image->vehicle_id !== $vehicle->id) {
            return back()->with('error', 'La imagen no pertenece al vehículo.');
        }

        try {
            DB::transaction(function () use ($image): void {
                // Eliminar archivo físico
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                
                $image->delete();
            });
            
            return back()->with('success', 'Imagen eliminada correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }

    public function markProfile(Vehicle $vehicle, Vehicleimage $image): RedirectResponse
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











