<?php
namespace App\Http\Controllers\Paei\Services;

use App\Models\PAEI\Customer;
use App\Models\PAEI\ProductPicture;

class GetProductPictureService{

    protected $picture;

    public function __construct(ProductPicture $c){
        $this->picture = $c;
    }

    public function saveUpdate($pictures){

        foreach($pictures as $c){
            $this->saveUpdatePicture($c);
        }

        return response()->json(['status'=>200, 'message'=>"Product Picture fetched Successfully."]);
    }

    protected function saveUpdatePicture($product){

        $this->picture->updateOrCreate(
                [
                    "productPictureID"  =>  $product['productPictureID']
                ],
                [
                    "productPictureID" => $product['productPictureID'],
                    "productID" => $product['productID'],
                    "name" => @$product['name'],
                    "thumbURL" => @$product['thumbURL'],
                    "smallURL"  => @$product['smallURL'],
                    "largeURL"  => $product['largeURL'],
                    "fullURL"  => @$product['fullURL'],
                    "external"  => @$product['external'],
                    "lastModified"  => isset($product['lastModified']) == 1 ? date('Y-m-d H:i:s',$product['lastModified']) : '0000-00-00 00:00', 
                    "added"  => isset($product['added']) == 1 ? date('Y-m-d H:i:s',$product['added']) : '0000-00-00 00:00',
                    

                ]
            );
    }


    public function getLastUpdateDate(){
        // echo "im call";
         $latest = $this->picture->orderBy('added', 'desc')->first();
        if($latest){
            return strtotime($latest->added);
        }
        return 0;// strtotime($latest);
    }
}
