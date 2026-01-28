<?php

namespace App\Http\Controllers\LivePushErply;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LivePushErply\Services\ErplySalesOrderService; 
use Illuminate\Http\Request;

class ErplySalesOrderController extends Controller
{
    //
    protected $service;

    public function __construct(ErplySalesOrderService $service){
        $this->service = $service;
    }

    public function pushSalesOrders(Request $req){
        return $this->service->pushSalesOrders($req);
    }
    
    public function saveDeliveryAddress(){
        return $this->service->saveDeliveryAddress();
    }
    
    public function pushRefundReturn(Request $req){
        return $this->service->pushRefundReturn($req);
    }

    public function pushRefundReturnV2(Request $req)
    {
        return $this->service->pushRefundReturnV2($req);
    }
}