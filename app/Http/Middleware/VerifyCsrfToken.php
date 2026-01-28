<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        '/webhooks/v1/salesDocument/update',
        '/webhooks/v1/salesDocument/delete',
        '/webhooks/v1/payment/createUpdate',
        '/webhooks/v1/product/createUpdate',
        '/webhooks/v1/product/delete',
        '/webhooks/v1//inventoryTransfer/createUpdate',
        '/webhooks/v1//customer/createUpdate',
    ];
}
