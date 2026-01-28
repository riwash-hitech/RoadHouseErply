<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductGroup;
use App\Models\PAEI\ReasonCode;
use App\Models\PAEI\Warehouse;

class ReasonCodesApiService{

    protected $reason;
    protected $api;

    public function __construct(ReasonCode $w, EAPIService $api){
        $this->reason = $w;
        $this->api = $api;
    }

   

    public function getReasonCodes($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }

        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','includeMatrixVariations','strictFilter');

         
        // $groups = $this->group->paginate($pagination);
        $reasons = $this->reason->where(function ($q) use ($requestData, $req) {
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
        
        return response()->json(["status"=>200, "records" => $reasons]);
    }


}
