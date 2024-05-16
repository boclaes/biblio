<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectedBooksTable extends Migration
{
    public function up()
    {
        Schema::create('rejected_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('google_books_id', 255);  // Specified max length
            $table->string('title', 2048);           // Increased length as per SQL dump
            $table->string('author', 2048)->nullable(); // Increased length and made nullable
            $table->string('year', 100);            // Kept as string, max length 100
            $table->timestamps();                   // Default Laravel timestamps
        });

        // Adding additional indexes as per SQL dump
        Schema::table('rejected_books', function (Blueprint $table) {
            $table->index('google_books_id', 'idx_google_books_id');  // Index for google_books_id
            $table->index('user_id', 'idx_rejected_books_user_id');  // Index for user_id
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejected_books');
    }
}
