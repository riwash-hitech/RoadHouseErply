<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\Coupon;

class GetCouponService{

    protected $coupon;
    protected $api;

    public function __construct(Coupon $c, EAPIService $api){
        $this->coupon = $c;
        $this->api = $api;
    }

    public function saveUpdate($coupons){

        foreach($coupons as $c){
            $this->saveUpdateCoupon($c);
        }

        return response()->json(['status'=>200, 'message'=>"Cashin fetched Successfully."]);
    }

    protected function saveUpdateCoupon($product){

        $this->coupon->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "couponID"  =>  $product['couponID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    'couponID' => @$product['couponID'],
                    'campaignID' => @$product['campaignID'],
                    'warehouseID' => @$product['warehouseID'],
                    'issuedFromDate' => @$product['issuedFromDate'],
                    'issuedUntilDate' => @$product['issuedUntilDate'],
                    'name' => @$product['name'],
                    'code' => @$product['code'],
                    'printedAutomaticallyInPOS' => @$product['printedAutomaticallyInPOS'],
                    'threshold' => @$product['threshold'],
                    'measure' => @$product['measure'],
                    'thresholdType' => @$product['thresholdType'],
                    'promptCashier' => @$product['promptCashier'],
                    'printingCostInRewardPoints' => @$product['printingCostInRewardPoints'],
                    'print' => @$product['print'],
                    'description' => @$product['description'], 
                    "added" =>  date('Y-m-d H:i:s', @$product['added']),
                    "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',@$product['lastModified']) : '0000-00-00 00:00',
                     
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->coupon->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
