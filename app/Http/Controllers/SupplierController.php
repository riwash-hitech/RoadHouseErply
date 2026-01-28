<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //

    protected $service;

    public function __construct(SupplierService $cs)
    {
        $this->service = $cs;        
    }

    public function create(Request $req){
        return $this->service->saveSupplier($req);
    }
}
