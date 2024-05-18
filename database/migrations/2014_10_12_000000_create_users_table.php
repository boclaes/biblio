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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id'); // Using bigIncrements for compatibility with bigint(20) UNSIGNED AUTO_INCREMENT
            $table->string('name', 255); // Explicitly defining the length
            $table->string('email', 255)->unique(); // Email is unique as per your SQL dump
            $table->timestamp('email_verified_at')->nullable()->default(null); // Nullable timestamp
            $table->string('password', 255); // Password field with explicit length
            $table->string('remember_token', 100)->nullable()->default(null); // Nullable token with default as null
            $table->timestamps(); // This creates `created_at` and `updated_at` fields
            $table->integer('last_book_index')->default(0); // Additional field for last book index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
