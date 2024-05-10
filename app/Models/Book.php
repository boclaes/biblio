<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'year',
        'description',
        'cover',
        'genre',
        'pages',
        'notes_user',
        'want_to_read',
        'reading',
        'done_reading',
    ];
    

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Reviews::class);
    }

}

