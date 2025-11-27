<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\JobFunction;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('jobFunction')->orderBy('last_name')->orderBy('first_name')->get();
        return view('employees.index', compact('employees'));
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();

        // Generar PIN de 6 dígitos único si no se envía
        if (empty($data['pin'])) {
            do {
                $pin = (string) random_int(100000, 999999);
            } while (Employee::where('pin', $pin)->exists());
            $data['pin'] = $pin;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('employees/photos','public');
        }

        $data['active'] = $request->boolean('active');
        Employee::create($data);
        return back()->with('success','Empleado creado correctamente.');
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        // Si no mandan password, no cambiar
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Si no mandan pin, conservar el existente
        if (!array_key_exists('pin', $data) || $data['pin'] === null || $data['pin'] === '') {
            unset($data['pin']);
        } else {
            // asegurar unicidad manual si hiciera falta
            if (Employee::where('pin', $data['pin'])->where('id','<>',$employee->id)->exists()) {
                return back()->with('error','El PIN ya está en uso por otro empleado.')->withInput();
            }
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees/photos','public');
        }

        $data['active'] = $request->boolean('active');
        $employee->update($data);
        return back()->with('success','Empleado actualizado.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        $employee->delete();
        return back()->with('success','Empleado eliminado.');
    }
}
