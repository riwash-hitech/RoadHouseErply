<?php

namespace App\Http\Controllers\ShopifyPull;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopifyPull\Services\ShopifyOrderService;
use Exception;

class ShopifyOrderController extends Controller
{
    protected $shopifyOrderService;
    public function __construct(ShopifyOrderService $shopifyOrderService){
        $this->shopifyOrderService = $shopifyOrderService;
    }
    public function getOrders(Request $req){
        try{
            return $this->shopifyOrderService->getOrders($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }
    public function getRefund(Request $req){
        try{
            return $this->shopifyOrderService->getRefund($req);
        }catch(Exception $e){
            return response($e->getMessage());
        }
        
    }
}
