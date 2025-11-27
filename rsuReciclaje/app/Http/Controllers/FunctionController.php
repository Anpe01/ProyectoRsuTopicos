<?php

namespace App\Http\Controllers;

use App\Models\FunctionModel;
use App\Models\JobFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FunctionController extends Controller
{
    public function index()
    {
        $functions = FunctionModel::orderBy('name')->paginate(10);
        return view('functions.index', compact('functions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:functions,name',
            'description' => 'nullable|string',
            'protected' => 'nullable|boolean',
        ]);
        $data['protected'] = $request->boolean('protected');
        FunctionModel::create($data);

        return back()->with('success','Función creada.');
    }

    public function update(Request $request, FunctionModel $function)
    {
        if ($function->protected) {
            return back()->with('error', 'No se puede editar una función protegida.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120|unique:functions,name,'.$function->id,
            'description' => 'nullable|string',
            'protected' => 'nullable|boolean',
        ]);
        $data['protected'] = $request->boolean('protected');
        $function->update($data);

        return back()->with('success','Función actualizada.');
    }

    public function destroy(FunctionModel $function)
    {
        if ($function->protected) {
            return back()->with('error', 'No se puede eliminar una función protegida.');
        }

        try {
            DB::transaction(function() use ($function) {
                // Si hay pivot staff_function, esto lanzará FK 23000
                $function->delete();
            });
            return back()->with('success','Función eliminada.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error','No se puede eliminar: existen dependencias.');
        }
    }

    // GET /api/functions/options?q=tex
    public function options(Request $request)
    {
        $q = trim($request->get('q', ''));
        
        // Usar JobFunction directamente (ya maneja la tabla functions)
        $query = JobFunction::query();
        
        // Filtrar por búsqueda si se proporciona
        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }
        
        // Ordenar y limitar
        $items = $query->orderBy('name')
            ->limit(50)
            ->get(['id', 'name as text']);

        return response()->json(['results' => $items]);
    }
}
