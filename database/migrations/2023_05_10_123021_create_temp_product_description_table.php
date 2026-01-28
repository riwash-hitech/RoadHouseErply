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
        Schema::connection("mysql2")->create('temp_product_description', function (Blueprint $table) {
            $table->id();
            $table->string("WEBSKU");
            $table->string("ITEMID");
            $table->text("LongDescription")->nullable();
            $table->dateTime("ModifiedDateTime");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_product_description');
    }
};
