<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ParkingLocation extends Model
{
    protected $fillable = ['admin_id','name','address','area','latitude','longitude','price_per_hour','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function admin()  { return $this->belongsTo(User::class, 'admin_id'); }
    public function spots()  { return $this->hasMany(ParkingSpot::class, 'location_id'); }
}