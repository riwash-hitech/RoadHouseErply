<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Warehouse;

class WarehouseApiService{

    protected $warehouse;
    protected $api;

    public function __construct(Warehouse $w, EAPIService $api){
        $this->warehouse = $w;
        $this->api = $api;
    }

    public function getByWarehouseID($id){

        $warehouse = $this->warehouse->where('clientCode',  $this->api->client->clientCode)->where("id", $id)->get();
        return response()->json(["status"=>200, "records" => $warehouse]);

    }

    public function getWarehouses($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
         

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','strictFilter');
        // $warehouses = $this->warehouse->paginate($pagination);
        $warehouses = $this->warehouse->where(function ($q) use ($requestData, $req) {
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


        return response()->json(["status"=>200, "records" => $warehouses]);
    }

    public function getProductLong(){

    }


    protected function packaging($matrix, $variation){
        $newPackage = array();
        foreach($matrix as $m){
            array_push($newPackage , $m);
        }

        foreach($variation as $v){
            array_push($newPackage , $v);
        }
        return $newPackage;
    }



    protected function makeQuery($req){

    }



}
