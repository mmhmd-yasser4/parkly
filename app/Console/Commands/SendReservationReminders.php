<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Console\Command;

class SendReservationReminders extends Command
{
    protected $signature   = 'reservations:send-reminders';
    protected $description = 'Send 20 minute warning to users whose budget is almost up';

    public function handle(): void
    {
        $activeReservations = Reservation::where('status', 'Active')
            ->whereNull('reminder_sent_at')
            ->with('spot.location')
            ->get();

        foreach ($activeReservations as $reservation) {
            $minutesUsed      = $reservation->actual_start_at->diffInMinutes(now());
            $minutesRemaining = $reservation->max_minutes - $minutesUsed;

            if ($minutesRemaining <= 20 && $minutesRemaining > 0) {
                Notification::notify(
                    $reservation->user_id,
                    'Time Running Out',
                    "You have approximately {$minutesRemaining} minutes left in your parking session. Please extend or prepare to leave.",
                    'reminder',
                    $reservation->id
                );

                $reservation->update(['reminder_sent_at' => now()]);
            }
        }
    }
}