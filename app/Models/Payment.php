<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id','payment_method_id','amount',
        'payment_method','payment_status','transaction_ref','paid_at',
    ];
    protected $casts = ['paid_at' => 'datetime'];
    public function reservation()   { return $this->belongsTo(Reservation::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
}
