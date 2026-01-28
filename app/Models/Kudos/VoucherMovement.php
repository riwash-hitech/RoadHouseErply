<?php

namespace App\Models\Kudos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherMovement extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'Voucher Movement';
}
