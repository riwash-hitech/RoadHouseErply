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
        Schema::create('newsystem_dayclsoing', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('dayID')->nullable();
            $table->integer('warehouseID')->nullable();
            $table->string('warehouseName')->nullable();
            $table->integer('pointOfSaleID')->nullable();
            $table->string('pointOfSaleName')->nullable();
            $table->integer('drawerID')->nullable();
            $table->string('shiftType')->nullable();
            $table->text('employees')->nullable();
            $table->dateTime('openedUnixTime')->nullable();
            $table->integer('openedByEmployeeID')->nullable();
            $table->string('openedByEmployeeName')->nullable();
            $table->decimal('openedSum')->nullable();
            $table->dateTime('closedUnixTime')->nullable();
            $table->integer('closedByEmployeeID')->nullable();
            $table->string('closedByEmployeeName')->nullable();
            $table->decimal('closedSum')->nullable();
            $table->decimal('bankedSum')->nullable();
            $table->text('notes')->nullable();
            $table->integer('reasonID')->nullable();
            $table->string('currencyCode')->nullable();
            $table->text('attributes');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_dayclsoing');
    }
};
