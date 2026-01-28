<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\MyPagination; 
use App\Models\PAEI\InventoryTransfer; 

class InventoryTransferApiService{

    protected $inventory;
    protected $customePagination;
    public function __construct(InventoryTransfer $w){
        $this->inventory = $w;
        $this->customePagination = new MyPagination();
    }

    public function getByID($id){

        $invs = $this->inventory->where("inventoryTransferID", $id)->get();
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

    public function getInventoryWriteOff($req){
        $id = $req->productID;
        $results = $this->inventory
                    // ->with('warehouse')
                    // ->join('newsystem_warehouse_locations', 'newsystem_warehouse_locations.warehouseID', 'newsystem_inventory_registrations.warehouseID')
                    ->whereJsonContains('rows', [['productID' => (int)$id ]])->get();
         
        if($results->isEmpty()){
            return response()->json(['status'=>400, "records"=>"Inventory Write Off Not Found!"]);
        }
        return response()->json(['status'=>200, "records"=> $results]);

    }

    public function getInventoryWriteOffsByIds($req){
        $ids = explode(",",$req->productIDs);
        $datas = array();
        foreach($ids as $id){

            $results = $this->inventory
                    // ->with('warehouse') 
                    ->whereJsonContains('rows', [['productID' => (int)$id ]])->get();
                  
            foreach($results as $r ){
                $datas[] = $r;
            }
        }
 
         
        if(count($datas) < 1){
            return response()->json(['status'=>400, "records"=>"Inventory Write Off Not Found!"]);
        }
        return response()->json(['status'=>200, "records"=> $datas]);

    }


    public function getInventories($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'inventoryTransferID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        // $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $invs = $this->inventory->paginate($pagination);
        $requestData = $req->except('sort_by', 'direction', 'pagination', 'page','recordsOnPage','strictFilter');

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $invs = $this->inventory->where(function ($q) use ($requestData, $req) {
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

    public function saveUpdate($req){
        $data = array(
            "creatorID" => @$req['creatorID'],
            "warehouseFromID" => @$req['warehouseFromID'],
            "warehouseToID"  => @$req['warehouseToID'],
            "currencyCode"  => @$req['currencyCode'],
            "type"  => @$req['type'],
            "currencyCode"  => @$req['currencyCode'],
            "shippingDate"  =>   @$req['shippingDate'],
            "shippingDateActual"  =>   @$req['shippingDateActual'],
            "inventoryTransferOrderID"  =>  @$req['inventoryTransferOrderID'],
            "status"  =>  @$req['status'],
            "notes"  =>  @$req['notes'],
            "added"  => date('Y-m-d H:i:s',$req['added']),
            "confirmed"  => @$req['confirmed'] ? $req['confirmed'] : 1,
          

        );
    }

     



}
