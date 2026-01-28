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
        Schema::create('newsystem_user_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("logID")->nullable();
            $table->string('userName')->nullable();
            $table->dateTime("timestamp")->nullable();
            $table->string('tableName')->nullable();
            $table->string('itemID')->nullable();
            $table->tinyInteger('pendingProcess')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsystem_user_operation_logs');
    }
};
