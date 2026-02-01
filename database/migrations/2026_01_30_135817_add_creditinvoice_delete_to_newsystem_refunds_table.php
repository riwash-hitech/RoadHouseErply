<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('newsystem_refunds', function (Blueprint $table) {
            $table->integer('credit_invoice_deleted')->default(0)->nullable();
            $table->json('deleted_payment_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsystem_refunds', function (Blueprint $table) {
            $table->dropColumn(['credit_invoice_deleted', 'deleted_payment_id']);
        });
    }
};
