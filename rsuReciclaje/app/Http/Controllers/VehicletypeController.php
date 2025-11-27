<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\VehicletypeRequest;
use App\Models\Vehicletype;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VehicletypeController extends Controller
{
    public function index()
    {
        $vehicletypes = Vehicletype::withCount('vehicles')->paginate(15);
        return view('vehicletypes.index', compact('vehicletypes'));
    }

    public function create()
    {
        return view('vehicletypes.create');
    }

    public function store(VehicletypeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data): void {
                Vehicletype::create($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede crear el tipo por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Tipo de vehículo creado correctamente.');
    }

    public function edit(Vehicletype $vehicletype)
    {
        return view('vehicletypes.edit', compact('vehicletype'));
    }

    public function update(VehicletypeRequest $request, Vehicletype $vehicletype): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($vehicletype, $data): void {
                $vehicletype->update($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede actualizar el tipo por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Tipo de vehículo actualizado correctamente.');
    }

    public function destroy(Vehicletype $vehicletype): RedirectResponse
    {
        if ($vehicletype->vehicles()->exists()) {
            return back()->with('error', 'No se puede eliminar el tipo porque tiene vehículos asociados.');
        }

        try {
            DB::transaction(function () use ($vehicletype): void {
                $vehicletype->delete();
            });
            return back()->with('success', 'Tipo de vehículo eliminado correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }
}












