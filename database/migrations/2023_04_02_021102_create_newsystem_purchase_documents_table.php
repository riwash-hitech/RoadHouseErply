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
        Schema::create('newsystem_purchase_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchaseDocumentID');
            $table->string('type');
            $table->string('status')->nullable();
            $table->string('currencyCode')->nullable();
            $table->decimal('currencyRate')->nullable();
            $table->bigInteger('warehouseID')->nullable();
            $table->string('warehouseName')->nullable();
            $table->string('number')->length(255)->nullable();
            $table->string('regnumber')->nullable();
            $table->date('date')->nullable();
            $table->date('inventoryTransactionDate')->nullable();
            $table->time('time')->nullable();
            $table->bigInteger('supplierID')->nullable();
            $table->string('supplierName')->nullable();
            $table->bigInteger('supplierGroupID')->nullable(); 
            $table->bigInteger('addressID')->nullable();
            $table->string('address')->nullable(); 
            $table->integer('contactID')->nullable();
            $table->string('contactName')->nullable();
            $table->integer('employeeID')->nullable();
            $table->string('employeeName')->nullable(); 
            $table->integer('supplierID2')->nullable();
            $table->string('supplierName2')->nullable(); 
            $table->integer('stateID')->nullable(); 
            $table->integer('paymentDays')->nullable(); 
            $table->tinyInteger('paid')->nullable(); 
            $table->integer('transactionTypeID')->nullable(); 
            $table->integer('transportTypeID')->nullable(); 
            $table->integer('deliveryTermsID')->nullable(); 
            $table->integer('deliveryTermsLocation')->nullable(); 
            $table->integer('deliveryAddressID')->nullable(); 
            $table->tinyInteger('triangularTransaction')->nullable(); 
            $table->integer('projectID')->nullable(); 
            $table->integer('reasonID')->nullable(); 
            $table->tinyInteger('confirmed')->nullable(); 
            $table->string('referenceNumber')->nullable(); 
            $table->string('notes')->nullable(); 
            $table->string('ediStatus')->nullable(); 
            $table->string('ediText')->nullable(); 
            $table->string('documentURL')->nullable(); 
            $table->decimal('rounding')->nullable(); 
            $table->decimal('netTotal')->nullable(); 
            $table->decimal('vatTotal')->nullable(); 
            $table->decimal('total')->nullable();

            $table->text('netTotalsByTaxRate')->nullable(); 
            $table->text('vatTotalsByTaxRate')->nullable();
            $table->string('invoiceLink')->nullable();
            $table->date('shipDate')->nullable();
            $table->decimal('cost')->nullable();
            $table->decimal('netTotalForAccounting')->nullable();
            $table->decimal('totalForAccounting')->nullable();
            $table->decimal('additionalCosts')->nullable();
            $table->integer('additionalCostsCurrencyId')->nullable();
            $table->decimal('additionalCostsCurrencyRate')->nullable();
            $table->string('additionalCostsDividedBy')->nullable();
            $table->text('baseToDocuments')->nullable();
            $table->text('baseDocuments')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedby')->nullable();
            $table->string('changedby')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->text('rows')->nullable();
            $table->text('attributes')->nullable();
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_purchase_documents');
    }
};
