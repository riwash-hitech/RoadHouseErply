<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\CampaignService;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    //
    protected $service;

    public function __construct(CampaignService $service)
    {
        $this->service = $service;
    }

    public function getCampaigns(){
        return $this->service->getCampaign();
    }

    public function saveCampaign(Request $req){
        return $this->service->saveCampaign($req);
    }
}
