<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Currency; 
use App\Traits\ResponseTrait;

class CurrencyApiService{

    protected $currency;
    protected $api;

    
    use ResponseTrait; 

    public function __construct(Currency $w, EAPIService $api ){
        $this->currency = $w; 
        $this->api = $api;
    }

   

    public function getCurrency($req){

        // return $this->successWithData($this->api->client);
        // die;
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
        $reasons = $this->currency->where(function ($q) use ($requestData, $req) {
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
