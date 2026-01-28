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
        Schema::create('newsystem_employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employeeID')->nullable();
            $table->string('fullName')->nullable();
            $table->string('employeeName')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->string('code')->nullable();
            $table->string('gender')->nullable();
            $table->string('userID')->nullable();
            $table->string('username')->nullable();
            $table->string('userGroupID')->nullable();
            $table->string('description')->nullable();
            $table->text('warehouses')->nullable();
            $table->text('pointsOfSale')->nullable();
            $table->text('productIDs')->nullable();
            $table->text('attributes')->nullable();
            $table->string('skype')->nullable();
            $table->date('birthday')->nullable();
            $table->integer('jobTitleID')->nullable();
            $table->string('jobTitleName')->nullable();
            $table->string('notes')->nullable();
            $table->string('drawerID')->nullable();
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
        Schema::dropIfExists('newsystem_employees');
    }
};
