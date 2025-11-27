<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\District;

class UbigeoController extends Controller
{
    public function provinces($departmentId){
        return response()->json(
            Province::where('department_id',$departmentId)
                ->orderBy('name')
                ->get(['id','name'])
        );
    }

    public function districts($provinceId){
        return response()->json(
            District::where('province_id',$provinceId)
                ->orderBy('name')
                ->get(['id','name'])
        );
    }
}
