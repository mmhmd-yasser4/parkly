<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingLocation;
use Illuminate\Http\Request;

class ParkingSpotController extends Controller
{
    public function index(Request $request, $locationId)
    {
        $location = ParkingLocation::findOrFail($locationId);

        $query = $location->spots();

        $status = $request->query('status');
        $allowedStatuses = ['Available', 'Occupied', 'Maintenance'];

        if ($status && in_array($status, $allowedStatuses)) {
            $query->where('status', $status);
        }

        return response()->json($query->get());
    }
}