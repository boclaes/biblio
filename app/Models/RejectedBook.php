<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'google_books_id', 
        'title', 
        'author', 
        'year'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
