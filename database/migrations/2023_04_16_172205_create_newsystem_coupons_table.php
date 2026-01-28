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
        Schema::create('newsystem_coupons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('couponID')->nullable();
            $table->integer('campaignID')->nullable();
            $table->integer('warehouseID')->nullable();
            $table->date('issuedFromDate')->nullable();
            $table->date('issuedUntilDate')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->tinyInteger('printedAutomaticallyInPOS')->nullable();
            $table->integer('threshold')->nullable();
            $table->string('measure')->nullable();
            $table->string('thresholdType')->nullable();
            $table->tinyInteger('promptCashier')->nullable();
            $table->integer('printingCostInRewardPoints')->nullable();
            $table->string('print')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('added')->nullable();
            $table->dateTime('lastModified')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_coupons');
    }
};
