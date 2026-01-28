<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Classes\UserLogger;
use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Customer;
use App\Models\PAEI\MatrixDimension;
use App\Models\PAEI\MatrixDimensionVariation;
use App\Traits\ResponseTrait;

class MatrixDimensionApiService{

    use ResponseTrait;
    protected $dimension;
    protected $variation;
    protected $letsLog;
    protected $api;

    public function __construct(MatrixDimension $md, MatrixDimensionVariation $dv, UserLogger $letsLog, EAPIService $api){
        $this->dimension = $md;
        $this->variation = $dv;
        $this->letsLog = $letsLog;
        $this->api = $api;
    }

    

    public function getMatrixDimension($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        } 
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        $requestData = $req->except(Except::$except);

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $dimensions = $this->dimension->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        return response()->json(["status"=>200, "records" => $dimensions]);
    }

    public function getVariationByDimID($req){
        $pagination = $req->recordsOnPage == ''? 20 : $req->recordsOnPage;
        $customers = $this->variation->where('clientCode', $this->api->client->clientCode)->where('parentID', $req->parentID)->paginate($pagination);
        return response()->json(["status"=>200, "records" => $customers]);
    }

    public function getVariations($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'name';
        } 
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = true;
        }
        $requestData = $req->except(Except::$except);

        $pagination = $req->recordsOnPage == '' ? 20 : $req->recordsOnPage;
        // $customers = $this->customer->filter($req)->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        $dimensions = $this->variation->where(function ($q) use ($requestData, $req) {
            $q->where('clientCode', $this->api->client->clientCode);
            foreach ($requestData as $keys => $value) {
                if ($value != null) { 
                    if($req->strictFilter == true){
                        $q->Where($keys, $value);
                    }else{
                        $q->Where($keys, 'LIKE', '%'.$value.'%');
                    }
                }
            }
        })->orderBy($req->sort_by, $req->direction)->paginate($pagination);
        return response()->json(["status"=>200, "records" => $dimensions]);
    }

    public function saveUpdateMatrixDimension($req){
        
        if($req->id){
            $old = $this->dimension->where('id', $req->id)->first();
        }

        $change = $this->dimension->updateOrcreate(
            [
                "id" => $req->id
            ],
            [
                "name" => $req->name,
                "active" => $req->active,
                "added" => date('Y-m-d H:i:s'),
                "lastModified" => date('Y-m-d H:i:s')
            ]
            );
        
        return $this->erplyMatrixDimension($req, $change);
        
        
    }

    protected function erplyMatrixDimension($req,$matrix){
        $param = array(  
            "name" => $req->name, 
        );
        if($req->id){
            $param['dimensionID'] = $matrix->dimensionID;
        } 
        $res = $this->api->sendRequest("saveMatrixDimension", $param, 0, 1);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            //success
            $old = $this->dimension->where('id', $matrix->id)->first();
            $new = $this->dimension->where('id', $matrix->id)->update(['dimensionID' => $res['records'][0]['dimensionID']]);

            //Logger
            $this->letsLog->setLog($req->id ? json_encode($old, true) : '', json_encode($new, true), $req->id ? "Dimension Updated" : "Dimension Created");

            return $this->successWithMessage($req->id ? "Matrix Dimension Updated" : "Matrix Dimension Created");
        }

        return $this->failWithMessageAndData("Failed while saving Matrix Dimension!", $res);
    }

    public function saveUpdateMatrixDimensionValue($req){
        if($req->id){
            $old = $this->variation->where('id', $req->id)->first();
        }
        $order = $req->order ? $req->order : count($this->variation->where('dimensionID', $req->parentID)->get()) + 1 ;

        $dimValue = $this->variation->updateOrcreate(
            [
                "id" => $req->id
            ],
            [
                "name" => $req->name,
                "dimensionID" => $req->dimensionID,
                "code" => $req->code,
                "order" => $order,
                "active" => $req->active,
                "added" => date('Y-m-d H:i:s'),
                "lastModified" => date('Y-m-d H:i:s')
            ]
            );

        return $this->erplyDimensionValue($req, $dimValue, $order);
        
         return response()->json(["status" => 200, "success" => true, "message" => $req->id ? "Matrix Dimension Value Updated" : "Matrix Dimension Value Created" ]);
    }

    protected function erplyDimensionValue($req, $dimValue,$order){
        $param = array(  
            "name" => $req->name, 
            "code" => $req->code,
            "dimensionID" => $req->dimensionID,
            "active" => $req->active,
            "order" => $order
        );
        if($req->id){
            $param['itemID'] = $dimValue->variationID;
        } 
        $res = $this->api->sendRequest("addItemToMatrixDimension", $param, 0, 1);
        info($res);
        if($res['status']['errorCode'] == 0 && !empty($res['records'])){
            //success
            $old = $this->variation->where('id', $dimValue->id)->first();
            $new = $this->variation->where('id', $dimValue->id)->update(['variationID' => $res['records'][0]['itemID']]);

             //lets Log
            $this->letsLog->setLog($old ? json_encode($old, true) : '', json_encode($new, true), $req->id ? "Dimension Value Updated" : "Dimension Value Created");

            return $this->successWithMessage($req->id ? "Dimension Value Updated" : "Dimension Value Created");
        }

        return $this->failWithMessageAndData("Failed while adding Matrix Dimension Value!", $res);
    }

     



}
