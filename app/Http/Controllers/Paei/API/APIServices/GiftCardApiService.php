<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\GiftCard; 
use App\Traits\ResponseTrait;

class GiftCardApiService{

    protected $giftcard;
    use ResponseTrait;
    protected $api;

    public function __construct(GiftCard $c, EAPIService $api){
        $this->giftcard = $c;
        $this->api = $api;
    }

     

    public function getGiftCards($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'giftCardID';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except); 

        // $groups = $this->group->paginate($pagination);
        $giftcards = $this->giftcard->where(function ($q) use ($requestData, $req) {
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
        
        return $this->successWithData($giftcards);
        // return response()->json(["status"=>200, "success" => true, "records" => $giftcards]);
    }
 
 



}
