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
        Schema::create('newsystem_dimensions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('dimensionID');
            $table->string('name');
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
        Schema::dropIfExists('newsystem_dimesnsions');
    }
};
