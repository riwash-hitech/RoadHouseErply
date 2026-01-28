<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\GiftCardService;
use App\Http\Controllers\Services\PurchaseDocumentService;
use App\Http\Controllers\Services\SupplierService;
use Illuminate\Http\Request;

class PurchaseDocumentController extends Controller
{
    //

    protected $service;

    public function __construct(PurchaseDocumentService $cs)
    {
        $this->service = $cs;        
    }

    public function create(Request $req){
        return $this->service->savePurchaseDocument($req);
    }
}
