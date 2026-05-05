<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id','token','card_brand','last_four','expiry','is_default'];
    protected $casts = ['is_default' => 'boolean'];
    protected $hidden = ['token'];
    public function user()     { return $this->belongsTo(User::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}