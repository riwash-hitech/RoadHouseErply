<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Log;

class WarehouseService
{
    protected $warehouse;
    protected $api; 
    protected $assortment;

    public function __construct(Warehouse $warehouse, EAPIService $api, AssortmentService $as)
    {
        $this->warehouse = $warehouse;
        $this->api = $api; 
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
        $this->assortment = $as;

    }

    public function saveWarehouseByIDS(){

    }

    public function saveWarehouse($warehouse){
        info("save warehouse function called");
        $verifiedSessionKey = $this->api->client->sessionKey;
       
        $data = $warehouse;//$this->warehouse->where('warehouse_status', 1)->where('erplyPending', 1)->limit(2)->get();
        // dd($data);
         //first saving assortmnt 
        //NOW SAVING ASSORTMNET ACCORDING TO WAREHOUSE
        $assortment = $this->assortment->saveBulkAssortment($data, $this->api->client->clientCode, $verifiedSessionKey);



         //$this->api->verifySessionByKey($this->api->client->sessionKey);
        $bulkWarehouse = $this->makeBundle($data, $verifiedSessionKey, $assortment);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $verifiedSessionKey, 
        );

        info("save warehouse bulk api callling...");
        // info($bulkWarehouse);

        $bulkRes = $this->api->sendRequest($bulkWarehouse, $bulkparam, 1, 0 , 0);
        // info($bulkRes);
        // print
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            info("warehouse api response received");
            foreach($data as $key => $c){
                $c->erplyWarehouseID = $bulkRes['requests'][$key]['records'][0]['warehouseID'];
                $c->erplyPending = 0;
                $c->save();
            }
            // info("warehouse ");
            
            // Log::info($bulkRes);
        }
        info("warehouse save update success");
        
        
        return response()->json(['status' => 200, 'message' => "Warehouse Location Created or Updated Successfully."]);
    }


    protected function makeBundle($data, $verifiedSessionKey,$assortment){
       
        $BundleArray = array();
        foreach($data as $key  => $warehouse){
            $param = array(
                "requestName" => "saveWarehouse",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "name" => $warehouse->name,
                "code" => $warehouse->locationid,
                "email" => $warehouse->store_email,
                "phone" => $warehouse->phone_number,
                "assortmentID" => $assortment['requests'][$key]['records'][0]['assortmentID'],
                // "phone" => $customer->ciPhone,
                // "mobile" => $customer->ciMobile,
                // "website" => $customer->ciWebsite,
                // "countryID" => 25, //25 Australia  
            );

            $warehouseID = $this->checkWarehouse($warehouse->locationid, $verifiedSessionKey, $warehouse->id);
            if($warehouseID != ''){
                $param['warehouseID'] = $warehouseID;
            }

            //NOW ADDING ATTRIBUTES 
            $index = 1;
            foreach($warehouse->toArray() as $key => $c){
                if($key == "latitude" || $key == "longitude" || $key == "locationidtransit" || $key == "storeaddress" || $key == "city" || $key == "openingHours" || $key == "warehousecat" || $key == "postcode"){
                    $param["attributeName".$index] = $key;
                    $param["attributeType".$index] = $key == 'storeaddress' || $key == "openingHours" ? 'text' : ($key == 'locationidtransit' || $key == 'warehousecat' ?  'varchar(300)' :  ($key == 'postcode' ? 'int(100)' : 'varchar(100)'));
                    $param["attributeValue".$index] = $c;
                    $index++;
                }
            }

            array_push($BundleArray,$param );
             
        }

        $BundleArray = json_encode($BundleArray, true);
        // print_r($BundleArray);
        // die;
        return $BundleArray; 
    }

    public function checkWarehouse($locationid, $sessionKey, $wid){
        info("checking warehouse location to erply db ");
        $param = array(
            "code" => $locationid,
            "sessionKey" => $sessionKey
        );

        $res = $this->api->sendRequest("getWarehouses", $param,0,0,0);
        // info($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Log::info("Warehouse Location ID exist ".$res['records'][0]['warehouseID']);
            //if exist update locally
            Warehouse::findOrfail($wid)->update(['erplyWarehouseID' => $res['records'][0]['warehouseID'], 'erplyPending'=> 0]);
            return $res['records'][0]['warehouseID'];
        }else{
            info("check warehouse location not exist to  erply db ". $locationid);
            return '';
        }
        // print_r($res);

        return '';

    }

    public function deleteWarehouses($wids,$aids){

    }
}