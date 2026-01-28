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
        Schema::create('newsystem_giftcards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('giftCardID');
            $table->bigInteger('typeID');
            $table->string('code')->nullable();
            $table->decimal('value')->nullable();
            $table->decimal('balance')->nullable();
            $table->integer('purchasingCustomerID')->nullable();
            $table->dateTime('purchaseDateTime')->nullable();
            $table->bigInteger('redeemingCustomerID')->nullable();
            $table->dateTime('redemptionDateTime')->nullable();
            $table->date('expirationDate')->nullable();
            $table->bigInteger('purchaseInvoiceID')->nullable();
            $table->bigInteger('vatrateID')->nullable();
            $table->string('information')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedby')->nullable(); 
            $table->dateTime('lastModified')->nullable();
            $table->string('changedby')->nullable(); 
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_giftcards');
    }
};
