<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Assortment;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\PriorityGroup;
use App\Models\PAEI\UserOperationLog;
use App\Traits\ResponseTrait;
class GetPriorityGroupService {

    use ResponseTrait; 
    protected $group;
    protected $api;

    public function __construct(PriorityGroup $group, EAPIService $api){
        $this->group = $group;
        $this->api = $api;
    }


    public function saveUpdate($assortments){

        foreach($assortments as $c){
            $this->saveUpdateAssortment($c);
        }

        return response()->json(['status'=>200, 'message'=>"Assortment fetched Successfully."]);
    }

    protected function saveUpdateAssortment($product){
        PriorityGroup::updateOrCreate(
            [
                "clientCode" => $this->api->client->clientCode,
                "priorityGroupID"  =>  $product['priorityGroupID']
            ],
            [
                "clientCode" => $this->api->client->clientCode,
                "priorityGroupID" => @$product["priorityGroupID"],
                "priorityGroupName" => @$product["priorityGroupName"], 
                "added" =>  date('Y-m-d H:i:s', @$product['added']),
                "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',@$product['lastModified']) : date('Y-m-d H:i:s', @$product['added']), 
            ]
        );
    }

    public function getLastUpdateDate(){
        // echo "im call";
         $latest = PriorityGroup::orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }


      
}


