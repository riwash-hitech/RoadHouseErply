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
        Schema::create('newsystem_user_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('userID');
            $table->text('previousData')->nullable();
            $table->text('newData')->nullable();
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_user_logs');
    }
};
