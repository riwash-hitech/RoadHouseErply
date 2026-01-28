<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\Warehouse;

class AssortmentService{
    protected $api;
    protected $location;
 
    public function __construct(EAPIService $api, Warehouse $warehouse)
    {
        $this->api = $api;
        $this->location = $warehouse;
    
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
    }

    public function saveBulkAssortment($warehouse ){
        info("save bulk assortment called");
        $bulkParam = $this->makeBundleAssortment($warehouse);
        $bulkP = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey
        );
        $bulkRes = $this->api->sendRequest($bulkParam, $bulkP, 1,0,0);
        // info($bulkRes);
        info("Response received from save bulk assortment");
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($warehouse as $key => $val){
                $val->erplyAssortmentID = $bulkRes['requests'][$key]['records'][0]['assortmentID'];
                $val->save();
            }
            info("Assortment Save/Updated Successfully.");
            return $bulkRes;
        }
    }   

    public function makeBundleAssortment($warehouse){
        $bulkParam = array();

        foreach($warehouse as $w){
            $param = array(
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "requestName" => "saveAssortment",
                "name" => $w->locationid,
            );
            $AID = $this->getAssortmentByNameValue($w->locationid,$w->id);
            if($AID != '')$param['assortmentID'] = $AID;
            array_push($bulkParam, $param);
        }
        // info("Assortment Bulk Param");
        // info($bulkParam);
        $bulkParam = json_encode($bulkParam, true);
        return $bulkParam;
    }

    public function getAssortmentByNameValue($id,$wid){
        $param = array(
            "searchAttributeName" => "name",
            "searchAttributeValue" => $id,
            "sessionKey" => $this->api->client->sessionKey
        );
        $res = $this->api->sendRequest("getAssortments", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            info('assortment exist return assortment id');
            //updating local db
            // Warehouse::findOrfail($wid)->update(['erplyAssortmentID'=>$res['records'][0]['assortmentID']]);
            return $res['records'][0]['assortmentID'];
        }
        return '';
    }

    public function deleteAssortment($ids="1,2,3,4"){
        $param = array(
            "assortmentIDs" => $ids,
        );
        $res = $this->api->sendRequest("deleteAssortment", $param);
        $deletedIds = $res['records'][0]['deletedIDs'];
        // $data = explode(",",$deletedIds);
        foreach(explode(",",$deletedIds) as $d){
            //NOW UPDATING LOCATIONS TABLES DATA
            if($d){
                $this->location->where('erplyAssortmentID', $d)->update(['erplyAssortmentID','0']);
            }
        }
        info("Asortment IDs Deleted ".$res['records'][0]['deletedIDs']. " nonExistingIDs ".$res['records'][0]['nonExistingIDs'] . " notDeletableIDs ". $res['records'][0]['notDeletableIDs'] );
        // return response()->json(['response'=>$res]);
    }
}