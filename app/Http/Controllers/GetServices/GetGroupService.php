<?php
// namespace App\Http\Controllers\GetServices;

// use App\Http\Controllers\Services\EAPIService;
// use App\Models\Client;
// use Illuminate\Http\Request;

// class GetGroupService
// {
//     protected $api;
//     protected $client;
//     public function __construct(EAPIService $api,  )
//     {
//         $this->api = $api;
//         $this->api->client = $client;

//     }
    
//     public function getGroups($req){
//         $param = array(
//             "productGroupID" => $req->productGroupID,
//             "showInWebshop" => $req->showInWebshop,
//             "changedSince" => $req->changedSince, 
//         );
//         $req = $this->api->sendRequest("getProductGroups", $param);
//         if($req['status']['errorCode'] != 0){
//             info($req);
//         }
//         return response()->json($req);
//     }


// }