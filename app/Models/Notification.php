<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
