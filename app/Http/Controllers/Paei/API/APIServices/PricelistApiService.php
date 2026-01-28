<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Paei\GetPricelistController; 
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Currency;
use App\Models\PAEI\Payment;
use App\Models\PAEI\Pricelist;
use App\Traits\ResponseTrait;

class PricelistApiService{
    use ResponseTrait;

    protected $pricelist;
    protected $api;
    protected $getService;

    public function __construct(Pricelist $w, EAPIService $api, GetPricelistController $getService){
        $this->pricelist = $w;
        $this->api = $api;
        $this->getService = $getService;
    }

   

    public function getPricelist($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'pricelistID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

        $select = $req->select ? explode(",", $req->select) : 
            array(
                'id',
                'pricelistID',
                'name',
                'startDate',
                'endDate',
                'active',
                'type',
                'pricelistRules',
                'attributes',
                'addedByUserName',
                'added',
                'lastModifiedByUserName',
                'lastModified',
                'created_at',
                'updated_at'
            );
         
        // $groups = $this->group->paginate($pagination);
        $pricelists = $this->pricelist->select($select)->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                    // 'like', '%' . $value . '%'); 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        return $this->successWithData($pricelists);//CustomeResponse::successWithData($pricelists);
        // return response()->json(["status"=>200, "records" => $pricelists]);
    }

    public function savePricelist($req){
        $param = array(
            "name" => $req->name,
            "startDate" => $req->startDate,
            "endDate" => $req->endDate,
            "active" => $req->active,
            "type" => $req->type, 
        );

        foreach($req->toArray() as $key => $val){
            if(!str_contains($key, 'name') && !str_contains($key, 'startDate') && !str_contains($key, 'endDate') && !str_contains($key, 'active') &&  $key != 'type') {
                $param["$key"] = $val;
            }

        }

        // print_r($param);
        // die;

        //Sending Request to Erply
        $res = $this->api->sendRequest("savePriceList", $param, 0, 1);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            $this->getService->getPricelist();
            return $this->successWithMessage("Price List Saved Successfully.");//CustomeResponse::successWithMessage("Price List Saved Successfully.");
        }
        return $this->failWithMessageAndData("Failed While Saving Price List!", $res);//CustomeResponse::failWithMessageAndData("Failed While Saving Price List!", $res);

    }


}
