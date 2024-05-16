<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcceptedBooksTable extends Migration
{
    public function up()
    {
        Schema::create('accepted_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('google_books_id');
            $table->string('title', 2048); // Adjusting size according to SQL Dump
            $table->string('author', 2048); // Adjusting size according to SQL Dump
            $table->string('year', 100); // Adjusted for potential non-numeric values like '2001-2002'
            $table->text('description');
            $table->string('cover', 255);
            $table->string('genre')->nullable();
            $table->string('pages')->nullable();
            $table->string('purchase_link')->nullable(); // Added purchase_link
            $table->timestamps();
        });

        // Creating indexes for google_books_id and user_id as per SQL Dump
        Schema::table('accepted_books', function (Blueprint $table) {
            $table->index('google_books_id', 'idx_google_books_id');
            $table->index('user_id', 'idx_accepted_books_user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accepted_books');
    }
}
