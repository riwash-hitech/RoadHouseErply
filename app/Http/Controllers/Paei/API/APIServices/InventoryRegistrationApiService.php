<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\MyPagination;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\InventoryRegistration; 
use App\Traits\ResponseTrait;
use App\Models\PAEI\ProductSoh;
use App\Models\PAEI\MatrixProduct;
use DB;

class InventoryRegistrationApiService{

    use ResponseTrait;
    protected $inventory;
    protected $customePagination;
    protected $api;


    public function __construct(InventoryRegistration $w, EAPIService $api){
        $this->inventory = $w;
        $this->customePagination = new MyPagination();
        $this->api = $api;
    }

    public function getByID($id){

        $invs = $this->inventory->where("inventoryRegistrationID", $id)->get();
        return response()->json(["status"=>200, "records" => $invs]);

    }

    public function getByWarehouseID($req){
        
        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $invs = $this->inventory->where("warehouseID", $req->warehouseID)->get();
        // print_r($invs);
        // die;
        $invsArray = json_decode(json_encode($invs), true);
        // print_r($invsArray);
        // die;

        // $this->customePagination->setData($req, $invsArray );
        return response()->json(["status"=>200, "records" => $this->customePagination->getPagination($req, $invsArray)]);
    }

    public function getInventoryRegistration($req){
        $id = $req->productID; 
        $results = $this->inventory->where('clientCode', $this->api->client->clientCode)
                    ->with('warehouse')
                    // ->join('newsystem_warehouse_locations', 'newsystem_warehouse_locations.warehouseID', 'newsystem_inventory_registrations.warehouseID')
                    ->whereJsonContains('rows', [['productID' => (int)$id ]])->get();
         
        if($results->isEmpty()){
            return response()->json(['status'=>400, "records"=>"Inventory Not Found!"]);
        }
        return response()->json(['status'=>200, "records"=> $results]);

    }

    public function getInventoryRegistrationByIds($req){
        $ids = explode(",",$req->productIDs);
        $datas = array();
        foreach($ids as $id){
            
            $results = $this->inventory->where('clientCode', $this->api->client->clientCode)
                    ->with('warehouse') 
                    ->whereJsonContains('rows', [['productID' => (int)$id ]])->get();
                  
            foreach($results as $r ){
                $datas[] = $r;
            }
        }


         
        // $results = $this->inventory->with('warehouse')->whereIn('rows->productID', $fids)->get();
        // print_r($results);
        // die;
        // $results = $this->inventory
        //             ->with('warehouse')
        //             ->where(function($query) use ($ids){
        //                 foreach($ids as $id){
        //                     $query->WhereJsonContains('rows', [['productID' => (int)$id ]]);
        //                 }
        //             })->get();
                    // ->join('newsystem_warehouse_locations', 'newsystem_warehouse_locations.warehouseID', 'newsystem_inventory_registrations.warehouseID')
                    // ->whereJsonContains('rows', [['productID' => (int)$id ]])->get();
         
        if(count($datas) < 1){
            return $this->failWithMessage("Inventory Not Found!");
            // return response()->json(['status'=>400, "records"=>"Inventory Not Found!"]);
        }
        return $this->successWithData($datas);
        // return response()->json(['status'=>200, "records"=> $datas]);

    }


    public function getInventories($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'inventoryRegistrationID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        // $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $invs = $this->inventory->paginate($pagination);
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','strictFilter');

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $invs = $this->inventory->with('warehouse')->where(function ($q) use ($requestData, $req) {
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
        return response()->json(["status"=>200, "records" => $invs]);
    }

     
     
    public function getSOH($req){
        
        if($req->productID){
            $data = DB::select("select sum(erplyCurrentStockValue) as totSOH from newsystem_product_soh where erplyProductID in (select productID from newsystem_product_variations where parentProductID = ". trim($req->productID) .")");
            // dd($data);
            $soh = 0;
            foreach($data as $d){
                $soh = $d->totSOH;
            }
            // dd($soh);
            $matrix = MatrixProduct::where("productID", $req->productID)->first();
            return response()->json(["Tot SOH :" =>  $soh, "WebActive : " => $matrix->displayedInWebshop]);
        }
        
        if($req->sku){
            $matrix = MatrixProduct::where("code", $req->sku)->first();
            // dd($matrix);
            if($matrix){
                
                 $data = DB::select("select sum(erplyCurrentStockValue) as totSOH from newsystem_product_soh where erplyProductID in (select productID from newsystem_product_variations where parentProductID = ". $matrix->productID .")");
                // dd($data);
                $soh = 0;
                foreach($data as $d){
                    $soh = $d->totSOH;
                }
                
                return response()->json(["Tot SOH :" =>  $soh, "WebActive : " => $matrix->displayedInWebshop]);
                
            }else{
                return response()->json(["message" =>  "SKU Not Found."]);
            }
        }
        
        
    }



}
