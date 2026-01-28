<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\Supplier;

class SupplierApiService{

    protected $supplier;
    protected $api;

    public function __construct(Supplier $c, EAPIService $api){
        $this->supplier = $c;
        $this->api = $api;
    }

    public function getBySupplierID($id){
        $customer = $this->supplier->where('clientCode', $this->api->client->clientCode)->where("supplierID", $id)->get();
        return response()->json(["status"=>200, "records" => $customer]);

    }

    public function getSuppliers($req){
        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        $suppliers = $this->supplier->where('clientCode', $this->api->client->clientCode)->paginate($pagination);
        return response()->json(["status"=>200, "records" => $suppliers]);
    }
 
 



}
