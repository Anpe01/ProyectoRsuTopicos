<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount(['models', 'vehicles'])->paginate(15);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:brands,name'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
        ], [], [
            'name' => 'nombre',
            'logo' => 'logo',
            'description' => 'descripción',
        ]);

        // Guardar logo si se subió
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
        }

        Brand::create($data);

        return back()->with('success', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('brands', 'name')->ignore($brand->id)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
        ], [], [
            'name' => 'nombre',
            'logo' => 'logo',
            'description' => 'descripción',
        ]);

        DB::transaction(function () use ($brand, $data, $request): void {
            // Si se sube un nuevo logo, eliminar el anterior
            if ($request->hasFile('logo')) {
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }
                $data['logo'] = $request->file('logo')->store('brands/logos', 'public');
            } else {
                // Mantener el logo existente si no se sube uno nuevo
                unset($data['logo']);
            }

            $brand->update($data);
        });

        return back()->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->models()->exists() || $brand->vehicles()->exists()) {
            return back()->with('error', 'No se puede eliminar la marca porque tiene modelos o vehículos asociados.');
        }

        try {
            DB::transaction(function () use ($brand): void {
                // Eliminar archivo de logo si existe
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }
                $brand->delete();
            });
            return back()->with('success', 'Marca eliminada correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }

    /**
     * Servir imagen de logo de marca
     */
    public function serveLogo($filename)
    {
        $path = 'brands/logos/' . $filename;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Logo no encontrado');
        }
        
        $file = Storage::disk('public')->get($path);
        $type = Storage::disk('public')->mimeType($path);
        
        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }
}




