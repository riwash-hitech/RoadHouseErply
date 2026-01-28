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
        Schema::create('newsystem_product_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('productCategoryID');
            $table->bigInteger('parentCategoryID');
            $table->string('productCategoryName')->length(200)->nullable();
            $table->text('attributes')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedBy')->length(200)->nullable();
            $table->dateTime('changed')->nullable();
            $table->string('changedBy')->nullable();
            $table->integer('order_sw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_product_categories');
    }
};
