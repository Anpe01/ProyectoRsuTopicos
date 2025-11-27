<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Brandmodel;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BrandmodelController extends Controller
{
    public function index()
    {
        $brandmodels = Brandmodel::with('brand')->withCount('vehicles')->paginate(15);
        $brands = Brand::orderBy('name')->get();
        return view('brandmodels.index', compact('brandmodels', 'brands'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        return view('brandmodels.create', compact('brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ], [], [
            'brand_id' => 'marca',
            'name' => 'nombre',
            'description' => 'descripción',
        ]);

        // Unique compuesto brand_id + name
        $request->validate([
            'name' => [Rule::unique('brandmodels')->where(fn ($q) => $q->where('brand_id', $data['brand_id']))],
        ], [
            'name.unique' => 'Ya existe un modelo con ese nombre para la marca seleccionada.',
        ]);

        Brandmodel::create($data);
        return back()->with('success', 'Modelo creado correctamente.');
    }

    public function edit(Brandmodel $brandmodel)
    {
        $brands = Brand::orderBy('name')->get();
        return view('brandmodels.edit', compact('brandmodel', 'brands'));
    }

    public function update(Request $request, Brandmodel $brandmodel): RedirectResponse
    {
        $data = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
        ], [], [
            'brand_id' => 'marca',
            'name' => 'nombre',
            'description' => 'descripción',
        ]);

        // Unique compuesto ignorando el propio registro
        $request->validate([
            'name' => [Rule::unique('brandmodels')->where(fn ($q) => $q->where('brand_id', $data['brand_id']))->ignore($brandmodel->id)],
        ], [
            'name.unique' => 'Ya existe un modelo con ese nombre para la marca seleccionada.',
        ]);

        DB::transaction(function () use ($brandmodel, $data): void {
            $brandmodel->update($data);
        });

        return back()->with('success', 'Modelo actualizado correctamente.');
    }

    public function destroy(Brandmodel $brandmodel): RedirectResponse
    {
        if ($brandmodel->vehicles()->exists()) {
            return back()->with('error', 'No se puede eliminar el modelo porque tiene vehículos asociados.');
        }

        try {
            DB::transaction(function () use ($brandmodel): void {
                $brandmodel->delete();
            });
            return back()->with('success', 'Modelo eliminado correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }

    public function byBrand(Brand $brand)
    {
        return response()->json(
            $brand->brandmodels()->orderBy('name')->get(['id', 'name'])
        );
    }
}



