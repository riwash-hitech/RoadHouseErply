<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CashinsApiService;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService;
use App\Http\Controllers\Paei\API\APIServices\EmployeeApiService;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    //
    protected $employee; 

    public function __construct(EmployeeApiService $mp ){
        $this->employee = $mp;
       
    }

    public function getEmployees(Request $req){
         
        return $this->employee->getEmployees($req);

    }
}
