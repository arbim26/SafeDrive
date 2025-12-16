<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'subscription',
        'company_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * JWT identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Custom JWT claims
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'subscription' => $this->subscription,
            'name' => $this->name,
            'email' => $this->email
        ];
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    public function driverDetail()
    {
        return $this->hasOne(DriverDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
