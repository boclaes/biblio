<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaspberryPi extends Model
{
    protected $table = 'raspberry_pis';

    protected $fillable = [
        'user_id', 'location_id', 'unique_identifier', 'ip_address', 'ngrok_url'
    ];

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
