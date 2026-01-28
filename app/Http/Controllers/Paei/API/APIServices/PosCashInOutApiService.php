<?php
namespace App\Http\Controllers\Paei\API\APIServices;
 
use App\Http\Controllers\Services\EAPIService; 

class PosCashInOutApiService{

    protected $cashin;
    protected $api;

    public function __construct(EAPIService $api){
        // $this->cashin = $w;
        $this->api = $api;
    }

   

    public function saveCashIn($req){
 
        $param = array(
            "pointOfSaleID" => $req->pointOfSaleID,
            "employeeID" => $req->employeeID,
            "sum" => $req->sum,
            "currencyCode" => $req->currencyCode,
            "reasonID" => $req->reasonID,
            "comment" => $req->comment,
            "added" => strtotime($req->added), 
        );
        foreach($req->toArray() as $key => $val){
            if(str_contains("$key", "attribute")){
                $param["$key"] = $val;
            }
        }

        $res = $this->api->sendRequest("POSCashIN", $param);
        if($req['status']['errorCode'] == 0 && !empty($res['records'])){
            return response()->json(["status"=>200, "records" => $res]);
        }

        return response()->json(["status"=>400, "records" => $res]);
    }

    public function saveCashOut($req){
        $param = array(
            "pointOfSaleID" => $req->pointOfSaleID,
            "employeeID" => $req->employeeID,
            "sum" => $req->sum,
            "currencyCode" => $req->currencyCode,
            "reasonID" => $req->reasonID,
            "comment" => $req->comment,
            "added" => strtotime($req->added), 
        );
        foreach($req->toArray() as $key => $val){
            if(str_contains("$key", "attribute")){
                $param["$key"] = $val;
            }
        }

        $res = $this->api->sendRequest("POSCashOUT", $param);
        if($req['status']['errorCode'] == 0 && !empty($res['records'])){
            return response()->json(["status"=>200, "records" => $res]);
        }

        return response()->json(["status"=>400, "records" => $res]);
    }


}
