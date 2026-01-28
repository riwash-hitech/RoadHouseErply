<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\CashinsApiService;
use App\Http\Controllers\Paei\API\APIServices\CouponApiService;
use App\Http\Controllers\Paei\API\APIServices\CurrencyApiService; 
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    //
    protected $coupon; 

    public function __construct(CouponApiService $mp ){
        $this->coupon = $mp;
       
    }

    public function getCoupons(Request $req){
         
        return $this->coupon->getCoupons($req);

    }
}
