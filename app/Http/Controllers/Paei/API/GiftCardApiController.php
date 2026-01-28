<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paei\API\APIServices\GiftCardApiService; 
use Illuminate\Http\Request;

class GiftCardApiController extends Controller
{
    //
    protected $service;


    public function __construct(GiftCardApiService $service){
        $this->service = $service;
        // $this->variation = $vp;
    }

    public function getGiftCards(Request $req){
        
        return $this->service->getGiftCards($req);

    }
 
}
