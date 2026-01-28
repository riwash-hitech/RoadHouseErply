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
        Schema::create('newsystem_purchase_document_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("purchaseDocumentID"); 
            $table->integer("stableRowID")->nullable();
            $table->bigInteger("productID")->nullable();
            $table->integer("serviceID")->nullable();
            $table->string("itemName")->nullable();
            $table->string("code")->nullable();
            $table->string("code2")->nullable();
            $table->integer("vatrateID")->nullable();
            $table->decimal("amount")->nullable();;
            $table->decimal("price")->nullable();;
            $table->decimal("discount")->nullable();
            $table->date("deliveryDate")->nullable();
            $table->decimal("unitCost")->nullable();
            $table->decimal("costTotal")->nullable();
            $table->integer("packageID")->nullable();
            $table->decimal("amountOfPackages")->nullable();
            $table->decimal("amountInPackage")->nullable();
            $table->string("packageType")->nullable();
            $table->integer("packageTypeID")->nullable();
            $table->integer("jdoc")->nullable();
            $table->string("supplierPriceListSupplierCode")->nullable();
            $table->string("supplierPriceListImportCode")->nullable();
            $table->string("supplierPriceListNotes")->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_purchase_details');
    }
};
