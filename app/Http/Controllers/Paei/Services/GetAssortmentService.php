<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Assortment;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\UserOperationLog;
use App\Traits\ResponseTrait;
class GetAssortmentService {

    use ResponseTrait; 
    protected $assortment;
    protected $api;

    public function __construct(Assortment $assortment, EAPIService $api){
        $this->assortment = $assortment;
        $this->api = $api;
    }


    public function saveUpdate($assortments){

        foreach($assortments as $c){
            $this->saveUpdateAssortment($c);
        }

        return response()->json(['status'=>200, 'message'=>"Assortment fetched Successfully."]);
    }

    protected function saveUpdateAssortment($product){
        $this->assortment->updateOrCreate(
            [
                "clientCode" => $this->api->client->clientCode,
                "assortmentID"  =>  $product['assortmentID']
            ],
            [
                "clientCode" => $this->api->client->clientCode,
                "assortmentID" => @$product["assortmentID"],
                "code" => @$product["code"],
                "name" => @$product["name"], 
                "added" =>  date('Y-m-d H:i:s', @$product['added']),
                "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',@$product['lastModified']) : '0000-00-00 00:00',
                "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'], true) : '',
            ]
        );
    }

    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->assortment->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }


      
}


