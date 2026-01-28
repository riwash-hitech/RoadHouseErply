<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\PaymentType;

class PaymentTypeApiService{

    protected $type;
    protected $api;

    public function __construct(PaymentType $w, EAPIService $api){
        $this->type = $w;
        $this->api = $api;
    }

   

    public function getTypes($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);
        
         
        // $groups = $this->group->paginate($pagination);
        $types = $this->type->where(function ($q) use ($requestData, $req) {
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
        
        return response()->json(["status"=>200, "records" => $types]);
    }

    public function saveUpdate($req){
        
    }


}
