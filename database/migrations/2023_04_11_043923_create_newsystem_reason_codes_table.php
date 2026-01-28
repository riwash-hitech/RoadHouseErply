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
        Schema::create('newsystem_reason_codes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reasonID')->nullable();
            $table->string('name')->nullable();
            $table->string('purpose')->nullable();
            $table->text('manualDiscountDisablesPromotionTiers')->nullable();
            $table->dateTime('added')->nullable();
            $table->dateTime('lastModified')->nullable();
            $table->string('code')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_reason_codes');
    }
};
