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
        Schema::create('newsystem_product_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('productGroupID');
            $table->string('name',255);
            $table->integer('showInWebshop')->nullable();
            $table->tinyInteger('nonDiscountable');
            $table->integer('positionNo')->nullable();
            $table->bigInteger('parentGroupID')->nullable();
            $table->text('images')->nullable();
            $table->text('subGroups')->nullable();
            $table->text('attributes')->nullable();
            $table->text('vatrates')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('addedBy')->length(200)->nullable();
            $table->dateTime('changed')->nullable();
            $table->string('changedBy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_product_groups');
    }
};
