<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ParkingSpot;
use App\Models\Reservation;
use App\Services\PayMobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    protected PayMobService $paymob;

    public function __construct(PayMobService $paymob)
    {
        $this->paymob = $paymob;
    }

    // ─── 1. Create reservation ────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'spot_id'    => 'required|integer|exists:parking_spots,id',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'budget'     => 'required|numeric|min:1',
        ]);

        $user = $request->user();

        // Must have a saved payment method
        $hasCard = $user->paymentMethods()->whereNull('deleted_at')->exists();
        if (!$hasCard) {
            return response()->json([
                'message' => 'You must add a payment method before making a reservation.',
            ], 422);
        }

        // Vehicle check (optional — delete these lines later if not needed)
        if ($request->vehicle_id) {
            $vehicle = $user->vehicles()->whereNull('deleted_at')->find($request->vehicle_id);
            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehicle not found or does not belong to you.',
                ], 403);
            }
        }

        return DB::transaction(function () use ($request, $user) {

            // Lock the spot so no other request can grab it at the same time
            $spot = ParkingSpot::lockForUpdate()->findOrFail($request->spot_id);

            if ($spot->status !== 'Available') {
                return response()->json([
                    'message' => 'This spot is no longer available.',
                ], 422);
            }

            // Calculate max minutes from budget and price per hour
            $pricePerHour = $spot->location->price_per_hour;
            $maxMinutes   = (int) floor(($request->budget / $pricePerHour) * 60);

            // Create the reservation
            $reservation = Reservation::create([
                'user_id'     => $user->id,
                'spot_id'     => $spot->id,
                'vehicle_id'  => $request->vehicle_id ?? null,
                'start_at'    => now(),
                'budget'      => $request->budget,
                'max_minutes' => $maxMinutes,
                'total_price' => 0,
                'status'      => 'Pending',
            ]);

            // Flip spot to Occupied
            $spot->update([
                'status'        => 'Occupied',
                'status_source' => 'reservation',
            ]);

            // Fire notification
            Notification::notify(
                $user->id,
                'Reservation Created',
                "Your reservation for spot {$spot->spot_number} has been created. You have up to {$maxMinutes} minutes.",
                'booking',
                $reservation->id
            );

            return response()->json([
                'message'     => 'Reservation created successfully.',
                'reservation' => $reservation,
                'max_minutes' => $maxMinutes,
            ], 201);
        });
    }

    // ─── 2. Check in ─────────────────────────────────────────────────────────
    public function checkIn(Request $request, $id)
    {
        $user        = $request->user();
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($reservation->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending reservations can be checked in.',
            ], 422);
        }

        $reservation->update([
            'actual_start_at' => now(),
            'status'          => 'Active',
        ]);

        Notification::notify(
            $user->id,
            'Checked In',
            "You have checked in to spot {$reservation->spot->spot_number}. Your session has started.",
            'booking',
            $reservation->id
        );

        return response()->json([
            'message'     => 'Checked in successfully.',
            'reservation' => $reservation->fresh(),
        ]);
    }

    // ─── 3. Extend reservation ────────────────────────────────────────────────
    public function extend(Request $request, $id)
    {
        $request->validate([
            'extra_budget' => 'required|numeric|min:1',
        ]);

        $user        = $request->user();
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($reservation->status !== 'Active') {
            return response()->json([
                'message' => 'Only active reservations can be extended.',
            ], 422);
        }

        $pricePerHour   = $reservation->spot->location->price_per_hour;
        $extraMinutes   = (int) floor(($request->extra_budget / $pricePerHour) * 60);

        $reservation->update([
            'budget'      => $reservation->budget + $request->extra_budget,
            'max_minutes' => $reservation->max_minutes + $extraMinutes,
        ]);

        Notification::notify(
            $user->id,
            'Reservation Extended',
            "Your reservation has been extended by {$extraMinutes} minutes.",
            'booking',
            $reservation->id
        );

        return response()->json([
            'message'     => 'Reservation extended successfully.',
            'reservation' => $reservation->fresh(),
        ]);
    }

    // ─── 4. End reservation ───────────────────────────────────────────────────
    public function end(Request $request, $id)
    {
        $user        = $request->user();
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($reservation->status !== 'Active') {
            return response()->json([
                'message' => 'Only active reservations can be ended.',
            ], 422);
        }

        return DB::transaction(function () use ($user, $reservation) {

            $exitTime     = now();
            $pricePerHour = $reservation->spot->location->price_per_hour;

            // Calculate actual time spent in hours
            $hoursSpent   = $reservation->actual_start_at->diffInMinutes($exitTime) / 60;
            $actualCharge = round($hoursSpent * $pricePerHour, 2);

            $overstayCharge = 0;

            // Check if user overstayed their budget
            if ($actualCharge > $reservation->budget) {
                $overstayCharge = round($actualCharge - $reservation->budget, 2);
            }

            // Get user's default payment method
            $paymentMethod = $user->paymentMethods()
                ->whereNull('deleted_at')
                ->where('is_default', true)
                ->first();

            // Create the payment record
            $payment = Payment::create([
                'reservation_id'   => $reservation->id,
                'payment_method_id'=> $paymentMethod?->id,
                'amount'           => $actualCharge,
                'payment_method'   => 'card',
                'payment_status'   => 'Success',
                'transaction_ref'  => 'TXN-' . strtoupper(Str::random(12)),
                'paid_at'          => now(),
            ]);

            // Update reservation
            $reservation->update([
                'actual_exit_at' => $exitTime,
                'total_price'    => $actualCharge,
                'status'         => 'Completed',
            ]);

            // Flip spot back to Available
            $reservation->spot->update([
                'status'        => 'Available',
                'status_source' => 'reservation',
            ]);

            // Notification message
            $notifMessage = "Your session has ended. You were charged {$actualCharge} EGP.";
            if ($overstayCharge > 0) {
                $notifMessage .= " This includes an overstay charge of {$overstayCharge} EGP.";
            }

            Notification::notify(
                $user->id,
                'Session Ended',
                $notifMessage,
                'payment',
                $reservation->id
            );

            // If overstay — fire a separate notification
            if ($overstayCharge > 0) {
                Notification::notify(
                    $user->id,
                    'Overstay Charge',
                    "An overstay charge of {$overstayCharge} EGP has been deducted from your saved card.",
                    'payment',
                    $reservation->id
                );
            }

            return response()->json([
                'message'         => 'Reservation ended successfully.',
                'actual_charge'   => $actualCharge,
                'overstay_charge' => $overstayCharge,
                'payment'         => $payment,
                'reservation'     => $reservation->fresh(),
            ]);
        });
    }

    // ─── 5. Cancel reservation ────────────────────────────────────────────────
    public function cancel(Request $request, $id)
    {
        $user        = $request->user();
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($reservation->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending reservations can be cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($reservation) {
            $reservation->spot->update([
                'status'        => 'Available',
                'status_source' => 'reservation',
            ]);

            $reservation->update(['status' => 'Cancelled']);
        });

        Notification::notify(
            $user->id,
            'Reservation Cancelled',
            "Your reservation for spot {$reservation->spot->spot_number} has been cancelled.",
            'booking',
            $reservation->id
        );

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
        ]);
    }

    // ─── 6. List user reservations ────────────────────────────────────────────
    public function index(Request $request)
    {
        $reservations = $request->user()
            ->reservations()
            ->with(['spot.location', 'vehicle', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reservations);
    }

    // ─── 7. Single reservation ────────────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $reservation = Reservation::with(['spot.location', 'vehicle', 'payment'])
            ->findOrFail($id);

        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($reservation);
    }
}