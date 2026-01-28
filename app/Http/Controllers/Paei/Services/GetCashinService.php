<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Cashin; 

class GetCashinService{

    protected $cashin;
    protected $api;

    public function __construct(Cashin $c, EAPIService $api){
        $this->cashin = $c;
        $this->api = $api;
    }

    public function saveUpdate($cashins){

        foreach($cashins as $c){
            $this->saveUpdateCashin($c);
        }

        return response()->json(['status'=>200, 'message'=>"Cashin fetched Successfully."]);
    }

    protected function saveUpdateCashin($product){

        $this->cashin->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "transactionID"  =>  $product['transactionID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "transactionID" => @$product["transactionID"],
                    "sum" => @$product["sum"],
                    "currencyCode" => @$product["currencyCode"],
                    "currencyRate" => @$product["currencyRate"],
                    "warehouseID" => @$product["warehouseID"],
                    "warehouseName" => @$product["warehouseName"],
                    "pointOfSaleID" => @$product["pointOfSaleID"],
                    "pointOfSaleName" => @$product["pointOfSaleName"],
                    "employeeID" => @$product["employeeID"],
                    "employeeName" => @$product["employeeName"],
                    "dateTime" => @$product["dateTime"],
                    "reasonID" => @$product["reasonID"],
                    "comment" => @$product["comment"], 
                    "added" =>  date('Y-m-d H:i:s', @$product['added']),
                    "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',@$product['lastModified']) : '0000-00-00 00:00',
                    "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'], true) : '',
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->cashin->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
