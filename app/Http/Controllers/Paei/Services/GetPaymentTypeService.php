<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\GiftCard;
use App\Models\PAEI\PaymentType;

class GetPaymentTypeService{

    protected $type;
    protected $api;

    public function __construct(PaymentType $c, EAPIService $api){
        $this->type = $c;
        $this->api = $api;
    }

    public function saveUpdate($types){

        foreach($types as $c){
            $this->saveUpdatePaymentType($c);
        }

        return response()->json(['status'=>200, 'message'=>"Payment Types fetched Successfully."]);
    }

    protected function saveUpdatePaymentType($product){

        $this->type->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "paymentTypeID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "paymentTypeID" => @$product["id"],
                    "type" => @$product["type"],
                    "name" => @$product["name"],
                    "print_name" => @$product["print_name"],
                    "quickBooksDebitAccount" => @$product["quickBooksDebitAccount"],
                    "added" => date('Y-m-d H:i:s',$product['added']), 
                    "lastModified" =>  date('Y-m-d H:i:s', $product['lastModified']), 
                     
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->type->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
