<?php
namespace App\Http\Controllers\Services;

use App\Models\Category;
use App\Models\Client;

class GroupService
{
    protected $service;
    protected $category;
    protected $client;
    public function __construct(EAPIService $s, Category $category, Client $c)
    {
        $this->service = $s;
        $this->category = $category;
        $this->client = $c;
    }

    public function updateMatrixGroup($req){
        $sessionKey = $this->client->sessionKey; //$this->service->verifySessionByKey($this->client->sessionKey);
        $limit = $req->limit = '' ? 5 : $req->limit;
        $cat = $this->category->where('erplyGroupPending', 1)->limit($limit)->get();
         
        // saveProductCategory
        $bulkParams = array();
        $bulkParam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $sessionKey,
        );
        
        foreach($cat as $c){
            $param = $this->getBulkAttributes($c,$sessionKey);
            array_push($bulkParams, $param); 
        }

        return response()->json("Group ErplyID Updated Successfully");

        $bulkParams = json_encode($bulkParams, true);

        $bulkRes = $this->service->sendRequest($bulkParams, $bulkParam,1,0,0);
        // info($bulkRes);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($cat as $key => $c){
                $c->erplyGroupID = $bulkRes['requests'][$key]['records'][0]['productGroupID'];
                $c->erplyGroupPending = 0;
                $c->save();
            }
            info("group bulk save or updated ");

            // return response()->json(['status'=>200, 'data'=>$bulkRes]);
        }
        echo "Saved / Updated Groups";
        // return response()->json(['status'=>401, 'data'=>$bulkRes]);


    }

    public function getGroupByCatID($catid){
        info("preparing for group check or create");
        // $cat = $this->category->where('erplyGroupPending', 1)->limit(5)->get();
        $cat = $this->category->where('ciCategoryID', $catid)->first();
        // saveProductCategory
        if($cat){
            $param = $this->getAttributes($cat);
            $res = $this->service->sendRequest("saveProductGroup", $param);
            // $cate = Category::findOrfail($c->newSystemcategoryID);
            if($res['status']['errorCode'] == 0 && !empty($res['records'])){
                $cat->erplyGroupID = $res['records'][0]['productGroupID'];
                $cat->erplyGroupPending = 0;
                $cat->save();
                // print_r($res);
                return $res['records'][0]['productGroupID'];
            } 

            // echo "Category Groups Created or Updated Successfully. ERPLY GROUP ID ".$res['records'][0]['productGroupID'];  
        }
         


    }
    
 

    protected function getAttributes($c){
        $param = array(
            "name" => $c->ciCategoryTitle, 
         ); 
         if($c->erplyGroupID > 0){
            //checking product group ID
            $gParam = array(
                "productGroupID" => $c->erplyGroupID
            );
            $res = $this->service->sendRequest("getProductGroups", $gParam);
            if($res['status']['errorCode'] == 0 && !empty($res['records'])){
                $param['productGroupID'] = $c->erplyGroupID;
            }  
            // 
         }
         $index = 1;
        foreach($c->toArray() as $key => $att){
            if($key == 'ciCategoryID' || $key == 'internetActive' || $key == 'ciCategorySequence' || $key == 'displayPosition'){
                $param["attributeName".$index] = $key;
                $param["attributeType".$index] =  $key == 'ciCategoryID' ? "varchar(100)" : ($key == 'displayPosition' ? 'float(10,2)' : 'int') ;
                $param["attributeValue".$index] = $att;
                $index++;
            }    
        }
        return $param;
    }

    protected function getBulkAttributes($c,$sessionKey){
        $param = array(
            "requestName" => "saveProductGroup",
            "sessionKey" => $sessionKey,
            "clientCode" => $this->client->clientCode,
            "name" => $c->ciCategoryTitle, 
         ); 
        //  if($c->erplyGroupID > 0){
            //checking product group ID
            $gParam = array(
                "searchAttributeName" => 'ciCategoryID', 
                "searchAttributeValue" => $c->ciCategoryID,
                "sessionKey" => $sessionKey
            );
            $res = $this->service->sendRequest("getProductGroups", $gParam,0,0,0);
            if($res['status']['errorCode'] == 0 && !empty($res['records'])){
                info("Group ID Exist ". $res['records'][0]['productGroupID']);
                //updating group id if not exist
                $c->erplyGroupPending = 0;
                $c->erplyGroupID = $res['records'][0]['productGroupID'];
                $c->save();
                $param['productGroupID'] = $res['records'][0]['productGroupID'];
            } 

            // 
        //  }
         $index = 1;
        foreach($c->toArray() as $key => $att){
            if($key == 'ciCategoryID' || $key == 'internetActive' || $key == 'ciCategorySequence' || $key == 'displayPosition'){
                $param["attributeName".$index] = $key;
                $param["attributeType".$index] =  $key == 'ciCategoryID' ? "varchar(100)" : ($key == 'displayPosition' ? 'float(10,2)' : 'int') ;
                $param["attributeValue".$index] = $att;
                $index++;
            }    
        }
        return $param;
    }

     


}