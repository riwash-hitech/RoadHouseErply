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
        Schema::connection('mysql2')->create('newsystem_product_size_sort_order_live', function (Blueprint $table) {
            $table->id();
            $table->string('size')->nullable();
            $table->integer("sort_order");
            $table->decimal("dmx_sort_order");
            $table->string('recid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_product_size_sort_order_live');
    }
};
