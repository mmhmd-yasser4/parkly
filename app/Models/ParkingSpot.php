<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ParkingSpot extends Model
{
    protected $fillable = ['location_id','spot_number','status','status_source'];
    public function location()     { return $this->belongsTo(ParkingLocation::class, 'location_id'); }
    public function reservations() { return $this->hasMany(Reservation::class, 'spot_id'); }
}

