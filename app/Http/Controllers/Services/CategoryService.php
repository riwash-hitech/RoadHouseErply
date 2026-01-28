<?php
namespace App\Http\Controllers\Services;

use App\Models\Category;
use App\Models\Client;
use App\Models\TempCat;

class CategoryService
{
    protected $service;
    protected $category;
    protected $client;
    protected $temp_cat;
    public function __construct(EAPIService $s, Category $category, Client $c)
    {
        $this->service = $s;
        $this->category = $category;
        $this->client = $c;
    }

    public function updateMatrixCategory($req){
        $limit = $req->limit == '' ?  5 : $req->limit; 
        $cat = $this->category->where('erplyCatPending', 1)->limit($limit)->get();
        $sessionKey =$this->client->sessionKey;
        // saveProductCategory
        $bulkParams = array();
        $bulkParam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $sessionKey,
        );
        foreach($cat as $c){
            $param = $this->getAttributesBulk($c, $sessionKey); 
            array_push($bulkParams, $param);
        }

        //temp
        return response()->json(['status'=> 200, 'response'=>"Category Updated"]);

        $bulkParams = json_encode($bulkParams, true);
        $bulkRes = $this->service->sendRequest($bulkParams, $bulkParam,1,0,0);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($cat as $key => $c){ 
                $c->erplyCatID = $bulkRes['requests'][$key]['records'][0]['productCategoryID']; 
                $c->erplyCatPending = 0;
                $c->save(); 
            }   
            info("category bulk updated");
        }
        return response()->json(['status'=> 200, 'response'=>$bulkRes]);



    }
    
    public function getCategoryByID($id){
        info("preparing for category check or create");
        // $cat = $this->category->where('erplyGroupPending', 1)->limit(5)->get();
        $cat = $this->category->where('ciCategoryID', $id)->first();
        // saveProductCategory
        if($cat){
            $param = $this->getAttributesVariant($cat);
            $res = $this->service->sendRequest("saveProductCategory", $param);
            // $cate = Category::findOrfail($c->newSystemcategoryID);
            $cat->erplyCatID = $res['records'][0]['productCategoryID'];
            $cat->erplyCatPending = 0;
            $cat->save();
            // print_r($res);
            return $res['records'][0]['productCategoryID'];
            // echo "Category Groups Created or Updated Successfully. ERPLY GROUP ID ".$res['records'][0]['productGroupID'];  
        }
         
    }

    public function updateVariantCategory(){
        // echo "hello";
        // die;
        $cat = $this->category->where('erplyCatPending', 1)->limit(5)->get();
        // print_r(json_decode($cat));
        // die;
        // saveProductCategory
        foreach($cat as $c){
           
            // check first category exist or not
            
            $param = $this->getAttributesVariant($c);
             
            $res = $this->service->sendRequest("saveProductCategory", $param);
            $c->erplyCatID = $res['records'][0]['productCategoryID'];
            $c->save(); 
            // // print_r($res);
            // echo "Category Variants Created Successfully. ERPLY CAT ID ".$res['records'][0]['productCategoryID'];  
            // echo $res['records'][0]['productCategoryID'];
             
        }
    }

    protected function getAttributes($c){
        $param = array(
            
            "name" => $c->ciCategoryTitle, 
         ); 
         if($c->erplyCatID > 0){
            $catParam = array(
                "searchAttributeName" => 'ciCategoryID', 
                "searchAttributeValue" => $c->ciCategoryID,
            );
            
            $res = $this->service->sendRequest("getProductCategories", $catParam);
            if(!empty($res['records'])){
                $param['productCategoryID'] = $res['records'][0]['productCategoryID'];
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

    protected function getAttributesBulk($c, $sessionKey){
        $param = array(
            "requestName" => "saveProductCategory",
            "sessionKey" => $sessionKey,
            "clientCode" => $this->client->clientCode,
            "name" => $c->ciCategoryTitle, 
         ); 
        //  if($c->erplyCatID > 0){
            $catParam = array(
                "searchAttributeName" => 'ciCategoryID', 
                "searchAttributeValue" => $c->ciCategoryID,
                "sessionKey" => $sessionKey
            );
            
            $res = $this->service->sendRequest("getProductCategories", $catParam,0,0,0);
            if($res['status']['errorCode'] == 0 &&  !empty($res['records'])){
                // $this->category->where('newSystemcategoryID', $c->newSystemcategoryID)->update(['erplyCatID'=> ])
                $c->erplyCatID = $res['records'][0]['productCategoryID'];
                $c->erplyCatPending = 0;
                $c->save();
                $param['productCategoryID'] = $res['records'][0]['productCategoryID'];
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

    protected function getAttributesVariant($c){
        $param = array( 
            "name" => $c->ciCategoryTitle,
            // "parentCategoryID" => $c->ciCategoryParentID,
         );

        if($c->erplyCatID){
            $catParam = array(
                "searchAttributeName" => 'ciCategoryID', 
                "searchAttributeValue" => $c->ciCategoryID,
            );
            
            $res = $this->service->sendRequest("getProductCategories", $catParam);
            if(!empty($res['records'])){
                $param['productCategoryID'] = $res['records'][0]['productCategoryID'];
            }
           
        } 
         
        if($c->ciCategoryParentID > 0){
            
                $param['parentCategoryID'] = $c->ciCategoryParentID;
            
        }

        //  die;
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
    
    public function pushTempCategory(){
        
        //first pushing parent;
        // $pcat = TempCat::where("erplyPending", 0)->where("parent_id", 0)->get();
        // $pids = array();
        // foreach($pcat as $p){
        //     $pids[] = $p->cat_id;
        // }
        // dd($pids);
        
        $cat = TempCat::where("erplyPending", 1)->limit(50)->get();
        
        // dd($cat);
        
        $bulkReq = array();
        
        $validCategory = array();
        
        foreach($cat as $c){
            $p_cat = TempCat::where("cat_id", $c->parent_id)->where("erplyPending", 0)->first();
            
            if($p_cat){
                $param = array(
                    "requestName" => "saveProductCategory",
                    "clientCode" => $this->service->client->clientCode,
                    "sessionKey" => $this->service->client->sessionKey,
                    "name" => $c->name,
                    "parentCategoryID" => $p_cat->erplyCatID,
                    "attributeName1" => "sequence",
                    "attributeType1" => "text",
                    "attributeValue1" => $c->sequence,
                    
                );
                array_push($bulkReq, $param);    
                array_push($validCategory, $c);
            }
            
        }
        
        if(count($bulkReq) < 1){
            return response("No Category Found");
            die;
        }
        
        // dd($validCategory);
        // dd($bulkReq);
        $bulkReq = json_encode($bulkReq, true);
        // dd($bulkReq);
        $bp = array(
                "sessionKey" => $this->service->client->sessionKey
            );
        $res = $this->service->sendRequest($bulkReq,$bp,1);
        
        if($res['status']['errorCode'] == 0 && !empty($res["requests"])){
            
            foreach($validCategory as $key => $cc){
                if($res['requests'][$key]['status']['errorCode'] == 0){
                    
                    TempCat::where("id", $cc["id"])->update([ "erplyCatID" => $res['requests'][$key]['records'][0]['productCategoryID'], "erplyPending" => 0 ]);
                    // $cc->erplyCatID = $res['requests'][$key]['records'][0]['productCategoryID'];
                    // $cc->erplyPending = 0;
                    // $cc->save();
                }else{
                    info("Error while saving category");
                }
                
            }
        }
        
        return response()->json($res);
        
        
        
    }


}