<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'subscription_type'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function drivers()
    {
        return $this->users()->where('role', 'driver');
    }
}