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
        Schema::create('newsystem_payment_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('paymentTypeID')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('print_name')->nullable();
            $table->string('quickBooksDebitAccount')->nullable();
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
        Schema::dropIfExists('newsystem_payment_types');
    }
};
