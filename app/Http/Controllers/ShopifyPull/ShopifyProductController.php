<?php

namespace App\Http\Controllers\ShopifyPull;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopifyPull\Services\ShopifyProductService;
use Exception;

class ShopifyProductController extends Controller
{
    //
    protected $shopifyProductService;
    // milan update test
    public function __construct(ShopifyProductService $shopifyProductService){
        $this->shopifyProductService = $shopifyProductService;
    }

    public function getMatrixProducts(Request $req){
        try{
            return $this->shopifyProductService->getMatrixProducts($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }

    public function getVariationProducts(Request $req){
        try{
            return $this->shopifyProductService->getVariationProducts($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }

    public function getProductSoh(Request $req){
        try{
            return $this->shopifyProductService->getProductSoh($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }
    
    public function getProductAndDelete(Request $req){
        try{
            return $this->shopifyProductService->getProductAndDelete($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }

    public function getCustomers(Request $req){
        try{
            return $this->shopifyProductService->getCustomers($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }


}
