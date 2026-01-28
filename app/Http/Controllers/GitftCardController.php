<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\GiftCardService;
use App\Http\Controllers\Services\SupplierService;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    //

    protected $service;

    public function __construct(GiftCardService $cs)
    {
        $this->service = $cs;        
    }

    public function create(Request $req){
        return $this->service->saveGiftCard($req);
    }
}
