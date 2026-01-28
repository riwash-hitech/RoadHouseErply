<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Paei\GetOpenningClosingController;
use App\Http\Controllers\Paei\Services\GetOpenningClosingService;
use App\Http\Controllers\Response\CustomeResponse;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\DayOpeningClosing;

class OpenningClosingDayApiService{

    protected $ocd;
    protected $api;
    protected $service;

    public function __construct(DayOpeningClosing $w, EAPIService $api, GetOpenningClosingController $service){
        $this->ocd = $w;
        $this->api = $api;
        $this->service = $service;
    } 

    public function getOpenningClosingDay($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'dayID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

         
        // $groups = $this->group->paginate($pagination);
        $ocds = $this->ocd->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    } 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $ocds]);
    }

    public function saveOpeningDay($req){
        $param = array(
            "clientCode" => $this->api->client->clientCode,
            "pointOfSaleID" => $req->pointOfSaleID,
            "employeeID" => $req->employeeID,
            "openedSum" => $req->openedSum,
            "openedUnixTime" => strtotime($req->openedUnixTime),
            "queryOpenDay" => $req->queryOpenDay,
            "currencyCode" => $req->currencyCode, 
        );
 
        
        $res =  $this->api->sendRequest("POSOpenDay", $param,0,1);
        
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->service->getOpenningClosing();
            return CustomeResponse::successWithMessage("POS Open Day Saved Successfully.");
        }
        return CustomeResponse::failWithMessageAndData("Failed While Saving POS Opening Day!", $res);


        
        
    }

    public function saveClosingDay($req){
        $param = array(
            "clientCode" => $this->api->client->clientCode,
            "pointOfSaleID" => $req->pointOfSaleID,
            "employeeID" => $req->employeeID,
            "openedUnixTime" => $req->openedUnixTime,
            "closedUnixTime" => $req->closedUnixTime,
            "closedSum" => $req->closedSum,
            "bankedSum" => $req->bankedSum,
            "reasonID" => $req->reasonID,
            "queryOpenDay" => $req->queryOpenDay,
            "currencyCode" => $req->currencyCode
        );

        $res =  $this->api->sendRequest("POSCloseDay", $param,0, 1);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->service->getOpenningClosing();
            return CustomeResponse::successWithMessage("POS Close Day Saved Successfully.");
        }
        return CustomeResponse::failWithMessageAndData("Failed While POS Closing Day", $res);
    }


}
