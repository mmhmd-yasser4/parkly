<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles()->whereNull('deleted_at')->get();

        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\d٠-٩\s\-]+[a-zA-Z\x{0600}-\x{06FF}\s]+$/u',
                Rule::unique('vehicles', 'plate_number')->whereNull('deleted_at'),
            ],
            'make_model' => 'required|string|max:100',
        ], [
            'plate_number.regex'  => 'Plate number must contain numbers followed by letters (Arabic or Latin).',
            'plate_number.unique' => 'This plate number is already registered.',
        ]);

        $user = $request->user();

        $isFirst = $user->vehicles()->whereNull('deleted_at')->count() === 0;

        $vehicle = $user->vehicles()->create([
            'plate_number' => $request->plate_number,
            'make_model'   => $request->make_model,
            'is_default'   => $isFirst,
        ]);

        return response()->json($vehicle, 201);
    }

    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $vehicle = Vehicle::whereNull('deleted_at')->findOrFail($id);

        if ($vehicle->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        DB::transaction(function () use ($user, $vehicle) {
            $user->vehicles()->whereNull('deleted_at')->update(['is_default' => false]);
            $vehicle->update(['is_default' => true]);
        });

        return response()->json($vehicle->fresh());
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $vehicle = Vehicle::whereNull('deleted_at')->findOrFail($id);

        if ($vehicle->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $wasDefault = $vehicle->is_default;

        $vehicle->delete();

        if ($wasDefault) {
            $next = $user->vehicles()->whereNull('deleted_at')->orderByDesc('created_at')->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Vehicle deleted']);
    }
}
