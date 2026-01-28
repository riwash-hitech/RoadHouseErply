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
        Schema::create('newsystem_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('paymentID')->nullable();
            $table->bigInteger('documentID')->nullable();
            $table->bigInteger('customerID')->nullable();
            $table->integer('typeID')->nullable();
            $table->string('type')->nullable();
            $table->date('date')->nullable();
            $table->decimal('sum')->nullable();
            $table->string('currencyCode')->nullable();
            $table->decimal('currencyRate')->nullable();  
            $table->decimal('cashPaid')->nullable();
            $table->decimal('cashChange')->nullable();
            $table->string('info')->nullable();
            $table->string('cardHolder')->nullable();
            $table->string('cardNumber')->nullable();
            $table->string('cardType')->nullable();
            $table->string('authorizationCode')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->tinyInteger('isPrepayment')->nullable(); 
            $table->integer('bankTransactionID')->nullable();
            $table->string('bankAccount')->nullable();
            $table->string('bankDocumentNumber')->nullable();  
            $table->string('bankDate')->nullable();  
            $table->string('bankPayerAccount')->nullable();  
            $table->string('bankPayerName')->nullable();  
            $table->string('bankPayerCode')->nullable();  
            $table->string('bankSum')->nullable();  
            $table->string('bankReferenceNumber')->nullable();  
            $table->string('bankDescription')->nullable();  
            $table->string('bankCurrency')->nullable();  
            $table->string('archivalNumber')->nullable();  
            $table->tinyInteger('storeCredit')->nullable();  
            $table->string('paymentServiceProvider')->nullable();  
            $table->string('aid')->nullable();  
            $table->string('applicationLabel')->nullable();  
            $table->string('pinStatement')->nullable();  
            $table->string('cryptogramType')->nullable();  
            $table->string('cryptogram')->nullable();  
            $table->string('expirationDate')->nullable();  
            $table->string('entryMethod')->nullable();  
            $table->string('transactionType')->nullable();  
            $table->string('transactionNumber')->nullable();  
            $table->string('transactionId')->nullable();  
            $table->string('transactionType2')->nullable();  
            $table->dateTime('transactionTime')->nullable(); 
            $table->string('klarnaPaymentID')->nullable(); 
            $table->decimal('certificateBalance')->nullable();  
            $table->string('statusCode')->nullable();  
            $table->string('statusMessage')->nullable();  
            $table->integer('giftCardVatRateID')->nullable();  
            $table->string('signature')->nullable();  
            $table->string('signatureIV')->nullable();  
            $table->text('attributes')->nullable();
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
        Schema::dropIfExists('newsystem_payments');
    }
};
