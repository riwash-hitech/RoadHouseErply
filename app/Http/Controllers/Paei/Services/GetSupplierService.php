<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Supplier;

class GetSupplierService{

    protected $supplier;
    protected $api;

    public function __construct(Supplier $c, EAPIService $api){
        $this->supplier = $c;
        $this->api = $api;
    }

    public function saveUpdate($suppliers){

        foreach($suppliers as $c){
            $this->saveUpdateSupplier($c);
        }

        return response()->json(['status'=>200, 'message'=>"Supplier fetched Successfully."]);
    }

    protected function saveUpdateSupplier($product){

        $this->supplier->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "supplierID"  =>  $product['id']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    "supplierID" => $product['id'],
                    "supplierType" => $product['supplierType'],
                    "fullName" => $product['fullName'],
                    "companyName" => @$product['companyName'],
                    "firstName"  => @$product['firstName'],
                    "lastName"  => $product['lastName'],
                    "groupID"  => @$product['groupID'],
                    "groupName"  => @$product['groupName'],
                    "phone"  => @$product['phone'],
                    "mobile"  =>  @$product['mobile'],
                    "email"  =>  @$product['email'],
                    "fax"  =>  @$product['fax'],
                    "code"  =>  @$product['code'],
                    "integrationCode"  => @$product['integrationCode'],
                    "vatrateID"  => $product['vatrateID'],
                    "currencyCode"  => @$product['currencyCode'],
                    "deliveryTermsID"  => @$product['deliveryTermsID'],
                    "countryID"  => @$product['countryID'],
                    "countryName"  => $product['countryName'],
                    "countryCode"  => @$product['countryCode'],
                    "address"  => @$product['address'],
                    "GLN"  => @$product['GLN'],
                    "attributes"  =>!empty(@$product['attributes']) ? json_encode($product['attributes'],true) : '', 
                    "vatNumber"  => @$product['vatNumber'],
                    "skype"  => @$product['skype'],
                    "website"  => @$product['website'],
                    "bankName"  => @$product['bankName'],
                    "bankAccountNumber"  => @$product['bankAccountNumber'],
                    "bankIBAN"  => @$product['bankIBAN'],
                    "bankSWIFT"  => @$product['bankSWIFT'],
                    "birthday"  => @$product['birthday'],
                    "companyID"  => @$product['companyID'],
                    "parentCompanyName"  => @$product['parentCompanyName'],
                    "supplierManagerID"  => @$product['supplierManagerID'],
                    "supplierManagerName"  => @$product['supplierManagerName'],
                    "paymentDays"  => @$product['paymentDays'],
                    "notes"  => @$product['notes'],
                    "lastModified"  => date('Y-m-d H:i:s',$product['lastModified']),//today date time
                    "added"  =>date('Y-m-d H:i:s',$product['added']),
                    "addedby"  => @$product['addedby'],
                    "changedby"  => @$product['changedby'],// today date
                    
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->supplier->where('clientCode', $this->api->client->clientCode)->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
