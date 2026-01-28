<?php
namespace App\Http\Controllers\Paei\Services;

use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\CustomerGroup;
use App\Models\PAEI\UserOperationLog;
use App\Traits\ResponseTrait;
class GetUserOperationServiceV2 {

    use ResponseTrait; 
    protected $customer; 
    protected $letsLog;
  

    public function __construct(UserLogger $logger){//UserOperationInterface $uoi){
 
        $this->letsLog = $logger;
       
 
    }
    
    public function handleDelete($erplyID, $table, $clientCode){

        
            if($table == "customers"){
                $old = Customer::where('clientCode', $clientCode)->where('customerID', $erplyID)->first();
                if($old){
                    $change = Customer::where('clientCode', $clientCode)->where('customerID', $erplyID)->update(['deleted' => 1]);
                    $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), "Customer Deleted");    
                }
            }

            if($table == "customerGroups"){
                $old = CustomerGroup::where('clientCode', $clientCode)->where('customerGroupID', $erplyID)->first();
                if($old){
                    $change = CustomerGroup::where('clientCode', $clientCode)->where('customerGroupID', $erplyID)->update(['deleted' => 1]);
                    $this->letsLog->setChronLog($old ? json_encode($old, true) : '', json_encode($change, true), "Customer Group Deleted");    
                }
            }
            
        // return response()->json(["status" => 200, "message" => "Customer Operation Fetched Successfully."]);
    }
    
    

   


}
 