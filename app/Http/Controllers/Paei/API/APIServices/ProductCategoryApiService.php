<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\ProductCategory;


class ProductCategoryApiService{

    protected $category;
    protected $api;

    public function __construct(ProductCategory $w, EAPIService $api){
        $this->category = $w;
        $this->api = $api;
    }

    public function getByCategoryID($id){

        $warehouse = $this->category->where('clientCode', $this->api->client->clientCode)->where("productCategoryID", $id)->get();
        return response()->json(["status"=>200, "records" => $warehouse]);

    }

    public function getCategories($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'productCategoryName';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $categories = $this->category->paginate($pagination);
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','strictFilter');
        
        $categories = $this->category->where(function ($q) use ($requestData, $req) {
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

        return response()->json(["status"=>200, "success" => true, "records" => collect($categories)]);
    }



}
