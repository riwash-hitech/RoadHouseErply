<?php

namespace App\Http\Controllers\Paei;

use App\Http\Controllers\Controller;  
use App\Http\Controllers\Paei\Services\GetProductPictureV2Service;
use App\Http\Controllers\Services\EAPIService;
use Illuminate\Http\Request;
use App\Models\PAEI\TempProductUpdate;

class GetProductPictureV2Controller extends Controller
{
    //
    protected $service;
    protected $api;

    public function __construct(GetProductPictureV2Service $service, EAPIService $api){
        $this->service = $service;
        $this->api = $api;
    }

    public function getProductPictures(Request $req){
        die('Cron Disabled i.e use getProductPicturesNew function');
        $isNew = 1;
        // if($req->newlastmodified){
        //     $isNew = 1;
        // }
        $param = array(
            "take" => "100",
            // "sort" => json_encode([
            //     "selector" => "added",
            //     "desc" => false
            // ]),
            "page" => $this->service->getCdnPage(),
            "match" => ">=",
            "changedSince" => $this->service->getLastUpdateDate($isNew),
            // "isDeleted" => false,
            "context" => 'erply-product',
            // "getAddresses" => 1, 
            "orderByDirection" => 'ASC'
        );
        
        $res = $this->api->sendRequestByCDNApiGetWithJwt("https://cdn.erply.com/images?", $param);
        // dd($res, $param);
        if(count($res) > 0){
             $this->service->saveUpdate($res, $isNew, $this->service->getCdnPage());
        }
        // dd($res);
        return response()->json(["status" => "success", "message" => "All Product Picture CDN Syncced to Synccare."]);
    }

    public function getProductPicturesNew (Request $request) 
    {
        $pageNo = $this->service->getCdnPage();

        $tempUpdate = TempProductUpdate::where("id", 1)->first();
        $unixtime = $this->service->getLastUpdateDate(0);
        if ($tempUpdate->cdn_next_request == 1) {
            $last_updated_time = $unixtime + 1;
        } else {
            $last_updated_time = $unixtime;
        }

        $param = [
            'take' => '100',
            'page' => $pageNo,
            'match' => '>=',
            'changedSince' => $last_updated_time, // $this->service->getLastUpdateDate(0),
            'context' => 'erply-product',
            'orderByDirection' => 'ASC'
        ];

        // JUST PRINT
        echo '<pre>';
        print_r($param);

        $res = $this->api->sendRequestByCDNApiGetWithJwt('https://cdn.erply.com/images?', $param);
        if (request()->query('debug') == 1) dd($res);
            
        // dd($res);
        if ($res && count($res['images']) > 0)  {
            return $this->service->saveUpdateNew($res['images'], $pageNo);
        } else {
            $tempUpdate = TempProductUpdate::where("id", 1)->first();
            $tempUpdate->cdn_image_page = 1;
            $tempUpdate->cdn_next_request = 1;
            $tempUpdate->save();
        }
        return response()->json(['status' => 'success', 'message' => 'All Product Picture CDN Syncced to Synccare.']);
    }
}

// [{"pictureID":"440","name":"","thumbURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","smallURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","largeURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","fullURL":"https:\/\/pswdata.retailcare.com.au\/magic\/uploads\/images\/1100105_6_10024_11_F_conImg.jpg","external":1,"hostingProvider":"","hash":null,"tenant":null}]