<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ZonecoordRequest;
use App\Models\Zonecoord;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZonecoordController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'sequence' => 'required|integer|min:1',
        ]);
        
        try {
            DB::transaction(function () use ($data): void {
                Zonecoord::create($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede crear coordenada por restricciones de clave foránea.');
            }
            throw $e;
        }
        return back()->with('success', 'Coordenada creada correctamente.');
    }

    public function update(Request $request, Zonecoord $zonecoord): RedirectResponse
    {
        $data = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'sequence' => 'required|integer|min:1',
        ]);
        
        try {
            DB::transaction(function () use ($zonecoord, $data): void {
                $zonecoord->update($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede actualizar la coordenada por restricciones de clave foránea.');
            }
            throw $e;
        }
        return back()->with('success', 'Coordenada actualizada correctamente.');
    }

    public function destroy(Zonecoord $zonecoord): RedirectResponse
    {
        try {
            DB::transaction(function () use ($zonecoord): void {
                $zonecoord->delete();
            });
            return back()->with('success', 'Coordenada eliminada correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }
}




