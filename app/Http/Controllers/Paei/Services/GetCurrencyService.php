<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Currency; 

class GetCurrencyService{

    protected $currency; 
    protected $api;

    public function __construct(Currency $c, EAPIService $api){
        $this->currency = $c;
        $this->api = $api;
    }

    public function saveUpdate($currencies){

        foreach($currencies as $c){
            // print_r($c);
            // die;
            $this->saveUpdateReasonCode($c); 
        }

        return response()->json(['status'=>200, 'message'=>"Currency fetched Successfully."]);
    }

    protected function saveUpdateReasonCode($product){

        $this->currency->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "currencyID"  =>  $product['currencyID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "currencyID" => $product['currencyID'],
                    "code" => @$product['code'],
                    "name" => @$product['name'],
                    "rate" => @$product['rate'],
                    "default" => @$product['default'],
                    "nameShort" => @$product['nameShort'],
                    "nameFraction" => @$product['nameFraction'],
                    "prefix" => @$product['prefix'],
                    "suffix" => @$product['suffix'], 
                    "lastModified"  => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00',
                    "added"  => isset($product['added']) == 1 ? date('Y-m-d H:i:s',$product['added']) : '0000-00-00 00:00',
                ]
            );
    }

     


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->currency->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
