<?php

use App\Http\Controllers\ShopifyPull\ShopifyOrderController;
use Illuminate\Support\Facades\Route;   

//read apis
Route::group(["prefix" => "/shopify/v1/"], function(){ 

    // Route::get('/getMatrixProducts', [ShopifyProductController::class, 'getMatrixProducts']);
    // Route::get('/getVariationProducts', [ShopifyProductController::class, 'getVariationProducts']);
    // Route::get('/getProductSoh', [ShopifyProductController::class, 'getProductSoh']);
    // Route::get('/getProductAndDelete', [ShopifyProductController::class, 'getProductAndDelete']);
    // Route::get('/getCustomers', [ShopifyProductController::class, 'getCustomers']);

    /** push product shopify */
    // Route::get('/pushMatrixProducts/{code?}', [ProductController::class, 'pushMatrixProductsShopify']);
    // Route::get('/updatePictures/{code?}', [ProductImageController::class, 'updatePicturesShopify']);
    
    //Product Stock
    // Route::get('/updateStock/{code?}', [ProductController::class, 'createUpdateProductStock']);

    
    //Brand as Collection
    // Route::get('/createUpdateBrandAsCollection/{code?}', [ProductController::class, 'createUpdateBrandAsCollection']);

    /**get orders from shopify */
    // Route::get('/getOrders/{code?}', [ShopifyOrderController::class, 'getOrders']);
    Route::get('/getRefund/{code?}', [ShopifyOrderController::class, 'getRefund']);
});
