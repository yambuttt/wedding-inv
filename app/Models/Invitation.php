<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function greetings()
    {
        return $this->hasMany(Greeting::class)->latest();
    }

    public function guests()
    {
        return $this->hasMany(Guest::class)->oldest();
    }
}
