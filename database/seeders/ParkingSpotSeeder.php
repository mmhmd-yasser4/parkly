<?php

namespace Database\Seeders;

use App\Models\ParkingLocation;
use App\Models\ParkingSpot;
use Illuminate\Database\Seeder;

class ParkingSpotSeeder extends Seeder
{
    public function run(): void
    {
        $locations = ParkingLocation::all();

        foreach ($locations as $location) {
            $spots = [];

            // Row A — all available
            for ($i = 1; $i <= 5; $i++) {
                $spots[] = [
                    'location_id'   => $location->id,
                    'spot_number'   => 'A-0' . $i,
                    'status'        => 'Available',
                    'status_source' => 'admin',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Row B — mix of occupied and available
            for ($i = 1; $i <= 5; $i++) {
                $spots[] = [
                    'location_id'   => $location->id,
                    'spot_number'   => 'B-0' . $i,
                    'status'        => $i <= 2 ? 'Occupied' : 'Available',
                    'status_source' => $i <= 2 ? 'reservation' : 'admin',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            // Row C — one maintenance spot
            for ($i = 1; $i <= 3; $i++) {
                $spots[] = [
                    'location_id'   => $location->id,
                    'spot_number'   => 'C-0' . $i,
                    'status'        => $i === 1 ? 'Maintenance' : 'Available',
                    'status_source' => 'admin',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            ParkingSpot::insert($spots);
        }
    }
}