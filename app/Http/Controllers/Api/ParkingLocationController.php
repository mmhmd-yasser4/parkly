<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;
use Illuminate\Http\Request;

class ParkingLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = ParkingLocation::where('is_active', true);

        $lat    = $request->query('lat');
        $lng    = $request->query('lng');
        $radius = $request->query('radius'); // in km

        if ($lat && $lng && $radius) {
            $query->selectRaw("
                *,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }

        $locations = $query->get();

        return response()->json($locations);
    }

    public function show($id)
    {
        $location = ParkingLocation::where('is_active', true)->findOrFail($id);

        $location->available_spots_count = $location->spots()
            ->where('status', 'Available')
            ->count();

        return response()->json($location);
    }
}