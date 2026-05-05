<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id', 'spot_id', 'vehicle_id', 'start_at',
        'actual_start_at', 'end_at', 'actual_exit_at',
        'reminder_sent_at', 'total_price', 'status',
        'budget', 'max_minutes',
    ];

    protected $casts = [
        'start_at'         => 'datetime',
        'actual_start_at'  => 'datetime',
        'end_at'           => 'datetime',
        'actual_exit_at'   => 'datetime',
        'reminder_sent_at' => 'datetime',
        'budget'           => 'decimal:2',
        'total_price'      => 'decimal:2',
    ];

    public function user()          { return $this->belongsTo(User::class); }
    public function spot()          { return $this->belongsTo(ParkingSpot::class, 'spot_id'); }
    public function vehicle()       { return $this->belongsTo(Vehicle::class)->withDefault(); }
    public function payment()       { return $this->hasOne(Payment::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
};