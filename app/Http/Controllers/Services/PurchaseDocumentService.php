<?php
namespace App\Http\Controllers\Services;

use App\Models\Client;
use App\Models\CurrentCustomer;
use App\Models\PurchaseDocument;
use Illuminate\Support\Facades\Log;

class PurchaseDocumentService
{
    protected $api;
    protected $pd; 

    public function __construct(EAPIService $api, PurchaseDocument $pd )
    {
        $this->api = $api;
        $this->pd = $pd;
 
    }

    public function savePurchaseDocument($req){
        $limit = $req->limit == '' ? 5 : $req->limit;
        //FIRST VERIFYING USER

        $customer = $this->pd->where('ciEmail', '<>', '')
                    ->where('erplyPending', 1)
                    ->where('webActive', 1)
                    ->whereIn('ciCustomerGroup',['Retail', 'Wholesale'])
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
                    $c->erplyPdID = $bulkRes['requests'][$key]['records'][0]['id'];
                    $c->erplyPending = 0;
                    $c->save();
                    info("Purchase Document created ". $bulkRes['requests'][$key]['records'][0]['id']);
                }else{
                    $c->error = $bulkRes['requests'][$key]['status']['errorCode'];
                    $c->save();
                    info("Error While creating purchase document". $bulkRes['requests'][$key]['status']['errorCode']);
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
        foreach($data as $pd){
            $reqArray = array(
                "requestName" => "savePurchaseDocument",
                "sessionKey" => $verifiedSessionKey,
                "clientCode" => $this->api->client->clientCode,
                "warehouseID" => $pd->ciFirstName,
                "deliveryAddressID" => $pd->ciLastName,
                "currencyCode" => $pd->cipdGroup == 'Retail' ? 16 : 17,
                "no" => $pd->ciEmail,
                "type" => $pd->ciPhone,
                "date" => $pd->ciMobile,
                "time" => $pd->ciWebsite,
                "confirmInvoice" => $pd->ciWebsite,
                "supplierID" => $pd->ciWebsite,
                "addressID" => $pd->ciWebsite,
                "contactID" => $pd->ciWebsite,
                "employeeID" => $pd->ciWebsite,
                "stateID" => $pd->ciWebsite,
                "currencyRate" => $pd->ciWebsite,
                "paymentDays" => $pd->ciWebsite,
                "paid" => $pd->ciWebsite,
                "transactionTypeID" => $pd->ciWebsite,
                "transportTypeID" => $pd->ciWebsite,
                "deliveryTermsID" => $pd->ciWebsite,
                "deliveryTermsLocation" => $pd->ciWebsite,
                "triangularTransaction" => $pd->ciWebsite,
                "projectID" => $pd->ciWebsite,
                "reasonID" => $pd->ciWebsite,
                "referenceNumber" => $pd->ciWebsite,
                "baseDocumentIDs" => 25, //25 Australia
                "notes" => '', 
                "ediStatus" => '',
                "ediText" => '',
                "documentURL" => '',
                "shipDate" => '',
                "rounding" => '',
                "netTotalForAccounting" => '',
                "totalForAccounting" => '',
                "stableRowID1" => '',
                "productID1" => '',
                "serviceID1" => '',
                "itemName1" => '',
                "vatrateID1" => '',
                "amount1" => '',
                "price1" => '',
                "discount1" => '',
                "packageID1" => '',
                "amountOfPackages1" => '',
                "deliveryDate" => '',
                "supplierPriceListSupplierCode1" => '',
                "supplierPriceListImportCode1" => '',
                "supplierPriceListNotes1" => '',

                 
            );
            // Additional attributes associated with this item.
            $pdID = $this->checkPurchaseDocument($pd->ciEmail);
            if($pdID != ''){
                $reqArray['id'] = $pdID;
            }

            //NOW ADDING ATTRIBUTES
            $index = 1;
            foreach($pd->toArray() as $key => $c){
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

    protected function checkPurchaseDocument($email){
        $param = array(
            "id" => $email,
            "sessionKey" => $this->api->client->sessionKey,
        );

        $res = $this->api->sendRequest("getPurchaseDocuments", $param,0,0,0);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            Log::info("Purchase Document exist ID".$res['records'][0]['id']);
            return $res['records'][0]['id'];
        }

        return '';

    }


     

}