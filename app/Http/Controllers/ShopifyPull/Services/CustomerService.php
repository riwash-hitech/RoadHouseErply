<?php

namespace App\Http\Controllers\LivePushErply\Services;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\EAPIService;
use App\Models\Shopify\ShopifyCustomer;
use Illuminate\Http\Request;

class CustomerService{
    //
    protected $api; 

    public function __construct(EAPIService $api){
        $this->api = $api;  
    }

    public function syncCustomerToErply(){

        $customers = ShopifyCustomer::
                        // ->join("newsystem_customer_business_relations", "newsystem_customer_business_relations.PSW_SMMCUSTACCOUNT", "newsystem_customer_flag.ACCOUNTNUM")
                        where("erplyPending", 1)
                        ->limit(50)
                        ->get();
                        
        if($customers->isEmpty()){
            info("All Customer  Synced to Erply.");
            return response("All Customer  Synced to Erply.");
        }
        $BundleArray = array();
        foreach($customers as $customer){

            // dd($customer->toArray());
            //now getting home store id
            // $storeID = 0;
            // if($customer->SAB_RBOSTOREPRIMARY){
            //     $warehouse = LiveWarehouseLocation::where("LocationID", $customer->SAB_RBOSTOREPRIMARY)->first();
            //     if($warehouse){
            //         $storeID = $warehouse->erplyID;
            //     }
            // }

            $reqArray = array(
                "requestName" => "saveCustomer",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "firstName" => $customer->firstName,
                "lastName" => $customer->lastName,
                // "groupID" => $customer->CUSTGROUP == 'Retail' ? 16 : ($customer->CUSTGROUP == "Wholesale" ? 15 : 17),
                "email" => $customer->emailAddress,
                "phone" => $customer->homePhone,
                "mobile" => $customer->mobile,
                "fax" => $customer->fax,
                "countryID" => 25, //25 Australia 
                "trimInputData" => 1,
                // "credit" => (integer)$customer->CREDITMAX,
                "attributeName1" => "ShopifyID",
                "attributeType1" => 'text',
                "attributeValue1" => $customer->newSystemMemberID,
                "attributeName2" => "Street",
                "attributeType2" => 'text',
                "attributeValue2" => $customer->street,
                "attributeName3" => "City",
                "attributeType3" => 'text',
                "attributeValue3" => $customer->city,
                "attributeName4" => "PostCode",
                "attributeType4" => 'text',
                "attributeValue4" => $customer->postCode,
                "attributeName5" => "State",
                "attributeType5" => 'text',
                "attributeValue5" => $customer->state,
                "attributeName6" => "Country",
                "attributeType6" => 'text',
                "attributeValue6" => $customer->country,
                // "attributeName7" => "ZIPCODE",
                // "attributeType7" => 'text',
                // "attributeValue7" => $customer->ZIPCODE,
                // "attributeName8" => "STATE",
                // "attributeType8" => 'text',
                // "attributeValue8" => $customer->STATE,


            
            );

             
            $customerID = $this->checkCustomer($customer->emailAddress);
            if($customerID != ''){
                $reqArray['customerID'] = $customerID;
            }

            //NOW ADDING ATTRIBUTES
            // $index = 1;
            // $tt = $customer->toArray();
            // foreach($tt as $key => $c){
            //     if($key == "ACCOUNTNUM"){
            //         echo $key;
            //         die;
            //     }else{
            //         echo " no";
            //         die;
            //     }
            //     // echo $key;
            //     // die;
            //     if($key == 'ACCOUNTNUM' || $key == 'SAB_RBOSTOREPRIMARY' || $key == 'STATUS'){
            //         $param["attributeName".$index] = $key;
            //         $param["attributeType".$index] = 'text';
            //         $param["attributeValue".$index] = $c;
            //         $index++;
            //     }
            // }
            

            array_push($BundleArray,$reqArray );
                
        }

        // dd($BundleArray);
        if(count($BundleArray) < 1){
            return response(" Synccare to Erply : All Customer Synced");
        }

        $BundleArray = json_encode($BundleArray, true);

        $param = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
        $res = $this->api->sendRequest($BundleArray, $param, 1);

        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            foreach($customers as $key => $c){
                if($res['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplyCustomerID = $res['requests'][$key]['records'][0]['customerID'];
                    $c->erplyPending = 0;
                    $c->save();
                }
            }
            info("Customer Created or Updated to Erply");
        }

        return response()->json($res);

    }


    protected function checkCustomer($email){
        $param = array(
            "searchEmail" => $email,
            // "searchAttributeValue" => $an,
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest("getCustomers", $param,0,0,0);
        // dd($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            info("Customer exist ID".$res['records'][0]['customerID']);
            return $res['records'][0]['customerID'];
        }

        return '';

    }
    
    public function saveCustomerAddress(){
        
        $customers = ShopifyCustomer::where("erplyPending", 0)->where("erplyAddressPending", 1)->limit(50)->get();
        
        if($customers->isEmpty()){
            info("All Customer Address Synced to Erply.");
            return response("All Customer Address Synced to Erply.");
        }
        $bulk = array();
        foreach($customers as $c){
            
            $param = array(
                "requestName" => "saveAddress",
                "sessionKey" => $this->api->client->sessionKey,
                "clientCode" => $this->api->client->clientCode,
                "ownerID" => $c->erplyCustomerID,
                "typeID" => 1,
                "street" => $c->street ? $c->street : '', 
                "city" => $c->city ? $c->city : '',
                "postalCode" => $c->postCode ? $c->postCode : '',
                "state" => $c->state ? $c->state : '',
                "country" => $c->country ? $c->country : '', 
                
            );
            
            $bulk[] = $param;
            
        }
        // dd($bulk);
        $bulk = json_encode($bulk, true);
        
        $param = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey,
        );
        
        $res = $this->api->sendRequest($bulk, $param, 1);

        if($res['status']['errorCode'] == 0 && !empty($res['requests'])){
            foreach($customers as $key => $c){
                if($res['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplyAddressID = $res['requests'][$key]['records'][0]['addressID'];
                    $c->erplyAddressPending = 0;
                    $c->save();
                }
            }
            info("Customer Address Created or Updated to Erply");
        }

        return response()->json($res);
        
        
    }
    
    
     
 
 
}
