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
        Schema::create('newsystem_sales_document_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("salesDocumentID");
            $table->integer("rowID");
            $table->integer("stableRowID")->nullable();
            $table->integer("productID");
            $table->integer("serviceID")->nullable();
            $table->string("itemName")->nullable();
            $table->string("code")->nullable();
            $table->integer("vatrateID")->nullable();
            $table->decimal("amount");
            $table->decimal("price");
            $table->decimal("discount");
            $table->decimal("finalNetPrice");
            $table->decimal("finalPriceWithVAT");
            $table->decimal("rowNetTotal");
            $table->decimal("rowVAT");
            $table->decimal("rowTotal");
            $table->date("deliveryDate");
            $table->integer("returnReasonID");
            $table->integer("employeeID");
            $table->string("campaignIDs");
            $table->integer("containerID");
            $table->integer("containerAmount");
            $table->tinyInteger("originalPriceIsZero");
            $table->integer("packageID");
            $table->decimal("amountOfPackages");
            $table->decimal("amountInPackage");
            $table->string("packageType");
            $table->integer("packageTypeID");
            $table->integer("sourceWaybillID");
            $table->integer("billingStatementID");
            $table->date("billingStartDate");
            $table->date("billingEndDate");
            $table->string("batch");
            $table->double("warehouseValue");
            $table->text("jdoc");
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_sales_document_details_tabls');
    }
};
