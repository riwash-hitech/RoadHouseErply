<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'current_giftcard';
    protected $primaryKey = 'customerID';
    public $timestamps = false;
}
