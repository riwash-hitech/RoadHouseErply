<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\CategoryService;
use App\Http\Controllers\Services\EAPIService;
use App\Models\ArchiveProduct;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Exception;

class ProductBulkDeleteController extends Controller
{
    use ResponseTrait;
    //
    protected $api;
    protected $archive;

    public function __construct(EAPIService $api, ArchiveProduct $ap)
    {
        $this->api = $api;
        $this->archive = $ap;
    }

    public function getArchive(){
        $param = [
            'take' => '3000',
            'filter' => '[ ["status","=","ARCHIVED"], "and", ["added",">=",'.  $this->getLast().'] ]'
        ];

         $res = $this->api->sendRequestBySwagger("https://api-pim-au.erply.com/v1/product", $param);
         

        // if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            foreach($res as $key => $product){
                $this->archive->updateOrcreate(
                    [
                        "productID" => $product['id']
                    ],
                    [
                        "productID" => $product['id'],
                        "type" => $product['type'],
                        "status" => $product['status'],
                        "lastModified" => date('Y-m-d H:i:s', $product['added']), 
                    ]
                );
            }

            echo "Successfully created product";
        // }
    }

    public function getLast(){
        $latest = $this->archive->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;
    }

    public function deleteProducts(Request $req){

        // return $this->successWithData($this->api->client);
        // die;
        
        $pageno = 0;
        // try{
        $pages = file(public_path()."/pageno.txt");
        foreach($pages as $p){
            $pageno = $p;
        }
        // }catch(Exception $e){
        //     $fh = fopen("pageno.txt","w");
        //     fwrite($fh, "1");
        //     fclose($fh);
        // }
        // echo $pageno + 1;
        // $fh = fopen("pageno.txt","w");
        // fwrite($fh, $pageno + 1);
        // fclose($fh);
        // die;

         

        //first getting archived products
        $param = array(
            
            "orderByDir" => "asc",
            "recordsOnPage" => "3",
            "pageNo" => $pageno + 1,
            "getRecipes" => 1,
            "getParameters" => 1,
            "getRelatedProducts" => 1,
            "getStockInfo" => 1,
            "getPriceListPrices" => 1,
            "active" => 0, 
         );

        //  print_r($param);
        //  die;
        $res = $this->api->sendRequest("getProducts", $param);
        // return $this->successWithData($res);
        $bulkParam = array();
        $pids = '';
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            foreach($res['records'] as $key => $product){
                // array_push($bulkParam, 
                // array(
                //     "requestName" => "deleteProduct", 
                //     "clientCode" => $this->api->client->clientCode, 
                //     "sessionKey" => $this->api->client->sessionKey,   
                //     "productID" => $product['productID']
                //     )
                // );

                 $pids .=  $key > 0 ? ";".$product['productID'] : $product['productID'];
                 $sres = $this->api->sendRequestBySwaggerWithoutData("https://api-pim-au.erply.com/v1/product/". urlencode($pids));
                 if(@$sres['message']){
                    //item not deleted
                    $fh = fopen(public_path()."/notdeleted.txt","a");
                    fwrite($fh, $product['productID'].',');
                    fclose($fh);
                 }else{
                    $fh = fopen(public_path()."/deleted.txt","a");
                    fwrite($fh, $product['productID'].',');
                    fclose($fh);
                 }
                //  if(!empty($product['warehouses'])){
                //     $this->checkInventoryAndDelete($product['warehouses'], $product['productID']);
                //  }
                // $this->getProductStock($product['productID']);

            }
        }
        $fh = fopen(public_path()."/pageno.txt","w");
        fwrite($fh, $pageno + 1);
        fclose($fh);
        return $this->successWithMessage("Product Deleted Successfully.");
        die;
        // info($res);

        //now deleting warehouses if exist
        
        // $bparam = array(
        //     "lang" => 'eng',
        //     "responseType" => "json",
        //     "sessionKey" => $this->api->client->sessionKey,
        // );
        // $bulkParam = json_encode($bulkParam, true);
        // $sres = $this->api->sendRequest($bulkParam,$bparam,1,1);
        
        

        // $this->service->updateVariantCategory();

    }   

    public function deleteProductUsingTable(Request $req){
        $limit = $req->limit ? $req->limit : 10;
        $data = $this->archive->where('checked', 0)->limit($limit)->get();
        $pids = '';
        foreach($data as $key => $d){
            $pids .=  $key > 0 ? ";".$d['productID'] : $d['productID']; 
            $d->checked = 1;
            $d->save();
        }
        $sres = $this->api->sendRequestBySwaggerWithoutData("https://api-pim-au.erply.com/v1/product/". urlencode($pids));
         
        return response()->json(['status' => 200, "message" => "Archived Product Deleted Successfully."]);


    }

    protected function getProductStock($pid){
        $param = array(
            "productID" => $pid,
            "warehouseID" => 1
        );

        $res = $this->api->sendRequest("getProductStock", $param,0,1);
        print_r($res);
        die;
    }


    protected function checkInventoryAndDelete($warehouses,$pid){
        // return $this->successWithData($warehouses);
        info("hello im called inv del");
        $bulkParam = array( 
        );
        $params = array(
            "lang" => 'eng',
            "responseType" => "json",
            "sessionKey" => $this->api->client->sessionKey,
        );

        foreach($warehouses as $ware){
            $param = array(
                'warehouseID' => $ware['warehouseID'],
                'sessionKey' => $this->api->client->sessionKey,
            );
            $res = $this->api->sendRequest("getInventoryRegistrations", $param,0,0,0);
            info($res);
            if($res['status']['errorCode'] == 0 && !empty($res['records'])){
                // info($res['records']);
                // print_r($res['records']);
                // die;
                foreach($res['records'] as $val){
                    // echo $val['warehouseID'];
                    // info($val);
                    if(@$val['rows']){
                        foreach($val['rows'] as $pro){
                            if($pro['productID'] == $pid){
                                // echo "Product Inventory Registered";
                                info("Inventory Exist". $val['inventoryRegistrationID']);
                                array_push(
                                    $bulkParam, array(
                                        'requestName' => "deleteInventoryRegistration" ,
                                        'inventoryRegistrationID' => $val['inventoryRegistrationID'],
                                        'sessionKey' => $this->api->client->sessionKey,
                                    )
                                ); 
                            }
                        }
                    }
                } 
            }
        }
        info($bulkParam);
        // die;
        if(count($bulkParam) > 0){
            $bulkParam = json_encode($bulkParam, true);
            // now deleting inventory
            // info("Deleting Inventory...");
            info("inventory reg del api calling...");
            $res = $this->api->sendRequest($bulkParam, $params,1,0,0);
            info("inv del res". $res);
            if($res['status']['errorCode'] == 0){
                info("Delete Inventory Registration Successfully.".$res['records']);
            }
        }else{
            info("No Bulk request found");
        }
         
    }

    
    
}
