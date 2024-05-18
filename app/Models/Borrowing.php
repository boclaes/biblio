<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'borrower_name', 'borrowed_since', 'user_id'];

    // Attributes that should be mutated to dates
    protected $dates = ['borrowed_since'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optionally, you can add an accessor to ensure the date is always returned as a Carbon instance
    public function getBorrowedSinceAttribute($value)
    {
        return Carbon::parse($value);
    }
}
