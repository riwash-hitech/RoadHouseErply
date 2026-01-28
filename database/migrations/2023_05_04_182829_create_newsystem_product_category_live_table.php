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
        Schema::connection('mysql2')->create('newsystem_product_category_live', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('erplyCatID')->nullable();
            $table->string('name');
            $table->tinyInteger('pendingProcess')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_product_category_live');
    }
};
