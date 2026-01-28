<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\StockColor;
use App\Models\StockColorSize;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class DimensionService{
    protected $api;
    protected $variation;
 

    protected $stockcolor;
    public function __construct(EAPIService $api, StockColorSize $variation, StockColor $sc)
    {
        $this->api = $api;
        $this->variation = $variation;
 
        $this->stockcolor = $sc;
        // $this->api->client->sessionKey = $this->api->verifySessionByKey($client->sessionKey);
    }

    public function saveSizeDimension($req){
        $limit = $req->limit == '' ? 20 : $req->limit;
        $data = $this->variation
                        ->where('newsystem_stock_colour_size.sizeDIMID', '')
                        // ->where('newsystem_internet_category.erplyCatPending', 0)
                        ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
                        ->groupBy('newsystem_stock_colour_size.ciSizeCode')
                        ->limit($limit)
                        ->get();
        // dd($data);
        $bulkParam = array();
        foreach($data as $d){
            $param = array(
                "requestName" => "addItemToMatrixDimension",
                "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                "clientCode" => $this->api->client->clientCode,
                "dimensionID" => 8,
                "name" => $d->ciSizeCode,
                "value" => $d->ciSizeCode
            );
            array_push($bulkParam, $param);
        }
        $bulkParam = json_encode($bulkParam, true);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );

        // print_r($bulkParam);
        $res = $this->api->sendRequest($bulkParam, $bulkparam,1,1,0);
        // print_r($res);
        // die;
        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            foreach($data as $key => $c){
                if($res['requests'][$key]['status']['errorCode'] == 0){

                    $c->where('ciSizeCode', $c->ciSizeCode)->update(['sizeDIMID' => $res['requests'][$key]['records'][0]['itemID']]);
                    info($res['requests'][$key]['records'][0]['itemID']." SIze Item added");
                }else{
                    info("error ".$res['requests'][$key]['status']['errorCode']."  cisieze code ". $c->ciSizeCode);
                }
            }
            
        }
        return response()->json(['status'=>200, 'response'=> $res]);



    }

    public function saveColorDimension($req){
        $limit = $req->limit == '' ? 20 : $req->limit;
        // echo $limit;
        // die;
        $data = $this->stockcolor->where('erplyDimID', '')->where('deleted', 0)->take($limit)->get();
        //  dd($data);
        $bulkParam = array();
        foreach($data as $d){
            $param = array(
                "requestName" => "addItemToMatrixDimension",
                "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
                "clientCode" => $this->api->client->clientCode,
                "dimensionID" => 1,
                "name" => $d->ciColourDescription,
                "value" => $d->ciColourDescription
            );
            array_push($bulkParam, $param);
        }
        $bulkParam = json_encode($bulkParam, true);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest($bulkParam, $bulkparam,1,0,0);
        // info($res);
        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            foreach($data as $key => $c){
                if($res['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplyDimID = $res['requests'][$key]['records'][0]['itemID'];
                    $c->save();
                    info($res['requests'][$key]['records'][0]['itemID']." Item added");
                }else{
                    info("Error ". $res['requests'][$key]['status']['errorCode']." Color Code". $c->ciColourCode);
                }
            } 
        }
        return response()->json(['status'=>200, 'response'=> $res]);

        // echo "success";
    }

    public function saveDimension($req){
        $param = array(
            "sessionKey" => $this->api->client->sessionKey,//$this->api->verifySessionByKey($this->api->client->sessionKey),
            "clientCode" => $this->api->client->clientCode,
            "name" => $req->name
        );

        $res = $this->api->sendRequest("saveMatrixDimension", $param);
        return response()->json(['status'=>200, "response"=>$res]);
    }

    
}