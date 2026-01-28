<?php
namespace App\Http\Controllers\Paei\API\APIServices;

use App\Classes\Except;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\Currency;
use App\Models\PswClientLive\Local\LiveProductMatrix;

class SchoolApiService{

    protected $school;

    public function __construct(LiveProductMatrix $school){
        $this->school = $school;
    }

   

    public function getSchool($req){

        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'SchoolName';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 20;
        $requestData = $req->except(Except::$except);

         
        // $groups = $this->group->paginate($pagination);
        $cashins = $this->school->select("SchoolName")->distinct('SchoolName')->where(function ($q) use ($requestData, $req) {
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
        })->orderBy($req->sort_by, $req->direction)->get();//->paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $cashins]);
    }

    public function getAll($req){
        if(isset($req->direction) == 0){
            $req->direction = 'asc';
        }
        if(isset($req->sort_by) == 0){
            $req->sort_by = 'SchoolName';
        }
        if(isset($req->strictFilter) == 0){
            $req->strictFilter = false;
        }
        
        $pagination = $req->recordsOnPage ? $req->recordsOnPage : 200;
        $requestData = $req->except(Except::$except);

         
        // $groups = $this->group->paginate($pagination);
        $cashins = $this->school->where(function ($q) use ($requestData, $req) {
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
        })->orderBy($req->sort_by, $req->direction)->get();//paginate($pagination);
        
        return response()->json(["status"=>200, "records" => $cashins]);
    }




}
