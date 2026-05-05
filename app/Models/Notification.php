<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'reservation_id', 'title', 'message', 'type'];

    protected $casts = ['read_at' => 'datetime'];

    public function user()        { return $this->belongsTo(User::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }

    public static function notify(int $userId, string $title, string $message, string $type, ?int $reservationId = null): void
    {
        static::create([
            'user_id'        => $userId,
            'reservation_id' => $reservationId,
            'title'          => $title,
            'message'        => $message,
            'type'           => $type,
        ]);
    }
}