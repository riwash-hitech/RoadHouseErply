<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentCustomerProductRelation extends Model
{
    use HasFactory;
    protected $connection = "mysql2";
    protected $table = 'current_customer_product_relation';
    protected $primaryKey = 'relationID';
    public $timestamps = false;
}
