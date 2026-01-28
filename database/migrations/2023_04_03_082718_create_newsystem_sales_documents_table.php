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
        Schema::create('newsystem_sales_documents', function (Blueprint $table) {
            $table->id();
            // 131
            $table->bigInteger('salesDocumentID');
            $table->string('type');
            $table->string('exportInvoiceType')->nullable();
            $table->string('currencyCode')->nullable();
            $table->decimal('currencyRate')->nullable();
            $table->bigInteger('warehouseID')->nullable();
            $table->string('warehouseName')->nullable();
            $table->bigInteger('pointOfSaleID')->nullable();
            $table->string('pointOfSaleName')->nullable();
            $table->integer('pricelistID')->nullable();
            $table->string('number')->nullable();
            $table->date('date')->nullable();
            $table->date('inventoryTransactionDate')->nullable();
            $table->time('time')->nullable();
            $table->bigInteger('clientID')->nullable(); 
            $table->string('clientName')->nullable();
            $table->string('clientEmail')->nullable(); 
            $table->string('clientCardNumber')->nullable();
            $table->integer('addressID')->nullable();
            $table->string('address')->nullable();
            $table->string('clientFactoringContractNumber')->nullable(); 
            $table->tinyInteger('clientPaysViaFactoring')->nullable();
            $table->integer('payerID')->nullable(); 
            $table->string('payerName')->nullable(); 
            $table->integer('payerAddressID')->nullable(); 
            $table->string('payerAddress')->nullable(); 
            $table->string('payerFactoringContractNumber')->nullable(); 
            $table->integer('payerPaysViaFactoring')->nullable(); 
            $table->integer('shipToID')->nullable(); 
            $table->string('shipToName')->nullable(); 
            $table->integer('shipToAddressID')->nullable(); 
            $table->string('shipToAddress')->nullable(); 
            $table->integer('contactID')->nullable(); 
            $table->string('contactName')->nullable(); 
            $table->integer('shipToContactID')->nullable(); 
            $table->string('shipToContactName')->nullable(); 
            $table->integer('employeeID')->nullable(); 
            $table->string('employeeName')->nullable(); 
            $table->integer('projectID')->nullable(); 
            $table->string('invoiceState')->nullable(); 
            $table->string('paymentType')->nullable(); 
            $table->integer('paymentTypeID')->nullable(); 
            $table->integer('paymentDays')->nullable(); 
            $table->string('paymentStatus')->nullable();
            $table->text('baseDocuments')->nullable(); 
            $table->text('followUpDocuments')->nullable();
            $table->tinyInteger('previousReturnsExist')->nullable();
            $table->integer('printDiscounts')->nullable();
            $table->integer('algorithmVersion')->nullable();
            $table->integer('algorithmVersionCalculated')->nullable();
            $table->tinyInteger('confirmed')->nullable();
            $table->string('notes')->nullable();
            $table->string('internalNotes')->nullable();
            $table->decimal('netTotal')->nullable();
            $table->decimal('vatTotal')->nullable();
            $table->text('netTotalsByRate')->nullable();
            $table->text('vatTotalsByRate')->nullable();
            $table->text('netTotalsByTaxRate')->nullable();
            $table->text('vatTotalsByTaxRate')->nullable();
            $table->decimal('rounding')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('paid')->nullable();
            $table->decimal('externalNetTotal',8,4)->nullable();
            $table->decimal('externalVatTotal',8,4)->nullable();
            $table->decimal('externalRounding',8,4)->nullable();
            $table->decimal('externalTotal',8,4)->nullable();
            $table->string('taxExemptCertificateNumber')->nullable();
            $table->text('otherCommissionReceivers')->nullable();
            $table->integer('packerID')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->text('webShopOrderNumbers')->nullable();
            $table->string('trackingNumber')->nullable();
            $table->string('fulfillmentStatus')->nullable();
            $table->string('customReferenceNumber')->nullable();
            $table->double('cost')->nullable();
            $table->tinyInteger('reserveGoods')->nullable();
            $table->date('reserveGoodsUntilDate')->nullable();
            $table->date('deliveryDate')->nullable();
            $table->string('deliveryTypeID')->nullable();
            $table->string('deliveryTypeName')->nullable();
            $table->date('shippingDate')->nullable();
            $table->string('packingUnitsDescription')->nullable();
            $table->string('penalty')->nullable();
            $table->tinyInteger('triangularTransaction')->nullable();
            $table->tinyInteger('purchaseOrderDone')->nullable();
            $table->integer('transactionTypeID')->nullable();
            $table->string('transactionTypeName')->nullable();
            $table->integer('transportTypeID')->nullable();
            $table->string('transportTypeName')->nullable();
            $table->string('deliveryTerms')->nullable();
            $table->string('deliveryTermsLocation')->nullable();
            $table->string('euInvoiceType')->nullable();
            $table->tinyInteger('deliveryOnlyWhenAllItemsInStock')->nullable();
            $table->string('eInvoiceBuyerID')->nullable();
            $table->integer('workOrderID')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->string('lastModifierUsername')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('invoiceLink')->nullable();
            $table->string('receiptLink')->nullable();
            $table->text('returnedPayments')->nullable();
            $table->decimal('amountAddedToStoreCredit')->nullable();
            $table->decimal('amountPaidWithStoreCredit')->nullable();
            $table->integer('applianceID')->nullable();
            $table->string('applianceReference')->nullable();
            $table->integer('assignmentID')->nullable();
            $table->integer('vehicleMileage')->nullable();
            $table->string('customNumber')->nullable();
            $table->decimal('advancePayment')->nullable();
            $table->integer('advancePaymentPercent')->nullable();
            $table->tinyInteger('printWithOriginalProductNames')->nullable();
            $table->tinyInteger('hidePrices')->nullable();
            $table->tinyInteger('hideAmounts')->nullable();
            $table->tinyInteger('hideTotal')->nullable();
            $table->tinyInteger('isFactoringInvoice')->nullable();
            $table->integer('taxOfficeID')->nullable();
            $table->date('periodStartDate')->nullable();
            $table->date('periodEndDate')->nullable();
            $table->tinyInteger('orderArrived')->nullable();
            $table->tinyInteger('orderInvoiced')->nullable();
            $table->string('ediStatus')->nullable();
            $table->string('ediText')->nullable();
            $table->string('documentURL')->nullable();
            $table->tinyInteger('hidePaymentDays')->nullable();
            $table->string('creditInvoiceType')->nullable();
            $table->text('issuedCouponIDs')->nullable();
            $table->text('attributes')->nullable();
            $table->text('longAttributes')->nullable();
            $table->text('jdoc')->nullable();
            $table->text('rows')->nullable();
             


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_sales_documents');
    }
};
