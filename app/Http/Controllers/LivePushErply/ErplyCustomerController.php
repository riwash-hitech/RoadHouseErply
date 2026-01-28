<?php

namespace App\Http\Controllers\LivePushErply;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LivePushErply\Services\CustomerService;
use App\Http\Controllers\LivePushErply\Services\ProductDimensionService;
use Illuminate\Http\Request;

class ErplyCustomerController extends Controller
{
    //
    protected $service;

    public function __construct(CustomerService $service){
        $this->service = $service;
    }

    public function syncCustomerToErply(){
        return $this->service->syncCustomerToErply();
    }
    
    
    public function saveCustomerAddress(){
        return $this->service->saveCustomerAddress();
    }
 


}
