<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id', 'name', 'address', 'area',
        'latitude', 'longitude', 'price_per_hour', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'latitude'       => 'decimal:7',
        'longitude'      => 'decimal:7',
        'price_per_hour' => 'decimal:2',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function spots()
    {
        return $this->hasMany(ParkingSpot::class, 'location_id');
    }
}