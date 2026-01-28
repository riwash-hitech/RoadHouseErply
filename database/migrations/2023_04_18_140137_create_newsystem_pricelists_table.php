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
        Schema::create('newsystem_pricelists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pricelistID')->nullable();
            $table->string('name')->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->tinyInteger('active')->nullable();
            $table->string('type')->nullable();
            $table->text('pricelistRules')->nullable();
            $table->string('attributes')->nullable(); 
            $table->string('addedByUserName')->nullable();
            $table->dateTime('added')->nullable();
            $table->string('lastModifiedByUserName')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_pricelists');
    }
};
