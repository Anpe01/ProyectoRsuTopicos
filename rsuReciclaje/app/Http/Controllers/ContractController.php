<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Department;
use App\Http\Requests\ContractRequest;

class ContractController extends Controller
{
    public function index()
    {
        $contracts   = Contract::with(['employee','department'])
                        ->orderByDesc('start_date')->get();
        $employees   = Employee::orderBy('last_name')->orderBy('first_name')->get(['id','first_name','last_name']);
        $departments = Department::orderBy('name')->get(['id','name']);
        $types = ['temporal','nombrado','a tiempo completo'];

        return view('contracts.index', compact('contracts','employees','departments','types'));
    }

    public function store(ContractRequest $request)
    {
        $data = $request->validated();
        Contract::create($data);
        return back()->with('success','Contrato creado correctamente.');
    }

    public function update(ContractRequest $request, Contract $contract)
    {
        $data = $request->validated();
        $contract->update($data);
        return back()->with('success','Contrato actualizado.');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return back()->with('success','Contrato eliminado.');
    }
}
