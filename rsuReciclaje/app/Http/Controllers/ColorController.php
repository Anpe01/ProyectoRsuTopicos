<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ColorRequest;
use App\Models\Color;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::withCount('vehicles')->paginate(15);
        return view('colors.index', compact('colors'));
    }

    public function create()
    {
        return view('colors.create');
    }

    public function store(ColorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data): void {
                Color::create($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede crear el color por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Color creado correctamente.');
    }

    public function edit(Color $color)
    {
        return view('colors.edit', compact('color'));
    }

    public function update(ColorRequest $request, Color $color): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($color, $data): void {
                $color->update($data);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede actualizar el color por restricciones de clave foránea.');
            }
            throw $e;
        }

        return back()->with('success', 'Color actualizado correctamente.');
    }

    public function destroy(Color $color): RedirectResponse
    {
        if ($color->vehicles()->exists()) {
            return back()->with('error', 'No se puede eliminar el color porque tiene vehículos asociados.');
        }

        try {
            DB::transaction(function () use ($color): void {
                $color->delete();
            });
            return back()->with('success', 'Color eliminado correctamente.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'No se puede eliminar por restricciones de clave foránea.');
            }
            throw $e;
        }
    }
}












