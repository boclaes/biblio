<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('google_books_id')->unique()->nullable();
            $table->string('title');
            $table->string('author');
            $table->string('year')->nullable();  // Changed from year type to string type to match your SQL dump structure
            $table->text('description');
            $table->string('cover', 2048); // Increased size for cover to accommodate URLs
            $table->string('genre')->nullable(); // Making genre optional
            $table->integer('pages')->nullable(); // Changed to integer type, assuming pages would be numeric
            $table->text('notes_user')->nullable();
            $table->text('review')->nullable();  // Adding review field similar to notes_user
            $table->boolean('want_to_read')->default(false);
            $table->boolean('reading')->default(false);
            $table->boolean('done_reading')->default(false);
            $table->boolean('borrowed')->default(false);
            $table->integer('place')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
