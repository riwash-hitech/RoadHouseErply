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
        Schema::create('newsystem_warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('warehouseID');
            $table->string('name',255);
            $table->string('code',255);
            $table->integer('addressID')->nullable();
            $table->string('address')->nullable();
            // The same address as above, split into components:
            $table->text('street')->nullable();
            $table->string('address2')->nullable();
            $table->string('city',255)->nullable();
            $table->string('ZIPcode',16)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('country', 255)->nullable();
            // Contact information for this location
            $table->string('companyName', 255)->nullable();
            $table->string('companyCode', 255)->nullable();
            $table->string('companyVatNumber', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('fax', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('bankName', 255)->nullable();
            $table->string('bankAccountNumber', 255)->nullable();
            $table->string('iban', 255)->nullable();
            $table->string('swift', 255)->nullable();
            // *****
            $table->integer('storeRegionID')->nullable();
            $table->integer('assortmentID')->nullable();
            $table->integer('priceListID')->nullable();
            $table->integer('priceListID2')->nullable();
            $table->integer('priceListID3')->nullable();
            $table->integer('priceListID4')->nullable();
            $table->integer('priceListID5')->nullable();
            $table->string('storeGroups')->nullable();
            $table->string('stateGroup')->nullable();
            $table->integer('defaultCustomerGroupID')->nullable();
            $table->tinyInteger('onlineAppointmentsEnabled')->nullable();
            $table->tinyInteger('isOfflineInventory')->nullable();
            $table->string('timeZone')->nullable();
            $table->text('attributes')->nullable();

            $table->integer('order_sw')->nullable();
            $table->integer('receiptAddressID')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedBy')->length(200)->nullable();
            $table->dateTime('changed')->nullable();
            $table->string('changedBy')->nullable();


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_warehouse_locations');
    }
};
