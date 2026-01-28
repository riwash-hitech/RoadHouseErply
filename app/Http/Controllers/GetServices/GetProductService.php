<?php
// namespace App\Http\Controllers\GetServices;

// use App\Http\Controllers\Services\EAPIService;
// use App\Models\Client; 

// class GetProductService
// {
//     protected $api;
//     protected $client;
//     public function __construct(EAPIService $api, )
//     {
//         $this->api = $api;
//         $this->api->client = $client;

//     }

//     public function getProducts($req){
//         $param = array(
//             "type" => $req->type == '' ? "MATRIX" : $req->type,
//             "includeMatrixVariations" => $req->includeMatrixVariations == '' ?  0 : $req->includeMatrixVariations,
//             "orderBy" => "changed",
//             "orderByDir" => $req->orderByDir,
//             "recordsOnPage" => $req->recordsOnPage,
//             "changedSince" => $req->changedSince, 
//             "pageNo" => $req->pageNo,
//             "productID" => $req->productID,
//             "productIDs" => $req->productIDs
//          ); 

//         //  $data = $this->
          

//          $req = $this->api->sendRequest("getProducts", $param);
//          if($req['status']['errorCode'] == 0){
//             return response()->json($req);
//          }
//     }
    
 



     



// }