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
        Schema::create('newsystem_cashins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("transactionID"); 
            $table->decimal("sum")->nullable();
            $table->string("currencyCode")->nullable();
            $table->decimal("currencyRate")->nullable();
            $table->integer("warehouseID")->nullable();
            $table->string("warehouseName")->nullable();
            $table->integer("pointOfSaleID")->nullable();
            $table->integer("pointOfSaleName")->nullable();
            $table->integer("employeeID")->nullable();;
            $table->string("employeeName")->nullable();;
            $table->dateTime("dateTime")->nullable();
            $table->integer("reasonID")->nullable();
            $table->string("comment")->nullable();
            $table->dateTime("added")->nullable();
            $table->dateTime("lastModified")->nullable();
            $table->decimal("attributes")->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_cashins');
    }
};
