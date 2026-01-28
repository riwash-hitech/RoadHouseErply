<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\Coupon;
use App\Models\PAEI\Currency; 

class CouponApiService{

    protected $coupon;
    protected $api;

    public function __construct(Coupon $w, EAPIService $api){
        $this->coupon = $w;
        $this->api = $api;
    }

   

    public function getCoupons($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

         
        // $groups = $this->group->paginate($pagination);
        $coupons = $this->coupon->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                    // 'like', '%' . $value . '%'); 
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $coupons]);
    }


}
