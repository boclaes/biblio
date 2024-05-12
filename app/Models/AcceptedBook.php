<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcceptedBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'google_books_id', 
        'title', 
        'author', 
        'year', 
        'description', 
        'cover', 
        'genre', 
        'pages',
        'purchase_link' 
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
