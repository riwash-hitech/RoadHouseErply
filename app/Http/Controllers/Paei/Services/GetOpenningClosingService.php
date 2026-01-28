<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\DayOpeningClosing;

class GetOpenningClosingService{

    protected $oc;
    protected $api;

    public function __construct(DayOpeningClosing $c, EAPIService $api){
        $this->oc = $c;
        $this->api = $api;
    }

    public function saveUpdate($cashins){

        foreach($cashins as $c){
            $this->saveUpdateCashin($c);
        }

        return response()->json(['status'=>200, 'message'=>"Get Openning Closing Day fetched Successfully."]);
    }

    protected function saveUpdateCashin($product){

        $this->oc->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "dayID"  =>  $product['dayID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    'dayID' => @$product['dayID'],
                    'warehouseID' => @$product['warehouseID'],
                    'warehouseName' => @$product['warehouseName'],
                    'pointOfSaleID' => @$product['pointOfSaleID'],
                    'pointOfSaleName' => @$product['pointOfSaleName'],
                    'drawerID' => @$product['drawerID'],
                    'shiftType' => @$product['shiftType'],
                    'employees' => !empty(@$product['employees']) ? json_encode(@$product['employees'], true) : '',
                    'openedUnixTime' => @$product['openedUnixTime'] ? date('H:i:s', @$product['openedUnixTime']) : '00:00:00',
                    'openedByEmployeeID' => @$product['openedByEmployeeID'],
                    'openedByEmployeeName' => @$product['openedByEmployeeName'],
                    'openedSum' => @$product['openedSum'],
                    'closedUnixTime' => @$product['closedUnixTime'] ? date('H:i:s', @$product['closedUnixTime']) : '00:00:00',
                    'closedByEmployeeID' => @$product['closedByEmployeeID'],
                    'closedByEmployeeName' => @$product['closedByEmployeeName'],
                    'closedSum' => @$product['closedSum'] ? @$product['closedSum'] : 0,
                    'bankedSum' => @$product['bankedSum'] ? @$product['bankedSum'] : 0,
                    'notes' => @$product['notes'],
                    'reasonID' => @$product['reasonID'],
                    'currencyCode' => @$product['currencyCode'],  
                    "attributes" => !empty(@$product['attributes']) ? json_encode(@$product['attributes'], true) : '',
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->oc->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
