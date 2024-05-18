<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Add user_id with foreign key constraint
            $table->string('borrower_name');
            $table->dateTime('borrowed_since');
            $table->timestamps();

            // Add indexes and constraints similar to the provided SQL
            $table->index('book_id', 'idx_borrowings_book_id');
        });

        // Set the AUTO_INCREMENT for the `id` field if necessary
        DB::statement('ALTER TABLE borrowings AUTO_INCREMENT = 5');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('borrowings');
    }
};
