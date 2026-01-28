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
        Schema::connection('mysql2')->create('currentsystem_product_live', function (Blueprint $table) {
            $table->id(); 
            $table->bigInteger('SchoolID')->nullable();
            $table->string('SchoolName')->nullable();
            $table->string('CustomerGroup')->nullable();
            $table->string('ERPLYSKU')->nullable();
            $table->string('WEBSKU')->nullable();
            $table->bigInteger('ITEMID')->nullable();
            $table->string('ItemName')->nullable();
            $table->integer('ColourID')->nullable();
            $table->string('ColourName')->nullable();
            $table->integer('SizeID')->nullable();
            $table->integer('CONFIGID')->nullable();
            $table->string('ConfigName')->nullable();
            $table->string('EANBarcode')->nullable();
            $table->string('SOFTemplate')->nullable();
            $table->string('SOFName')->nullable();
            $table->string('SOFOrder')->nullable();
            $table->string('SOFStatus')->nullable();
            $table->string('PLMStatus')->nullable();
            $table->string('ProductType')->nullable();
            $table->string('ProductSubType')->nullable();
            $table->string('Supplier')->nullable();
            $table->string('Gender')->nullable();
            $table->string('CategoryName')->nullable();
            $table->string('ItemWeightGrams')->nullable();
            $table->string('RetailSalesPrice')->nullable();
            $table->string('RetailSalesPriceExclGST')->nullable();
            $table->string('CostPrice')->nullable();
            $table->string('DefaultStore')->nullable();
            $table->string('SecondaryStore')->nullable();
            $table->string('ERPLYFLAG')->nullable();
            $table->string('ERPLYFLAGModified')->nullable();
            $table->string('AvailableForPurchase')->nullable();
            $table->string('WebEnabled')->nullable();
            $table->tinyInteger('pendingProcess')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currentsystem_product_live');
    }
};
