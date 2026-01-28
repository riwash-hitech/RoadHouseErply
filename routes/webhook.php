<?php
 


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Paei\Webhooks\WHCustomerController;
use App\Http\Controllers\Paei\Webhooks\WHInventoryTransferController;
use App\Http\Controllers\Paei\Webhooks\WHPaymentController;
use App\Http\Controllers\Paei\Webhooks\WHProductController;
use App\Http\Controllers\Paei\Webhooks\WHSalesDocumentController;
use Illuminate\Support\Facades\Route; 



//for product get
Route::group(["prefix" => "v1/"], function(){

    //Product
    Route::post("/product/createUpdate", [WHProductController::class, 'createUpdate']);
    Route::post("/product/delete", [WHProductController::class, 'delete']);

    //sales order
    Route::post("/salesDocument/delete", [WHSalesDocumentController::class, 'delete']);
    Route::post("/salesDocument/update", [WHSalesDocumentController::class, 'update']);
    
    //payment
    Route::post("/payment/createUpdate", [WHPaymentController::class, 'createUpdate']);

    //inventory
    Route::post("/inventoryTransfer/createUpdate", [WHInventoryTransferController::class, 'createUpdate']);

    //Customer
    Route::post("/customer/createUpdate", [WHCustomerController::class, 'createUpdate']);
});
 















