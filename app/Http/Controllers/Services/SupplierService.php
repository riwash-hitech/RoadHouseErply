<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\CurrentCustomer;
use Illuminate\Support\Facades\Log;

class SupplierService
{
    protected $api;
    protected $supplier; 

    public function __construct(EAPIService $api, CurrentCustomer $cc)
    {
        $this->api = $api;
        $this->supplier = $cc;
 
    }

    public function saveSupplier($req){
        $limit = $req->limit == '' ? 5 : $req->limit;
        //FIRST VERIFYING USER

        $suppliers = $this->supplier->where('ciEmail', '<>', '')
                    ->where('erplyPending', 1)
                    ->where('webActive', 1)
                    ->whereIn('ciCustomerGroup',['Retail', 'Wholesale'])
                    ->limit($limit)
                    ->get();
        // dd($customer);
        // die;
        $bulkCustomer = $this->makeBundleJSON($suppliers);
        $bulkparam = array(
            "lang" => 'eng',
            "responseType" => "json", 
            "sessionKey" => $this->api->client->sessionKey
        );

        $bulkRes = $this->api->sendRequest($bulkCustomer, $bulkparam, 1,0,0);
        if($bulkRes['status']['errorCode'] == 0 && !empty($bulkRes['requests'])){
            foreach($suppliers as $key => $c){
                if($bulkRes['requests'][$key]['status']['errorCode'] == 0){
                    $c->erplySupplierID = $bulkRes['requests'][$key]['records'][0]['supplierID'];
                    $c->erplyPending = 0;
                    $c->save();
                    info("Customer created ". $bulkRes['requests'][$key]['records'][0]['supplierID']);
                }else{
                    $c->error = $bulkRes['requests'][$key]['status']['errorCode'];
                    $c->save();
                    info("Error While creating supplier". $bulkRes['requests'][$key]['status']['errorCode']);
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
        foreach($data as $supplier){
            $reqArray = array(
                "requestName" => "saveSupplier",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "firstName" => $supplier->ciFirstName,
                "lastName" => $supplier->ciLastName,
                "fullName" => $supplier->ciLastName,
                "groupID" => $supplier->ciCustomerGroup == 'Retail' ? 16 : 17,
                "supplierManagerID" => $supplier->ciCustomerGroup == 'Retail' ? 16 : 17,
                "companyName" => $supplier->ciEmail,
                "companyTypeID" => $supplier->ciPhone,
                "code" => $supplier->ciMobile,
                "companyID" => $supplier->ciMobile,
                "birthday" => $supplier->ciMobile,
                "vatNumber" => $supplier->ciMobile,
                "bankName" => $supplier->ciMobile,
                "bankAccountNumber" => $supplier->ciMobile,
                "bankIBAN" => $supplier->ciMobile,
                "bankSWIFT" => $supplier->ciMobile,
                "phone" => $supplier->ciMobile,
                "mobile" => $supplier->ciMobile,
                "fax" => $supplier->ciMobile,
                "email" => $supplier->ciMobile,
                "skype" => $supplier->ciMobile,
                "website" => $supplier->ciMobile,
                "website" => $supplier->ciWebsite,
                "integrationCode" => "",
                "vatrateID" => "", //25 Australia 
                "currencyCode" => "",
                "deliveryTermsID" => "",
                "countryID" => "",
                "GLN" => "",
                "paymentDays" => "",
                
            );

            $supplierID = $this->checkSupplier($supplier->Email);
            if($supplierID != ''){
                $reqArray['supplierID'] = $supplierID;
            }

            //NOW ADDING ATTRIBUTES
            $index = 1;
            foreach($supplier->toArray() as $key => $c){
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

    protected function checkSupplier($email){
        $param = array(
            "searchName" => $email,
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest("getSuppliers", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Log::info("Supplier exist ID".$res['records'][0]['supplierID']);
            return $res['records'][0]['supplierID'];
        }

        return '';

    }


     

}