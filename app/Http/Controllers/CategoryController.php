<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\CategoryService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    //
    protected $service;

    public function __construct(CategoryService $s)
    {
        $this->service = $s;
    }

    public function updateCategory(Request $req){
        
        return $this->service->updateMatrixCategory($req);
        // $this->service->updateVariantCategory();

    }  
    
    public function pushTempCategory(){
        return $this->service->pushTempCategory();  
    }
}
