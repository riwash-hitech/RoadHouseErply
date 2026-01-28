<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\InventoryRegistrationController;
use App\Http\Controllers\Paei\API\CashinsApiController;
use App\Http\Controllers\Paei\API\CouponApiController;
use App\Http\Controllers\Paei\API\CurrencyApiController;
use App\Http\Controllers\Paei\API\CustomerAPIController;
use App\Http\Controllers\Paei\API\CustomerGroupApiController;
use App\Http\Controllers\Paei\API\EmployeeApiController;
use App\Http\Controllers\Paei\API\GiftCardApiController;
use App\Http\Controllers\Paei\API\InventoryRegistrationApiController;
use App\Http\Controllers\Paei\API\InventoryTransferApiController;
use App\Http\Controllers\Paei\API\InventoryWriteOffApiController;
use App\Http\Controllers\Paei\API\MatrixDimensionApiController;
use App\Http\Controllers\Paei\API\LastSyncApiController;
use App\Http\Controllers\Paei\API\OpenningClosingDayApiController;
use App\Http\Controllers\Paei\API\PaymentApiController;
use App\Http\Controllers\Paei\API\PaymentTypeApiController;
use App\Http\Controllers\Paei\API\PosCashInOutController;
use App\Http\Controllers\Paei\API\PricelistApiController;
use App\Http\Controllers\Paei\API\ProductAPIController;
use App\Http\Controllers\Paei\API\ProductCategoryApiController;
use App\Http\Controllers\Paei\API\ProductGroupApiController;
use App\Http\Controllers\Paei\API\PurchaseDocumentApiController;
use App\Http\Controllers\Paei\API\ReasonCodesApiController;
use App\Http\Controllers\Paei\API\SalesDocumentApiController;
use App\Http\Controllers\Paei\API\SchoolApiController;
use App\Http\Controllers\Paei\API\WarehouseApiController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ProductAssortmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\Services\EAPIService;
use App\Http\Controllers\Services\WarehouseService;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\Temp\A21Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseLocationController;
use App\Models\Kudos\kudos_selection_ado_105;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Paei\GetMatrixProductController;
use App\Http\Controllers\Paei\GetProductCategoryController;
use App\Http\Controllers\Paei\GetProductGroupController;
use App\Http\Controllers\Paei\GetWarehouseController;


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

require __DIR__ . '/shopify.php';

ini_set('max_execution_time', 3000);
ini_set('memory_limit', -1);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




//for product get
Route::get("/product/create", [ProductController::class, 'create']);
Route::get("/product/get", [ProductController::class, 'getProduct']);

//LOCAL DB TO ERPLY DB

//for product push
Route::get("/product/category", [CategoryController::class, 'updateCategory']);
Route::get("/product/group", [GroupController::class, 'pushGroup']);
Route::get("/product-push", [ProductStockController::class, 'toErply']);
Route::get("/bulk-product-push", [ProductStockController::class, 'bulkProductPush']);
// Route::get("/product-inventory-reg", [ProductStockController::class, 'inventoryReg']);

Route::get("/product-pictures-upload-matrix", [PictureController::class, 'productPicture']);
Route::get("/product-pictures-upload-variation", [PictureController::class, 'productPictureVariation']);

//Matrix Dimension
Route::get("/create-matrix-dimension", [ProductStockController::class, 'createDimension']);
Route::get("/create-matrix-color-dimension", [ProductStockController::class, 'colorDimension']);
Route::get("/create-matrix-size-dimension", [ProductStockController::class, 'sizeDimension']);

//inventory registration
Route::get("/inventory-registration", [InventoryRegistrationController::class, 'saveInventory']);
Route::get("/update-inventory-price", [InventoryRegistrationController::class, 'updateInventoryPrice']);

//change sales price
Route::get("/update-net-price", [InventoryRegistrationController::class, 'updateNetPrice']);
 
//add product assortment
Route::get("/product-add-assortment", [ProductAssortmentController::class, 'productAssortment']);

//session
Route::get("/verifyUser", [SessionController::class, 'verifyUser']);
Route::get("/verifySession", [SessionController::class, 'verifySession']);

//for customer
Route::get("/customer-create", [CustomerController::class, 'create']);

//for supplier
Route::get("/supplier-create", [SupplierController::class, 'create']);

//for warehouse location`
Route::get("/warehouse-location-create", [WarehouseLocationController::class, 'saveWarehouse']);
Route::get("/warehouse-location-delete", [WarehouseLocationController::class, 'deleteWarehouse']);

//Campaign
Route::get("/get-campaigns", [CampaignController::class, 'getCampaigns']);
Route::post("/save-campaigns", [CampaignController::class, 'saveCampaign']);

Route::get("/test",[WarehouseLocationController::class, 'test']);

Route::get("/kudos",function(){
    $kudo = kudos_selection_ado_105::limit(20)->get();
    foreach($kudo as $k){
        echo $k->id."<br>";
    }
});


//WORKING API
//for setup details
Route::get("/getSetupDetails", [SetupController::class, 'getdetails']);

Route::group(['middleware'=>'auth:sanctum'], function(){
    //for setup details
    Route::post("/saveSetup", [SetupController::class, 'saveUpdate']);
    Route::post("/deleteByKey", [SetupController::class, 'deleteByKey']);
    Route::post("/getByKey", [SetupController::class, 'getByKey']);

    //for user
    Route::post("/updateUser/{id}", [UserController::class, 'updateUser']);
    Route::post("/deleteUser", [UserController::class, 'deleteUser']);
    Route::get("/getUsers", [UserController::class, 'getUsers']);
    Route::get("/getUser/{id}", [UserController::class, 'getUser']);
    Route::post("/updatePassword/{id}", [UserController::class, 'updatePassword']);
    Route::post("/updateUserStatus/{id}", [UserController::class, 'updateUserStatus']);
 

    //Product related API

    //for products

    Route::get("/getMatrixDimension", [MatrixDimensionApiController::class, 'getDimensions']);
    Route::get("/getMatrixDimensionVariation", [MatrixDimensionApiController::class, 'getVariations']);
    // Route::post("/saveMatrixDimension", [MatrixDimensionApiController::class, 'saveMatrixDimension']);
    // Route::post("/saveMatrixDimensionValue", [MatrixDimensionApiController::class, 'saveMatrixDimensionValue']);

    //inventory registrations
    Route::get("/getInventoryRegistrations", [InventoryRegistrationApiController::class, 'getInventories']);
    Route::get("/getInventoryWriteOff", [InventoryWriteOffApiController::class, 'getInventories']);
    // Route::post("/saveInventoryWriteOff", [InventoryWriteOffApiController::class, 'saveUpdate']);
    Route::get("/getInventoryTransfer", [InventoryTransferApiController::class, 'getInventories']);

    //Reason Codes
    Route::get("/getReasonCodes", [ReasonCodesApiController::class, 'getReasonCodes']);

    //Currency
    Route::get("/getCurrency", [CurrencyApiController::class, 'getCurrency']);

    //Coupons
    Route::get("/getCoupons", [CouponApiController::class, 'getCoupons']);

    //Gift Cards
    Route::get("/getGiftCards", [GiftCardApiController::class, 'getGiftCards']);

    //Price List
    Route::get("/getPricelists", [PricelistApiController::class, 'getPricelists']);
    // Route::post("/savePricelist", [PricelistApiController::class, 'savePricelist']);

    //Opening Closing Days
    Route::get("/getOpeningClosingDays", [OpenningClosingDayApiController::class, 'getOpeningClosingDays']);
    // Route::post("/saveOpeningDay", [OpenningClosingDayApiController::class, 'saveOpeningDay']);
    // Route::post("/saveClosingDay", [OpenningClosingDayApiController::class, 'saveClosingDay']);



    //Pos Cash In and Out and Get CashIns
    // Route::post("/savePosCashIn", [PosCashInOutController::class, 'saveCashIn']);
    // Route::post("/savePosCashOut", [PosCashInOutController::class, 'saveCashOut']);
    Route::get("/getCashIns", [CashinsApiController::class, 'getCashins']);

    //Employees API
    Route::get("/getEmployees", [EmployeeApiController::class, 'getEmployees']);

    //Payments
    Route::get("/getPayments", [PaymentApiController::class, 'getPayments']);
    Route::get("/getPaymentTypes", [PaymentTypeApiController::class, 'getTypes']);

    //Customers and Groups API
    
    Route::get("/getCustomer", [CustomerAPIController::class, 'getCustomersByID']);
    // Route::post("/saveCustomer", [CustomerAPIController::class, 'saveCustomer']);
    // Route::post("/deleteCustomer", [CustomerAPIController::class, 'deleteCustomer']);
    Route::get("/getCustomerGroups", [CustomerGroupApiController::class, 'getCustomerGroups']);
    // Route::post("/saveCustomerGroup", [CustomerGroupApiController::class, 'saveCustomerGroup']);
    // Route::post("/deleteCustomerGroup", [CustomerGroupApiController::class, 'deleteCustomerGroup']);
    // Route::post("/deleteCustomerGroup", [CustomerGroupApiController::class, 'saveCustomerGroup']);

    //Warehouse API
    Route::get("/getWarehouses", [WarehouseApiController::class, 'getWarehouses']);
    Route::get("/getWarehouse", [WarehouseApiController::class, 'getWarehouseByID']);



    // Route::post("/getWarehouse", [WarehouseApiController::class, 'getWarehouseByID']);

    //last sync
    Route::get("/lastSyncAll", [LastSyncApiController::class, 'getLastSyncAll']);


    //Product, Groups and Categories API
    Route::get("/getProducts", [ProductAPIController::class, 'getProduct']);
    Route::get("/getProductGroups", [ProductGroupApiController::class, 'getGroups']);
    Route::get("/getProductGroup", [ProductGroupApiController::class, 'getGroupsByID']);
    Route::get("/getProductCategories", [ProductCategoryApiController::class, 'getCategories']);
    Route::get("/getProductCategory", [ProductCategoryApiController::class, 'getCategoryByID']);

});

    //Puchase and Sales Documents
    Route::get("/getPurchaseDocuments", [PurchaseDocumentApiController::class, 'getPurchaseDocuments']);
    Route::get("/getSalesDocuments", [SalesDocumentApiController::class, 'getSalesDocuments']);
    Route::get("/getCustomers", [CustomerAPIController::class, 'getCustomers']);

//Temp API For School Only
Route::get("/getSchool", [SchoolApiController::class, 'getSchool']);
Route::get("/getAllSchool", [SchoolApiController::class, 'getAll']);

//Working API
//for user login and registration
Route::post("/registerUser", [UserController::class, 'register']);
Route::post("/userLogin", [UserController::class, 'login']);

//GETTING PRODUCTS FROM ERPLY
// Route::get("/get-products", [GetProductController::class, 'getProduct']);
// Route::get("/get-groups", [GroupController::class, 'getGroups']);


//export to excel variation product
Route::get('/getProductCSV', [ProductAPIController::class, 'exportCSV']);

Route::get("/push-roadhouse-cat", [CategoryController::class, 'pushTempCategory']);

 
//SOH
Route::get("/get-soh", [InventoryRegistrationApiController::class, 'getSOH']);



Route::group(["prefix" => "erply/read/v1"], function(){
    Route::get("/get-product-sales-report", [ProductController::class, 'getProductsReport']);
});










