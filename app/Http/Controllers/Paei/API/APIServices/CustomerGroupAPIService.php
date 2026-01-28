<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Classes\MyPagination;
use App\Classes\MySorting;
use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService; 
use App\Models\PAEI\CustomerGroup;

class CustomerGroupAPIService{

    protected $group;
    protected $sorting;
    protected $pagination;
    protected $api; 
    protected $letsLog;

    public function __construct(CustomerGroup $c, MySorting $sorting, MyPagination $pagination, EAPIService $api , UserLogger $logger){
        $this->group = $c;
        $this->sorting = $sorting;
        $this->pagination = $pagination;
        $this->api = $api;
        $this->letsLog = $logger;
        
    }

    public function getByID($id){
        $customer = $this->group->where("id", $id)->first();
        if(!$customer){
            return response()->json(["status" => 400, "message" => "Invalid customer Group ID!"]);
        }
        return response()->json(["status"=>200, "records" => $customer]);

    }

    public function getCustomerGroup($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }
        if(isset($req->deleted) == 0){
            $req->deleted = 0;
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $requestData = $req->except(Except::$except);
         
        $groups = $this->group->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            $q->where('deleted', $req->deleted);
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
        //SORTING
        // $groups = $this->sorting->letsSort($groups, $req);

        //PAGINATION
        // $groups = $this->pagination->getPagination($req, $groups);

        return response()->json(["status"=>200,"success" => true, "records" => collect($groups)]);
    }

    // public function 

    public function saveCustomerGroup($req){

         
        //LOCAL CREATE OR UPDATE
        $data = array(
            "name" => $req["name"],
                // "pricelistID" => $req->has("pricelistID") ? $req->pricelistID : 0,
                // "pricelistID2" => $req->has("pricelistID2") ? $req->pricelistID : 0,
                // "pricelistID3" => $req->has("pricelistID3") ? $req->pricelistID : 0,
                // "pricelistID4" => $req->has("pricelistID4") ? $req->pricelistID : 0,
                // "pricelistID5" => $req->has("pricelistID5") ? $req->pricelistID : 0,
                "added" => date('Y-m-d H:i:s'),
                "lastModified" => "0000-00-00 00:00:00",

        );
        $attributes = array();
        $chunk = array();
        $count = 0;
        foreach($req->toArray() as $key => $val){
            
            if(str_contains($key, 'attribute')) {
                // info($key);
                // $chunk[]
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
        
        
        if($req->id){
            $old_group = $this->group->where('id', $req->id)->first();
        }

        $group = $this->group->updateOrcreate( 
            [
                "id" => $req["id"],       
            ],
            $data
        );
       
        //lets Log
        $this->letsLog->setLog($req->id ? json_encode($old_group, true) : '', json_encode($group, true), $req->id ? "Group Updated" : "Group Created");


        $erplyCustomerID = $this->saveCustomerGroupErply($req, $group->id);
        $this->group->find($group->id)->update(['customerGroupID' => $erplyCustomerID ]);
        return response()->json(['status' => 200, 'message' => $req->id ? 'Customer Group Updated Successfully.' : 'Customer Group Created Successfully.']);
        //updating to erply server

    }

    protected function saveCustomerGroupErply($req, $id){
        $group = $this->group->where('id', $id)->first();
        // print_r($group);
        // die;
        $param = array(
            "name" => $req->name, 
        );
         
        if($group->customerGroupID){
            $param['customerGroupID'] = $group->customerGroupID;
        }

        foreach($req->toArray() as $key => $val){
            if(str_contains($key, 'attribute')) {
                // info($key);
                $param["$key"] = $val;
            }
        }

        // print_r($param);
        // die;
        $res = $this->api->sendRequest('saveCustomerGroup', $param);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            //updating customer group 
            // $this->getService->saveCustomerGroup
            return $res['records'][0]['customerGroupID'];
        }
        return response()->json($res);
    }

    public function deleteCustomerGroup($req){
        if($req->id){
            $group = $this->group->where('id',$req->id)->first();
            if(!$group){

                return response()->json([
                    'message' => "Invalid Group ID!"//->first()
                ], 400);
            }
            
            //first deleting to erply db
            return $this->deleteErply($group->customerGroupID, $group);

        }

        if($req->ids){
            $ids = explode(",", $req->ids);
            $groups = $this->group->whereIn('id',$ids)->get();
            if(count($groups) < 1){
                return response()->json(
                    [
                       'status' => 400,
                       'message' => "No Records Founds!"
                    ]
                );
            }

            if(count($groups) > 99){
                return response()->json(
                    [
                       'status' => 400,
                       'message' => "Maximum Records Founds!"
                    ]
                );
            }
            return $this->deleteErplyBulk($groups);
        }
    }


    protected function deleteErply($customerGroupID, $old){
        if($customerGroupID){
            $param = array(
                "customerGroupID" => $customerGroupID
            );
            $res = $this->api->sendRequest('deleteCustomerGroup', $param);
            if($res['status']['errorCode'] == 0){
                info("Customer Group Deleted Erply");
                $this->group->where('customerGroupID', $customerGroupID)->update(['deleted' => 1]);
                $new = $this->group->where('customerGroupID', $customerGroupID)->first();

                //set Log
                $this->letsLog->setLog(json_encode($old, true), json_encode($new, true), "Group Deleted");

                return response()->json(
                    [
                       'status' => 200,
                       'message' => "Customer Group Deleted Successfully."
                    ]
                );
            }
            
            return response()->json($res);
        }
    }

    protected function deleteErplyBulk($groups){
        $token = $this->api->verifySessionByKey($this->api->client->sessionKey);
        $bulkParam = array();

        foreach($groups as $g){
            $param = array(
                "requestName" => "deleteCustomerGroup",
                "sessionKey" => $token,
                "clientCode" => $this->api->client->clientCode,
                "customerGroupID" => $g->customerGroupID
            );
            array_push($bulkParam, $param);
        }

        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $token
        );

        if(count($bulkParam) < 1){
            return response()->json(
                [
                   'status' => 400,
                   'response' => "No Records Founds!"
                ]
            );
        }
        $bulkParam = json_encode($bulkParam, true);
        // print_r($bulkParam);
        // die;
        $res = $this->api->sendRequest($bulkParam, $bulkparam, 1);

        if($res['status']['errorCode'] == 0){
            info("Customer Groups Deleted Erply");
            foreach($groups as $g){
                $this->group->where('customerGroupID', $g->customerGroupID)->delete();
            }
            // $this->group->where('customerGroupID', $customerGroupID)->delete();
            return response()->json(
                [
                    'status' => 200,
                    'response' => "Customer Group Deleted Successfully."
                ]
            );
        }
        
        return response()->json($res);
        
    }
 



}
