<?php

namespace App\Http\Controllers\Paei\API;

use App\Http\Controllers\Controller;
use App\Models\PAEI\Customer;
use App\Models\PAEI\MatrixProduct;
use App\Models\PAEI\ProductCategory;
use App\Models\PAEI\ProductGroup;
use App\Models\PAEI\VariationProduct;
use App\Models\PAEI\Warehouse;
use DateTime;
use Illuminate\Http\Request;

class LastSyncApiController extends Controller
{
    //
    protected $matrix;
    protected $variation;
    protected $category;
    protected $group;
    protected $warehouse;
    protected $customer;

    public function __construct(MatrixProduct $mp, VariationProduct $vp, ProductCategory $pc, ProductGroup $pg, Warehouse $wl, Customer $customer){
        $this->matrix = $mp;
        $this->variation = $vp;
        $this->category = $pc;
        $this->group = $pg;
        $this->warehouse = $wl;
        $this->customer = $customer;
    }

    public function getLastSyncAll(){
        $lastSync = array();
        $customer = $this->customer->orderBy('lastModified','desc')->first()->lastModified;
        $category = $this->category->orderBy('updated_at','desc')->first()->updated_at;
        $group = $this->group->orderBy('changed','desc')->first()->changed;
        $warehouse = $this->warehouse->orderBy('changed', 'desc')->first()->changed;
        $matrix = new DateTime($this->matrix->orderBy('lastModified', 'desc')->first()->lastModified);
        $variation = new DateTime($this->variation->orderBy('lastModified', 'desc')->first()->lastModified);
        if($matrix > $variation){
            array_push($lastSync, array("product" => $this->matrix->orderBy('lastModified', 'desc')->first()->lastModified));
        }else{
            array_push($lastSync, array("product" => $this->variation->orderBy('lastModified', 'desc')->first()->lastModified));
        }
        array_push($lastSync, array("customer" => $customer));
        array_push($lastSync, array("category" => $category));
        array_push($lastSync, array("group" => $group));
        array_push($lastSync, array("warehouse" => $warehouse));

        $lastSync = json_encode($lastSync, true);
        return response()->json(["status"=> 200, "records" => $lastSync]);

    }
}
