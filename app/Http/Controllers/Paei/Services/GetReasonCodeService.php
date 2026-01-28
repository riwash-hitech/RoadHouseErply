<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\MatrixDimension;
use App\Models\PAEI\MatrixDimensionVariation;
use App\Models\PAEI\ReasonCode;

class GetReasonCodeService{

    protected $reason; 
    protected $api;

    public function __construct(ReasonCode $c, EAPIService $api){
        $this->reason = $c;
        $this->api = $api;
    }

    public function saveUpdate($reasons){

        foreach($reasons as $c){
            // print_r($c);
            // die;
            $this->saveUpdateReasonCode($c); 
        }

        return response()->json(['status'=>200, 'message'=>"Reason Code fetched Successfully."]);
    }

    protected function saveUpdateReasonCode($product){

        $this->reason->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "reasonID"  =>  $product['reasonID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "reasonID" => $product['reasonID'],
                    "name" => @$product['name'],
                    "code" => @$product['code'],
                    "purpose" => @$product['purpose'],
                    "manualDiscountDisablesPromotionTiers" => !empty($product['manualDiscountDisablesPromotionTiers']) ? json_encode($product['manualDiscountDisablesPromotionTiers'],1) : '',
                    "lastModified"  => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00',
                    "added"  => isset($product['added']) == 1 ? date('Y-m-d H:i:s',$product['added']) : '0000-00-00 00:00',
                ]
            );
    }

     


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->reason->where('clientCode',  $this->api->client->clientCode)->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
