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
        Schema::create('newsystem_customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customerID');
            $table->string('customerType');
            $table->string('fullName')->nullable();
            $table->string('companyName')->nullable();
            $table->string('companyName2')->nullable();
            $table->bigInteger('companyTypeID')->nullable();
            $table->text('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->integer('personTitleID')->length(255)->nullable();
            $table->string('gender')->nullable();
            $table->bigInteger('groupID')->nullable();
            $table->bigInteger('countryID')->nullable();
            $table->string('groupName')->nullable();
            $table->bigInteger('payerID')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable(); 
            $table->string('email')->nullable();
            $table->string('fax')->nullable(); 
            $table->string('code')->nullable();
            $table->date('birthday')->nullable();
            $table->string('integrationCode')->nullable();
            $table->tinyInteger('flagStatus')->nullable(); 
            $table->tinyInteger('doNotSell')->nullable();
            $table->string('colorStatus')->nullable(); 
            $table->string('image')->nullable();
            $table->integer('taxExempt')->nullable();
            $table->integer('partialTaxExemption')->nullable();
            $table->string('factoringContractNumber',255)->nullable();
            $table->integer('paysViaFactoring')->nullable(); 
            $table->integer('rewardPoints')->nullable();
            $table->string('twitterID')->nullable();
            $table->string('facebookName')->nullable();
            $table->string('creditCardLastNumbers')->nullable();
            $table->integer('isPOSDefaultCustomer')->nullable();
            $table->string('euCustomerType')->nullable();
            $table->integer('credit')->nullable();
            $table->integer('salesBlocked')->nullable();
            $table->string('referenceNumber')->nullable();
            $table->string('customerCardNumber')->nullable();
            $table->integer('rewardPointsDisabled')->nullable();
            $table->integer('customerBalanceDisabled')->nullable();
            $table->integer('posCouponsDisabled')->nullable();
            $table->integer('emailOptOut')->nullable();
            $table->string('lastModifierUsername')->nullable();
            $table->integer('shipGoodsWithWaybills')->nullable();
            $table->text('addresses')->nullable();
            $table->text('contactPersons')->nullable();
            $table->integer('defaultAssociationID')->nullable();
            $table->string('defaultAssociationName')->nullable();
            $table->integer('defaultProfessionalID')->nullable();
            $table->string('defaultProfessionalName')->nullable();
            $table->text('associations')->nullable();
            $table->text('professionals')->nullable();
            $table->text('attributes')->nullable();
            $table->text('longAttributes')->nullable();
            $table->text('externalIDs')->nullable();
            //Customer balance. To retrieve these fields, set input parameter getBalanceInfo to 1.
            $table->decimal('actualBalance')->nullable();
            $table->integer('creditLimit')->nullable();
            $table->decimal('availableCredit')->nullable();
            $table->tinyInteger('creditAllowed')->nullable();
            //To retrieve the following fields, set input parameter responseMode = "detail". These fields are not included in the output by default.
            $table->string('vatNumber')->nullable();
            $table->string('skype')->nullable();
            $table->string('website')->nullable();
            $table->string('webshopUsername')->nullable();
            $table->string('webshopLastLogin')->nullable();
            $table->string('bankName')->nullable();
            $table->string('bankAccountNumber')->nullable();
            $table->string('bankIBAN')->nullable();
            $table->string('bankSWIFT')->nullable();
            $table->integer('jobTitleID')->nullable();
            $table->string('jobTitleName')->nullable();
            $table->integer('companyID')->nullable();
            $table->string('employerName')->nullable();
            $table->integer('customerManagerID')->nullable();
            $table->string('customerManagerName')->nullable();
            $table->integer('paymentDays')->nullable();
            $table->string('penaltyPerDay')->nullable();
            $table->integer('priceListID')->nullable();
            $table->integer('priceListID2')->nullable();
            $table->integer('priceListID3')->nullable();
            $table->integer('priceListID4')->nullable();
            $table->integer('priceListID5')->nullable();
            $table->tinyInteger('outsideEU')->default(0);
            $table->integer('businessAreaID')->nullable();
            $table->string('businessAreaName')->nullable();
            $table->integer('deliveryTypeID')->nullable();
            $table->integer('signUpStoreID')->nullable();
            $table->integer('homeStoreID')->nullable();
            $table->integer('taxOfficeID')->nullable();
            $table->string('notes')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->integer('lastModifierEmployeeID')->nullable();
            $table->dateTime('added')->nullable();
            // Data exchange channels
            $table->tinyInteger('emailEnabled')->nullable();
            $table->tinyInteger('eInvoiceEnabled')->nullable();
            $table->tinyInteger('docuraEDIEnabled')->nullable();
            // E-invoicing settings
            $table->string('eInvoiceEmail')->nullable();
            $table->string('eInvoiceReference')->nullable();
            $table->tinyInteger('mailEnabled')->nullable();
            $table->string('operatorIdentifier')->nullable();
            $table->string('EDI')->nullable();
            $table->string('PeppolID')->nullable();
            // EDI settings
            $table->string('GLN')->nullable();
            $table->string('ediType')->nullable();
            // Customer's first postal address.
            $table->string('address')->nullable();
            $table->text('street')->nullable();
            $table->text('address2')->nullable();
            $table->string('city')->length(255)->nullable();
            $table->string('postalCode')->length(16)->nullable();
            $table->string('state')->length(255)->nullable();
            $table->string('country')->length(255)->nullable();
            $table->integer('addressTypeID')->nullable();
            $table->string('addressTypeName')->length(255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_customers');
    }
};
