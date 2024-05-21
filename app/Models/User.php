<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import the HasApiTokens trait

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Add HasApiTokens trait here

    protected $fillable = [
        'name',
        'email',
        'password',
        'last_book_index'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($user) {
            // Detach all related books
            $user->books()->detach();
            
            // Optionally, find and delete books that no longer belong to any users
            $orphanBooks = Book::has('users', '=', 0)->get();  // Fetch books with no more associated users
            foreach ($orphanBooks as $orphanBook) {
                $orphanBook->delete();  // Delete each orphan book
            }
    
            // Delete all accepted and rejected books
            $user->acceptedBooks()->delete();
            $user->rejectedBooks()->delete();
        });
    }

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }

    public function acceptedBooks()
    {
        return $this->hasMany(AcceptedBook::class);
    }

    public function rejectedBooks()
    {
        return $this->hasMany(RejectedBook::class);
    }
}
