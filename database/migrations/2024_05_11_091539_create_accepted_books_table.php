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
            $table->string('title');
            $table->string('author');
            $table->string('year', 100); // Adjusted for potential non-numeric values like '2001-2002'
            $table->text('description');
            $table->string('cover');
            $table->string('genre')->nullable();
            $table->string('pages')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accepted_books');
    }
}
