<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //
    protected $service;

    public function __construct(CustomerService $cs)
    {
        $this->service = $cs;        
    }

    public function create(Request $req){
        return $this->service->saveCustomer($req);
    }
}
