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
        Schema::create('newsystem_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customerGroupID');
            $table->integer('parentID')->nullable();
            $table->string('name')->nullable();
            $table->integer('pricelistID')->nullable();
            $table->integer('pricelistID2')->nullable();
            $table->integer('pricelistID3')->nullable();
            $table->integer('pricelistID4')->nullable();
            $table->integer('pricelistID5')->nullable();
            $table->dateTime('added')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->text('attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_customer_groups');
    }
};
