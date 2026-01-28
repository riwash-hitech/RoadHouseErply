<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\CurrentCustomer;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected $api;
    protected $customer;
 

    public function __construct(EAPIService $api, CurrentCustomer $cc)
    {
        $this->api = $api;
        $this->customer = $cc;
 
    }

    public function saveCustomer($req){
        $limit = $req->limit == '' ? 5 : $req->limit;
        //FIRST VERIFYING USER

        $customer = $this->customer
                    ->where('erplyPending', 1)
                    ->where('webActive', 1)
                    ->whereIn('ciCustomerGroup',['Retail', 'Wholesale'])
                    ->where('ciEmail', '<>', '')
                    ->limit($limit)
                    ->get();
        // dd($customer);
        // die;
        $bulkCustomer = $this->makeBundleJSON($customer);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey
        );

        $bulkRes = $this->api->sendRequest($bulkCustomer, $bulkparam, 1,0,0);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($customer as $key => $c){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplyCustomerID = $bulkRes['requests'][$key]['records'][0]['customerID'];
                    $c->erplyPending = 0;
                    $c->save();
                    info("Customer created ". $bulkRes['requests'][$key]['records'][0]['customerID']);
                }else{
                    $c->error = $bulkRes['requests'][$key]['status']['errorCode'];
                    $c->save();
                    info("Error While creating customer". $bulkRes['requests'][$key]['status']['errorCode']);
                }
            }
            // Log::info($bulkRes);
            // return response()->json(['status' => 200, 'data' => $bulkRes]);
        }    
        // return response()->json( ['status' => 401,'data'=> $bulkRes]);

    }


    protected function makeBundleJSON($data){
        $verifiedSessionKey = $this->api->client->sessionKey; //$this->api->verifySessionByKey($this->api->client->sessionKey);
        $BundleArray = array();
        foreach($data as $customer){
            $reqArray = array(
                "requestName" => "saveCustomer",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "firstName" => $customer->ciFirstName,
                "lastName" => $customer->ciLastName,
                "groupID" => $customer->ciCustomerGroup == 'Retail' ? 16 : 17,
                "email" => $customer->ciEmail,
                "phone" => $customer->ciPhone,
                "mobile" => $customer->ciMobile,
                "website" => $customer->ciWebsite,
                "countryID" => 25, //25 Australia 

            );

            $customerID = $this->checkCustomer($customer->ciEmail);
            if($customerID != ''){
                $reqArray['customerID'] = $customerID;
            }

            //NOW ADDING ATTRIBUTES
            $index = 1;
            foreach($customer->toArray() as $key => $c){
                if($key == "ciCustomerID" || $key == "ciTradingName" || $key == "ciAddress" || $key == "CreditMax" || $key == "creditBalance"){
                    $param["attributeName".$index] = $key;
                    $param["attributeType".$index] = $key == 'CreditMax' || $key == 'creditBalance' ? 'float' : ($key == 'ciAddress' ?  'varchar(500)' : 'varchar(100)');
                    $param["attributeValue".$index] = $c;
                    $index++;
                }
            }

            array_push($BundleArray,$reqArray );
             
        }

        $BundleArray = json_encode($BundleArray, true);
        return $BundleArray; 
    }

    protected function checkCustomer($email){
        $param = array(
            "searchEmail" => $email,
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest("getCustomers", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Log::info("Customer exist ID".$res['records'][0]['customerID']);
            return $res['records'][0]['customerID'];
        }

        return '';

    }


     

}