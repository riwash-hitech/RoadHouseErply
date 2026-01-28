<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\WarehouseService;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseLocationController extends Controller
{
    //

    protected $service;
    protected $warehouse;

    public function __construct(WarehouseService $ws, Warehouse $w)
    {
        $this->service = $ws;
        $this->warehouse = $w;
    }

    public function saveWarehouse(Request $req){
        $limit = $req->limit == '' ? 3 : $req->limit;
        //now getting warehouse data
        $warehouse = $this->warehouse->where('locationid', '<>', '')
                    // ->where('erplyPending', 1)
                    // ->where('id', '515515')
                    ->where('warehouse_status', 1)
                    ->where('erplyPending', 1)
                    ->groupBy('locationid')
                    ->limit($limit)
                    ->get();
        // dd($warehouse);
        // print_r() 
        // foreach($warehouse as $w){
        //     echo $w->locationid."<br>";
        // }
        // // print_r($warehouse);
        // die;
        return $this->service->saveWarehouse($warehouse);

    }
    // public function test(){
    //     $this->service->checkWarehouse('3W001','');
    // }

    public function deleteWarehouse(){
        $warehouse = $this->warehouse->where('locationid', '<>', '')
                    ->where('erplyPending', 0)
                    // ->where('id', '515515')
                    ->groupBy('locationid')
                    ->limit(10)
                    ->get();

    }
}
