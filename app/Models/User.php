<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function updateUser(array $data)
{
    // If password is provided, hash it; otherwise, remove it from the update data
    if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    } else {
        unset($data['password']); // Prevent overwriting with null
    }

    // Update the user with the provided data
    return $this->update($data);
}


    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    // Role-based Accessors (Better alternative to isAdmin() & isUser())
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    public function getIsCustomerAttribute(): bool
    {
        return $this->role === 'customer'; // Fixed from 'user'
    }

    // Auto-hash password when setting
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
