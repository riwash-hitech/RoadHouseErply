<?php
namespace App\Http\Controllers\Paei\Services;

use App\Http\Controllers\Services\EAPIService;
use App\Models\PAEI\Cashin;
use App\Models\PAEI\Employee;

class GetEmployeeService{

    protected $employee;
    protected $api;

    public function __construct(Employee $c, EAPIService $api){
        $this->employee = $c;
        $this->api = $api;
    }

    public function saveUpdate($employees){

        foreach($employees as $c){
            $this->saveUpdateEmployee($c);
        }

        return response()->json(['status'=>200, 'message'=>"Employees fetched Successfully."]);
    }

    protected function saveUpdateEmployee($product){

        $this->employee->updateOrCreate(
                [
                    "clientCode" => $this->api->client->clientCode,
                    "employeeID"  =>  $product['employeeID']
                ],
                [
                    "clientCode" => $this->api->client->clientCode,
                    'employeeID' => @$product['employeeID'],
                    'fullName' => @$product['fullName'],
                    'employeeName' => @$product['employeeName'],
                    'firstName' => @$product['firstName'],
                    'lastName' => @$product['lastName'],
                    'phone' => @$product['phone'],
                    'mobile' => @$product['mobile'],
                    'email' => @$product['email'],
                    'fax' => @$product['fax'],
                    'code' => @$product['code'],
                    'gender' => @$product['gender'],
                    'userID' => @$product['userID'],
                    'username' => @$product['username'],
                    'userGroupID' => @$product['userGroupID'],
                    'description' => @$product['description'],
                    'warehouses' =>!empty($product['warehouses']) ? json_encode($product['warehouses'], true) : '',
                    'pointsOfSale' => @$product['pointsOfSale'],
                    'productIDs' => !empty($product['productIDs']) ? json_encode($product['productIDs'], true) : '', 
                    'skype' => @$product['skype'],
                    'birthday' => @$product['birthday'],
                    'jobTitleID' => @$product['jobTitleID'],
                    'jobTitleName' => @$product['jobTitleName'],
                    'notes' => @$product['notes'],
                    'drawerID' => @$product['drawerID'], 
                    'lastModifiedByUserName' => @$product['lastModifiedByUserName'], 
                    "added" =>  date('Y-m-d H:i:s', @$product['added']),
                    "lastModified" => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',@$product['lastModified']) : '0000-00-00 00:00',
                    "attributes" => !empty($product['attributes']) ? json_encode($product['attributes'], true) : '',
                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->employee->orderBy('lastModified', 'desc')->first();
        if($latest){
            return strtotime($latest->lastModified);
        }
        return 0;// strtotime($latest);
    }
}
