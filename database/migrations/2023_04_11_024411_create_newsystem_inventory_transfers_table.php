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
        Schema::create('newsystem_inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventoryTransferID')->nullable();
            $table->bigInteger('inventoryTransferNo');
            $table->integer('creatorID')->nullable();
            $table->integer('warehouseFromID')->nullable();
            $table->integer('warehouseToID')->nullable();
            $table->integer('deliveryAddressID')->nullable();
            $table->string('currencyCode')->nullable();
            $table->double('currencyRate')->nullable();
            $table->string('type')->nullable();
            $table->integer('inventoryTransferOrderID')->nullable();
            $table->integer('followupInventoryTransferID')->nullable();
            $table->date('date')->nullable();
            $table->date('shippingDate');
            $table->date('shippingDateActual');
            $table->date('inventoryTransactionDate');
            $table->string('status')->nullable();
            $table->string('notes')->nullable();
            $table->tinyInteger('confirmed')->nullable();
            $table->dateTime('added');
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
        Schema::dropIfExists('newsystem_inventory_transfers');
    }
};
