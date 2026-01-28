<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Services\ProductService;
use App\Models\Product;
use App\Models\ProductVariant;


 

class ProductController extends Controller
{
    //
     
    
    protected $service; 
    public $page = 1;
    public function __construct( ProductService $ps)
    {
        // session_start();
        $this->service = $ps;
        // $this->api = $api;    
        // $this->api->clientCode = "466822";
        // $this->api->username = "support@retailcare.com.au";
        // $this->api->password = "RCare123@#$";
        // $this->api->url = "https://".$this->api->clientCode.".erply.com/api/";
    }

    public function getProduct(){
        
        // $this->getLastUpdateDate(); 
        $inputParameters = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "pageNo" => $this->page,
            "changedSince" => $this->getLastUpdateDate(), 
         ); 
        //Handling products 
        $this->service->handleProduct($inputParameters);
        
    }


    
 

    public function create(){
        echo "test ok";
        die;
    }

    public function getProductsReport(\Illuminate\Http\Request $req){
        return $this->service->getProductsReport($req);
    }


}
