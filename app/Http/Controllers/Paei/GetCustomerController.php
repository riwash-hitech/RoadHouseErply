<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\Services\GetCustomerService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class GetCustomerController extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetCustomerService $service, EAPIService $api)
    {
        $this->service = $service;
        $this->api = $api;
    }

    public function getCustomer(Request $req)
    { 
        $isDebug = $req->debug ?? 0;
        $isAdded = 0;
        if (@$req->type == "added") {
            $isAdded = 1;
        }
        $param = array(
            "orderBy" => $isAdded == 1 ? "customerID" : "lastChanged",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            "getAddresses" => 1,
            "getContactPersons" => 1,
            "responseMode" => "detail",
            // "changedSince" => $this->service->getLastUpdateDate(), 
            "sessionKey" => $this->api->client->sessionKey
        );
        if ($isAdded == 1) {
            $param["createdUnixTimeFrom"] = $this->service->getLastUpdateDate($isAdded);
        }
        if ($isAdded == 0) {
            $param["changedSince"] = $this->service->getLastUpdateDate($isAdded);
        }

        $res = $this->api->sendRequest("getCustomers", $param);

        if ($isDebug == 1) {
            dd($param, $res);
        }

        if ($res['status']['errorCode'] == 0 && !empty($res['records'])) {
            return $this->service->saveUpdate($res['records'], $isAdded);
        }
    }
}
