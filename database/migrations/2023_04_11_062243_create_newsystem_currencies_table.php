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
        Schema::create('newsystem_currencies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('currencyID')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->decimal('rate')->nullable();
            $table->integer('default')->nullable();
            $table->string('nameShort')->nullable();
            $table->string('nameFraction')->nullable();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();  
            $table->dateTime('added')->nullable();
            $table->dateTime('lastModified')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_currencies');
    }
};
