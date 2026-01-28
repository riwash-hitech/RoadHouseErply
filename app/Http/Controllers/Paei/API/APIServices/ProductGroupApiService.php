<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductGroup;
use App\Models\PAEI\Warehouse;

class ProductGroupApiService{

    protected $group;
    protected $api;

    public function __construct(ProductGroup $w, EAPIService $api){
        $this->group = $w;
        $this->api = $api;
    }

    public function getByGroupsID($id){

        $warehouse = $this->group->where('clientCode', $this->api->client->clientCode)->where("productGroupID", $id)->get();
        return response()->json(["status"=>200, "records" => $warehouse]);

    }

    public function getGroups($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }

         

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','includeMatrixVariations','strictFilter');

         
        // $groups = $this->group->paginate($pagination);
        $groups = $this->group->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null && $value != 'undefined') { 
                    if((bool)$req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                    // 'like', '%' . $value . '%'); 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        
        return response()->json(["status"=>200,"success" => true, "records" => collect($groups)]);
    }


}
