<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Classes\MyPagination;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\InventoryRegistration;
use App\Models\PAEI\InventoryWriteOffs;

class InventoryWriteOffApiService{

    protected $inventory;
    protected $customePagination;
    protected $api;

    public function __construct(InventoryWriteOffs $w, EAPIService $api){
        $this->inventory = $w;
        $this->customePagination = new MyPagination();
        $this->api = $api;
    }

    public function getByID($id){

        $invs = $this->inventory->where('clientCode', $this->api->client->clientCode)->where("inventoryWriteOffID", $id)->get();
        return response()->json(["status"=>200, "records" => $invs]);

    }

    public function getByWarehouseID($req){
        
        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $invs = $this->inventory->where('clientCode', $this->api->client->clientCode)->where("warehouseID", $req->warehouseID)->get();
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
        $results = $this->inventory->where('clientCode',  $this->api->client->clientCode)
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

            $results = $this->inventory->where('clientCode', $this->api->client->clientCode)
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
            $req->sort_by = 'inventoryWriteOffID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        // $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $invs = $this->inventory->paginate($pagination);
        $requestData = $req->except(Except::$except);

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $invs = $this->inventory->where(function ($q) use ($requestData, $req) {
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

    public function saveInventoryWriteOff($req){
        $data = array(
            "warehouseID" => @$req['warehouseID'],
            "stocktakingID"  => @$req['stocktakingID'],
            "recipientID"  => @$req['recipientID'],
            "reasonID"  => @$req['reasonID'],
            "currencyCode"  => @$req['currencyCode'],
            "date"  =>  date('Y-m-d H:i:s'),
            "comments"  =>  @$req['comments'],
            "added"  => date('Y-m-d H:i:s',$req['added']),
            "confirmed"  => @$req['confirmed'] ? $req['confirmed'] : 1,
            // "lastModified"  => isset($req['lastModified']) == 1 && isset($req['lastModified']) != null ? date('Y-m-d H:i:s',$req['added']) : "0000-00-00 00:00:00",
            // "rows"  => !empty($req['rows']) ? json_encode($req['rows'],1) : '', 
            // "attributes"  => !empty($req['attributes']) ? json_encode($req['attributes'],1) : '', 

        );

        //for attributes
        $attributes = array();
        $chunk = array();
        $count = 0;
        foreach($req->toArray() as $key => $val){
            
            if(str_contains($key, 'attribute')) {
                 
                $chunk["$key"] = $val;
                $count = $count + 1;
                if($count == 3){
                    array_push($attributes, $chunk);
                    $count = 0;
                    unset($chunk);
                }
            }
        }
        if(count($attributes) > 0){
            $attributes = json_encode($attributes, true);
            $data['attributes'] = $attributes;
        }
        //for rows
        $attributes = array();
        $chunk = array();
        $count = 0;
        foreach($req->toArray() as $key => $val){
            
            if(str_contains($key, 'productID') || str_contains($key, 'amount') || str_contains($key, 'price')) {
                 
                $chunk["$key"] = $val;
                $count = $count + 1;
                if($count == 3){
                    array_push($attributes, $chunk);
                    $count = 0;
                    unset($chunk);
                }
            }
        }
        if(count($attributes) > 0){
            $attributes = json_encode($attributes, true);
            $data['rows'] = $attributes;
        }
        
        $inv = $this->inventory->updateOrCreate(
            [
                "id"  =>  $req['id']
            ],
            $data
        );

        //now saving or updating to erply
        return $this->saveInventoryWriteOffErply($req, $inv->id);

        
    }

    protected function saveInventoryWriteOffErply($req, $id){
        $inv = $this->inventory->where('id', $id)->first();
        $data = array(
            // "inventoryWriteOffID" => $req['inventoryWriteOffID'],
            // "inventoryWriteOffNo" => $req['inventoryWriteOffNo'],
            // "creatorID" => @$req['creatorID'],
            "warehouseID" => @$req['warehouseID'],
            "stocktakingID"  => @$req['stocktakingID'],
            // "inventoryID"  => $req['inventoryID'],
            "recipientID"  => @$req['recipientID'],
            "reasonID"  => @$req['reasonID'],
            "currencyCode"  => @$req['currencyCode'],
            // "currencyRate"  =>  @$req['currencyRate'],
            // "date"  =>  date('Y-m-d H:i:s'),
            // "inventoryTransactionDate"  =>  @$req['inventoryTransactionDate'],
            "comments"  =>  @$req['comments'],
            "added"  => date('Y-m-d H:i:s',$req['added']),
            "confirmed"  => @$req['confirmed'] ? $req['confirmed'] : 1,
            // "lastModified"  => isset($req['lastModified']) == 1 && isset($req['lastModified']) != null ? date('Y-m-d H:i:s',$req['added']) : "0000-00-00 00:00:00",
            // "rows"  => !empty($req['rows']) ? json_encode($req['rows'],1) : '', 
            // "attributes"  => !empty($req['attributes']) ? json_encode($req['attributes'],1) : '', 

        );
        
        
        foreach($req->toArray() as $key => $val){
            if(str_contains($key, 'attribute') || str_contains($key, 'productID') || str_contains($key, 'amount') || str_contains($key, 'price')) {
                $data["$key"] = $val; 
            }
        }

        //if exist get inventory write off ID
        if($inv->inventoryWriteOffID){
            $param['inventoryWriteOffID'] = $inv->inventoryWriteOffID;
        }

        $res = $this->api->sendRequest('saveInventoryWriteOff', $param);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            //updating customer group 
            // $this->getService->saveCustomerGroup
            $eid = $res['records'][0]['inventoryWriteOffID'];
            $this->inventory->find($id)->update(['inventoryWriteOffID' => $eid ]);
            return response()->json(['status' => 200, 'response' => 'Inventory WriteOff Created Successfully.']);
        }
        return response()->json($res);



    }

     



}
