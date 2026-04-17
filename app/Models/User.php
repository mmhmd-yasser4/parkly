<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'full_name', 'email', 'phone_number', 'password', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',];

    protected $casts = ['is_active' => 'boolean'];

    public function socialAccounts() { return $this->hasMany(SocialAccount::class); }
    public function vehicles()       { return $this->hasMany(Vehicle::class); }
    public function reservations()   { return $this->hasMany(Reservation::class); }
    public function notifications()  { return $this->hasMany(Notification::class); }
    public function parkingLocations(){ return $this->hasMany(ParkingLocation::class, 'admin_id'); }
    public function paymentMethods() { return $this->hasMany(PaymentMethod::class); }
}