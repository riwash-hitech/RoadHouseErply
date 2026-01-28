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
        Schema::create('newsystem_inventory_write_offs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventoryWriteOffID')->nullable();
            $table->bigInteger('inventoryWriteOffNo');
            $table->integer('creatorID')->nullable();
            $table->integer('warehouseID');
            $table->integer('stocktakingID')->nullable();
            $table->integer('inventoryID')->nullable();
            $table->integer('recipientID')->nullable();
            $table->integer('reasonID')->nullable();
            $table->string('currencyCode')->nullable();
            $table->double('currencyRate')->nullable();
            $table->date('date');
            $table->date('inventoryTransactionDate');
            $table->string('comments')->nullable();
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
        Schema::dropIfExists('newsystem_inventory_write_offs');
    }
};
