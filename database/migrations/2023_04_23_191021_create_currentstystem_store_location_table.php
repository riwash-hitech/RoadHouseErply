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
        Schema::connection('mysql2')->create('currentstystem_store_location', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("erplyID")->nullable();
            $table->bigInteger("erplyAssortmentID")->nullable();
            $table->bigInteger("LocationID")->nullable();
            $table->string("LocationName")->nullable();
            $table->string("LocationType")->nullable();
            $table->tinyInteger("MainStoreFlag")->nullable();
            $table->tinyInteger("AllowClickAndCollect")->nullable();
            $table->string("ADDRESS")->nullable();
            $table->string("STREET")->nullable();
            $table->string("CITY")->nullable();
            $table->string("STATE")->nullable();
            $table->string("Postcode")->nullable();
            $table->string("EMAIL")->nullable();
            $table->string("PHONE")->nullable();
            $table->string("ClickAndCollectInfo")->nullable();
            $table->string("LONGITUDE")->nullable();
            $table->string("LATITUDE")->nullable();
            $table->string("StoreHours")->nullable();
            $table->tinyInteger("AllowTransferOrdersTo")->nullable();
            $table->tinyInteger("AllowTransferOrdersFrom")->nullable();
            $table->string("Division")->nullable();
            $table->string("CostCentre")->nullable();
            $table->string("State2")->nullable();
            $table->string("Region")->nullable();
            $table->string("DefaultReceiptLocation")->nullable();
            $table->string("DefaultIssueLocation")->nullable();
            $table->bigInteger("DefaultCustomer")->nullable();
            $table->bigInteger("StoreID")->nullable();
            $table->string("LocationFlag")->nullable();
            $table->dateTime("LastModifiedDateTime")->nullable();
            $table->tinyInteger("pendingProcess")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currentstystem_store_location');
    }
};
