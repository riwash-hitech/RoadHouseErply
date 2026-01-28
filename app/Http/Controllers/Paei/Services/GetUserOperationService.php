<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService; 
use App\Models\PAEI\UserOperationLog;
use App\Traits\ResponseTrait;
class GetUserOperationService {

    use ResponseTrait;
    protected $uoi;
    protected $customer;
    protected $useroperation;
    protected $letsLog;
    protected $api;
    protected $v2;

    public function __construct(UserOperationLog $log, UserLogger $logger, EAPIService $api, GetUserOperationServiceV2 $v2){//UserOperationInterface $uoi){
        // $this->uoi = $uoi;
        $this->useroperation = $log;
        $this->letsLog = $logger;
        $this->api = $api;
        $this->v2 = $v2;
    }
    
    public function handleCustomer($res){

        foreach($res as $l){ 
            $old = $this->useroperation->where('clientCode', $this->api->client->clientCode)->where("logID", $l["logID"])->where('tableName', $l["tableName"])->first();
            
            $new = $this->useroperation->updateOrcreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "logID" => $l["logID"],
                    "tableName" => $l["tableName"]
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "logID" => $l["logID"],
                    "userName" => $l["username"],
                    "tableName" => $l["tableName"],
                    "itemID" => $l['itemID'],
                    "operation" => $l['operation'],
                    "timestamp" => date('Y-m-d H:i:s',$l['timestamp']),
                ]
            );
            if($l['operation'] == 'delete'){
                $this->v2->handleDelete($l['itemID'], $l['tableName'], $this->api->client->clientCode);   
            }
            $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($new, true), $old  ? "User Operation Log Updated" : "User Operation Log Created");    
  
        } 
        return response()->json(["status" => 200, "message" => "Customer Operation Fetched Successfully."]);
    }

    // public function handleCustomerGroup($res){

    //     foreach($res as $l){ 
    //         $old = $this->useroperation->where('clientCode', $this->api->client->clientCode)->where("logID", $l["logID"])->where('tableName', $l["tableName"])->first();
            
    //         $new = $this->useroperation->updateOrcreate(
    //             [
    //                 "clientCode" => $this->api->client->clientCode,
    //                 "logID" => $l["logID"],
    //                 "tableName" => $l["tableName"]
    //             ],
    //             [
    //                 "clientCode" => $this->api->client->clientCode,
    //                 "logID" => $l["logID"],
    //                 "userName" => $l["username"],
    //                 "tableName" => $l["tableName"],
    //                 "itemID" => $l['itemID'],
    //                 "operation" => $l['operation'],
    //                 "timestamp" => date('Y-m-d H:i:s',$l['timestamp']),
    //             ]
    //         );
    //         if($l['operation'] == 'delete'){
    //             $this->v2->handleDelete($l['itemID'], $l['tableName'], $this->api->client->clientCode);   
    //         }
    //         $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($new, true), $old  ? "User Operation Log Updated" : "User Operation Log Created");    
  
    //     } 
    //     return response()->json(["status" => 200, "message" => "Customer Operation Fetched Successfully."]);
    // }
    
    public function handleOperationLog($res){
        
        foreach($res as $l){
            
            $old = $this->useroperation->where('clientCode', $this->api->client->clientCode)->where("logID", $l["logID"])->where('tableName', $l["tableName"])->first();
            
            $new = $this->useroperation->updateOrcreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "logID" => $l["logID"],
                    "tableName" => $l["tableName"]
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "logID" => $l["logID"],
                    "userName" => $l["userName"],
                    "tableName" => $l["tableName"],
                    "itemID" => $l['itemID'],
                    "timestamps" => date('Y-m-d H:i:s',$l['timestamps']),
                ]
            );

            $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($new, true), $old  ? "User Operation Log Updated" : "User Operation Log Created");    


        }

        return $this->successWithMessage("User Operation Log Fetched Successfully.");
    }

    public function getLastUpdateDate($table){
        // echo "im call";
        $latest = $this->useroperation->where('clientCode', $this->api->client->clientCode)->where('tableName', $table)->orderBy('timestamp', 'desc')->first();
        if($latest){

            return strtotime($latest->timestamp);
        }
        return 0;// strtotime($latest);
    }


}

interface UserOperationInterface{

    public function handleLog($req);
}


class OperationCustomer implements UserOperationInterface{

    public function handleLog($req){
        //
    }
}

class OperationCustomerGroup implements UserOperationInterface{

    public function handleLog($req){

    }
}


