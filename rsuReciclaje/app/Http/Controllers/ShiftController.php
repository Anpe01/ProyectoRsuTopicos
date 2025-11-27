<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Http\Requests\ShiftRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::orderBy('name')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function store(ShiftRequest $request)
    {
        $data = $this->normalized($request);
        $shift = Shift::create($data);

        if ($request->ajax()) {
            return response()->json(['message' => 'Turno creado','id'=>$shift->id]);
        }
        return back()->with('success','Turno creado');
    }

    public function show(Shift $shift)
    {
        // Facilita cargar datos por AJAX al editar (JSON)
        return response()->json($shift);
    }

    public function update(ShiftRequest $request, Shift $shift)
    {
        $data = $this->normalized($request);
        $shift->update($data);

        if ($request->ajax()) {
            return response()->json(['message' => 'Turno actualizado']);
        }
        return back()->with('success','Turno actualizado');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        // Si en el futuro se relaciona con programaciones/asistencias, cambiar a restrict
        $shift->delete();
        return back()->with('success','Turno eliminado.');
    }
    private function normalized(Request $request): array
    {
        $start = Carbon::createFromFormat('H:i', $request->input('start_time'))->format('H:i:s');
        $end   = Carbon::createFromFormat('H:i', $request->input('end_time'))->format('H:i:s');

        return [
            'name'        => $request->string('name'),
            'start_time'  => $start,
            'end_time'    => $end,
            'description' => $request->input('description'),
            'active'      => $request->boolean('active'),
        ];
    }
}