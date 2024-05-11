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
            $table->string('google_books_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rejected_books');
    }
}
