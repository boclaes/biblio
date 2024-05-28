<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_books_id', 'title', 'author', 'year', 'description', 'cover', 'genre', 'pages', 'notes_user', 'review', 'want_to_read', 'reading', 'done_reading', 'borrowed', 'place'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function borrowing()
    {
        return $this->hasOne(Borrowing::class);
    }
}


