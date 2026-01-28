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
        Schema::create('newsystem_dimension_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('variationID');
            $table->bigInteger('parentID');
            $table->string("name");
            $table->string("code");
            $table->integer("order");
            $table->tinyInteger('active');
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
        Schema::dropIfExists('newsystem_dimension_values');
    }
};
