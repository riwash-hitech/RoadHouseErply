<?php

use App\Http\Controllers\DBConnectionController;
use App\Http\Controllers\LivePushErply\ProductCategoryController;
use App\Http\Controllers\LivePushErply\ProductController;
use App\Http\Controllers\LivePushErply\ProductDimensionController;
use App\Http\Controllers\LivePushErply\ProductGroupController;
use App\Http\Controllers\LivePushErply\StoreLocationController;
use App\Http\Controllers\Paei\GetAssortmentController;
use App\Http\Controllers\Paei\GetCashInsController;
use App\Http\Controllers\Paei\GetCouponController;
use App\Http\Controllers\Paei\GetCurrencyController;
use App\Http\Controllers\Paei\GetCustomerController;
use App\Http\Controllers\Paei\GetCustomerGroupController;
use App\Http\Controllers\Paei\GetDimensionController;
use App\Http\Controllers\Paei\GetEmployeeController;
use App\Http\Controllers\Paei\GetGiftCardController;
use App\Http\Controllers\Paei\GetInventoryRegistrationController;
use App\Http\Controllers\Paei\GetInventoryWriteOffController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Paei\GetMatrixProductController;
use App\Http\Controllers\Paei\GetPriorityGroupController;
use App\Http\Controllers\Paei\GetOpenningClosingController;
use App\Http\Controllers\Paei\GetPaymentController;
use App\Http\Controllers\Paei\GetPaymentTypeController;
use App\Http\Controllers\Paei\GetPricelistController;
use App\Http\Controllers\Paei\GetProductCategoryController;
use App\Http\Controllers\Paei\GetProductGroupController;
use App\Http\Controllers\Paei\GetProductPictureController;
use App\Http\Controllers\Paei\GetPurchaseDocumentController;
use App\Http\Controllers\Paei\GetReasonCodeController;
use App\Http\Controllers\Paei\GetSalesDocumentController;
use App\Http\Controllers\Paei\GetSupplierController;
use App\Http\Controllers\Paei\GetUserOperationLogController;
use App\Http\Controllers\Paei\GetWarehouseController;
use App\Http\Controllers\PswClientLive\PswLiveProductController;
use App\Http\Controllers\ProductBulkDeleteController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\AxToSynccareController;
use App\Http\Controllers\LivePushErply\ErplyCustomerController;
use App\Http\Controllers\LivePushErply\ErplySalesOrderController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\PswClientLive\PswLiveStoreLocationController;
use App\Models\InventoryRegistration;
use App\Models\PAEI\Warehouse as PAEIWarehouse;
use App\Models\StockColorSize;
use App\Models\StockDetail;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Paei\GetProductPictureV2Controller;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
ini_set('max_execution_time', 3000);
ini_set('memory_limit', -1);
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

//for MsSQL to Synccare Roadhouse only

Route::get('/sync-ax-to-synccare-product-des', [AxToSynccareController::class, 'getProductAX']);
Route::get('/sync-des-to-erply', [AxToSynccareController::class, 'updateDesErply']);
Route::get('/sync-des-to-erply-v2', [AxToSynccareController::class, 'updateAgainDes']);
Route::get('/count-variations', [AxToSynccareController::class, 'setFlagToMaxVariation']);
//END 



Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get("/get-export", function(){
    return view('index');
});

#Clear Logs
Route::get('/clear-logs', [LogsController::class, 'clearLogs']);

//FOR ERPLY TO LOCAL DB
Route::get('/get-matrix-dimension', [GetDimensionController::class, 'getMatrixDimension']);
Route::get('/get-matrix-dimension-value', [GetDimensionController::class, 'getMatrixDimensionValue']);
Route::get('/get-product', [GetMatrixProductController::class, 'getProduct']);
Route::get('/get-product-priority-group', [GetPriorityGroupController::class, 'getPriorityGroup']);
Route::get('/get-product-update-all', action: [GetMatrixProductController::class, 'getUpdateAllProduct']);
Route::get('/get-brand', [GetProductGroupController::class, 'getBrand']);
Route::get('/get-product-pim', [GetMatrixProductController::class, 'getProductPIM']);
Route::get("/get-product-group", [GetProductGroupController::class, 'getProductGroup']);
Route::get("/get-product-category", [GetProductCategoryController::class, 'getProductCategory']);
Route::get("/get-customer", [GetCustomerController::class, 'getCustomer']);
Route::get("/get-customer-groups", [GetCustomerGroupController::class, 'getCustomerGroups']);
Route::get("/get-warehouse", [GetWarehouseController::class, 'getWarehouse']);
Route::get("/get-product-picture", [GetProductPictureController::class, 'getProductPictures']);
Route::get("/get-product-picture-v2", [GetProductPictureV2Controller::class, 'getProductPictures']);
Route::get("/get-product-picture-v3", [GetProductPictureV2Controller::class, 'getProductPicturesNew']);
Route::get("/get-inventory-registration", [GetInventoryRegistrationController::class, 'getInventoryRegistration']);
Route::get("/get-inventory-registration-2", [GetInventoryRegistrationController::class, 'getSohOldApi']);
Route::get("/get-inventory-write-offs", [GetInventoryWriteOffController::class, 'getInventoryWriteOffs']);

Route::get("/get-reason-codes", [GetReasonCodeController::class, 'getReasonCodes']);

Route::get("/get-currencies", [GetCurrencyController::class, 'getCurrencies']);

//Payment and Payment Types
Route::get("/get-payment-types", [GetPaymentTypeController::class, 'getTypes']);
Route::get("/get-payment", [GetPaymentController::class, 'getPayments']);

//get openning closing day
Route::get("/get-openning-closing-day", [GetOpenningClosingController::class, 'getOpenningClosing']);

//Get Price List
Route::get("/get-pricelist", [GetPricelistController::class, 'getPricelist']);

//get coupons
Route::get("/get-coupons", [GetCouponController::class, 'getCoupons']);

//get coupons
Route::get("/get-employees", [GetEmployeeController::class, 'getEmployees']);

//get gift cards
Route::get("/get-giftcards", [GetGiftCardController::class, 'getGiftCards']);

//Get Sales Documents
Route::get("/get-sales-documents", [GetSalesDocumentController::class, 'getSalesDocuments']);

Route::get("/get-purchase-documents", [GetPurchaseDocumentController::class, 'getPurchaseDocument']);
Route::get("/get-cashins", [GetCashInsController::class, 'getCashins']);

Route::get("/get-suppliers", [GetSupplierController::class, 'getSuppliers']);

//for erply to local update product details
Route::get('/lets-update-matrix', [GetMatrixProductController::class, 'letsUpdateMatrix']);


//User Operations Log
Route::get('/get-user-operation-customer', [GetUserOperationLogController::class, 'getOperationLogCustomer']);
Route::get('/get-user-operation-customer-group', [GetUserOperationLogController::class, 'getOperationLogCustomerGroup']);

//Get Assortment
Route::get('/get-assortments', [GetAssortmentController::class, 'getAssortment']);


//for user operation
Route::get('/get-user-operation-product', [GetMatrixProductController::class, 'getOperationLogProduct']);

//PSW PUSH DATA TO ERPLY STAGING, TEST, LIVE

//PRODUCT APIS
// Route::get('/push-dimension-color', [ProductDimensionController::class, 'syncDimensionColor']);
// Route::get('/push-dimension-size', [ProductDimensionController::class, 'syncDimensionSize']);
// Route::get('/push-product-group', [ProductGroupController::class, 'syncProductGroup']);
// Route::get('/push-product-category', [ProductCategoryController::class, 'syncProductCategory']);
// Route::get('/push-product-matrix', [ProductController::class, 'syncMatrixProduct']);
// Route::get('/push-product-variation', [ProductController::class, 'syncVariationProduct']);
Route::get('/push-shopify-customer', [ErplyCustomerController::class, 'syncCustomerToErply']);
Route::get('/push-customer-address', [ErplyCustomerController::class, 'saveCustomerAddress']);
Route::get('/push-shopify-sales-order', [ErplySalesOrderController::class, 'pushSalesOrders']);
Route::get('/push-shopify-order-delivery-address', [ErplySalesOrderController::class, 'saveDeliveryAddress']);
Route::get('/push-shopify-sales-refund-return', [ErplySalesOrderController::class, 'pushRefundReturn']);
Route::get('/v2/push-shopify-sales-refund-return', [ErplySalesOrderController::class, 'pushRefundReturnV2']);



//Warehouse 
// Route::get('/push-warehouse', [StoreLocationController::class, 'syncWarehouse']);

//check product exist in erply
Route::get('/check-product-exist-erply', [GetMatrixProductController::class, 'checkProductExistInErply']);


 


//updating variation sku
Route::get("/update-variation-sku", function(){
    // $limit = 5000;
    // $variation = StockColorSize::where('product_sku_2','')->limit($limit)->get();
    // // dd($variation);
    // foreach($variation as $v){
    //     // echo $v->product_sku."<br>";
    //     $split = explode("PSW_",$v->product_sku);
    //     $v->product_sku_2 = $split[1];
    //     $v->save();
    //     // die;
    // }
    // echo "Success";
});

Route::get("/update-variation-pos-sku", function(){

    $matrix = StockDetail::where('erplyPending', 0)->where('')->get();
    foreach($matrix as $m){
        $v = StockColorSize::where('web_sku', $m->web_sku)->where('newSystemInternetActive',1)->first();
        if(isset($v->pos_sku) == 0){
            echo $m->web_sku."<br>";
        }
    }
    echo "Success";	
    die;

    $limit = 1000;
    $variation = StockColorSize::join('current_customer_product_relation', function($rel){
                $rel->on("current_customer_product_relation.product_sku", '=' ,'newsystem_stock_colour_size.product_sku_2');
                $rel->on("current_customer_product_relation.web_sku",'=','newsystem_stock_colour_size.web_sku');
            })
            // ->where('newsystem_stock_colour_size.newSystemInternetActive', 1)
            ->where('newsystem_stock_colour_size.pos_sku', '')
            ->select(["current_customer_product_relation.softCode","current_customer_product_relation.product_sku", "newsystem_stock_colour_size.newSystemColourSizeID", "newsystem_stock_colour_size.web_sku"])
            ->limit(1000)
            ->get();
    dd($variation);
    foreach($variation as $v){
        // echo $v->product_sku."<br>";
        // $split = explode("PSW_",$v->product_sku);
        $v->pos_sku = $v['product_sku']."_".$v['softCode'];
        $v->save();
        // die;
    }
    echo "Success pos sku updated";
});


Route::get("/json-query", function(){
$results = DB::table('newsystem_inventory_registrations') ->whereJsonContains('rows', [['productID' => 8158]]) ->get();
dd($results);
});


Route::get("/delete-bulk-products", [ProductBulkDeleteController::class, 'deleteProductUsingTable']);
Route::get("/get-bulk-archive-products", [ProductBulkDeleteController::class, 'getArchive']);


//updating warehosue erply id
// Route::get("/update-warehouse-erplyid", function(){
//     $limit = 1000;
//     $variation = PAEIWarehouse::where('code','<>', '')->get();

//     // dd($variation);
//     foreach($variation as $v){
//         Warehouse::where('locationid', $v->code)->update(['erplyWarehouseID'=> $v->warehouseID, 'erplyPending' => 0]);
       
//     }
//     echo "Warehouse location updated successfully";
// });
//END

//updating variation inventory registration ID
// Route::get("/update-variation-inventory-id", function(){
//     $limit = 1000;
//     $variation = InventoryRegistration::where('synToVariation', 1)->limit($limit)->get();
//     foreach($variation as $v){
//         // echo $v->product_sku."<br>";
//         StockColorSize::where('product_sku', $v->productSKU)
//         $v->product_sku_2 = $split[1];
//         $v->save();
//         // die;
//     }
//     echo "Success";
// });
//END

//PSW CLIENT LIVE DB TO MIDDLEWARE SERVER

//live psw DB url
//PRODUCT
Route::get("/generate-product-dev-file", [PswLiveProductController::class, 'makeProductFile']);
Route::get("/read-product-dev-file", [PswLiveProductController::class, 'handleProductFile']);
Route::get("/read-product-size-sort", [PswLiveProductController::class, 'pswToMiddlewareSizeSort']);
//sync temp product to current product table
Route::get("/sync-temp-product-to-matrix-product", [PswLiveProductController::class, 'syncTempToCurrentsystemMatrix']);
Route::get("/sync-temp-product-to-currentsystem-product", [PswLiveProductController::class, 'syncTempToCurrentsystem']);
//productDescription
Route::get("/make-product-des-file", [PswLiveProductController::class, 'makeDescriptionFile']);
Route::get("/read-product-des-file", [PswLiveProductController::class, 'readDescriptionFile']);
Route::get("/sync-product-des-newsystem", [PswLiveProductController::class, 'syncDescriptionNewsystem']);

//Store Location
Route::get("/generate-store-location", [PswLiveStoreLocationController::class, 'makeStoreLocationFile']);
Route::get("/read-store-location", [PswLiveStoreLocationController::class, 'handleStoreLocationFile']);
Route::get("/temp-location-to-live", [PswLiveStoreLocationController::class, 'syncToLive']);




Route::get('/db-connection', [DBConnectionController::class, 'index']);
Route::post('/db-connection-check', [DBConnectionController::class, 'deConnectioncheck'])->name('dbConnectionCheck');

Route::get('/get-product-bulk-swagger-post', [ProductStockController::class, 'getProductSwaggerPost']);

Route::get('/fix-variation-dimension-issue', [GetMatrixProductController::class, 'fixVariationDimensionID']);


//export to excel variation product
// Route::get('/get-product-csv', [GetMatrixProductController::class, 'exportCSV']);


// require __DIR__.'/auth.php';


