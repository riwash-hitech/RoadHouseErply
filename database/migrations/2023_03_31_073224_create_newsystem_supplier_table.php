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
        Schema::create('newsystem_suppliers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('supplierID');
            $table->string('supplierType');
            $table->string('fullName')->nullable();
            $table->string('companyName')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->bigInteger('groupID')->nullable();
            $table->string('groupName')->length(255)->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->string('code')->nullable();
            $table->string('integrationCode')->nullable();
            $table->bigInteger('vatrateID')->nullable(); 
            $table->string('currencyCode')->nullable();
            $table->bigInteger('deliveryTermsID')->nullable(); 
            $table->bigInteger('countryID')->nullable();
            $table->string('countryName')->nullable();
            $table->string('countryCode')->nullable();
            $table->string('address')->nullable(); 
            $table->string('GLN')->nullable();
            $table->text('attributes')->nullable(); 
            // ****
            $table->string('vatNumber')->nullable();
            $table->string('skype')->nullable();
            $table->string('website')->nullable();
            $table->string('bankName')->nullable();
            $table->string('bankAccountNumber')->nullable(); 
            $table->string('bankIBAN')->nullable();
            $table->string('bankSWIFT')->nullable();
            $table->date('birthday')->nullable();
            $table->bigInteger('companyID')->nullable();
            $table->string('parentCompanyName')->nullable();
            $table->bigInteger('supplierManagerID')->nullable();
            $table->string('supplierManagerName')->nullable();
            $table->integer('paymentDays')->nullable();
            $table->string('notes')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedby')->nullable();
            $table->string('changedby')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_supplier');
    }
};
