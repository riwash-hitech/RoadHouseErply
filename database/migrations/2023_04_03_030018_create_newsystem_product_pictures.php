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
        Schema::create('newsystem_product_pictures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('productPictureID');
            $table->bigInteger('productID');
            $table->string('name')->nullable();
            $table->string('thumbURL')->nullable();
            $table->string('smallURL')->nullable();
            $table->string('largeURL')->nullable();
            $table->string('fullURL')->nullable();
            $table->string('external')->nullable();
            $table->dateTime('added');
            $table->dateTime('lastModified');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_product_pictures');
    }
};
