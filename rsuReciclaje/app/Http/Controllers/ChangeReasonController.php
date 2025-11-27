<?php

namespace App\Http\Controllers;

use App\Models\ChangeReason;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChangeReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $changeReasons = ChangeReason::orderBy('is_predefined', 'desc')
            ->orderBy('name')
            ->paginate(15);
        return view('change-reasons.index', compact('changeReasons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('change-reasons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:change_reasons,name'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'nombre',
            'description' => 'descripción',
            'active' => 'activo',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['is_predefined'] = false; // Los usuarios no pueden crear motivos predefinidos

        ChangeReason::create($data);

        return redirect()->route('change-reasons.index')
            ->with('success', 'Motivo de cambio creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChangeReason $changeReason): View
    {
        return view('change-reasons.show', compact('changeReason'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChangeReason $changeReason): View
    {
        return view('change-reasons.edit', compact('changeReason'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChangeReason $changeReason): RedirectResponse
    {
        // No permitir editar motivos predefinidos
        if ($changeReason->is_predefined) {
            return back()->with('error', 'No se puede editar un motivo predefinido del sistema.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('change_reasons', 'name')->ignore($changeReason->id)],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'nombre',
            'description' => 'descripción',
            'active' => 'activo',
        ]);

        $data['active'] = $request->boolean('active', true);

        $changeReason->update($data);

        return redirect()->route('change-reasons.index')
            ->with('success', 'Motivo de cambio actualizado correctamente.');
    }

    /**
     * Obtener motivos activos (API)
     */
    public function getActive(Request $request)
    {
        $reasons = ChangeReason::where('active', true)
            ->orderBy('is_predefined', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'is_predefined']);
        
        return response()->json($reasons);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChangeReason $changeReason): RedirectResponse
    {
        // No permitir eliminar motivos predefinidos
        if ($changeReason->is_predefined) {
            return back()->with('error', 'No se puede eliminar un motivo predefinido del sistema.');
        }

        // Verificar si está siendo usado en run_changes
        $usageCount = DB::table('run_changes')
            ->where('notes', 'LIKE', '%' . $changeReason->name . '%')
            ->count();

        if ($usageCount > 0) {
            return back()->with('error', 'No se puede eliminar el motivo porque está siendo utilizado en ' . $usageCount . ' cambio(s).');
        }

        $changeReason->delete();

        return redirect()->route('change-reasons.index')
            ->with('success', 'Motivo de cambio eliminado correctamente.');
    }
}
