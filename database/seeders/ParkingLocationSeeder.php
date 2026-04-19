<?php

namespace Database\Seeders;

use App\Models\ParkingLocation;
use Illuminate\Database\Seeder;

class ParkingLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'admin_id'       => 1,
                'name'           => 'Maadi Grand Parking',
                'address'        => '15 Road 9, Maadi',
                'area'           => 'Maadi',
                'latitude'       => 29.9602,
                'longitude'      => 31.2569,
                'price_per_hour' => 20.00,
                'is_active'      => true,
            ],
            [
                'admin_id'       => 1,
                'name'           => 'Zamalek Central Park',
                'address'        => '22 Hassan Sabry St, Zamalek',
                'area'           => 'Zamalek',
                'latitude'       => 30.0626,
                'longitude'      => 31.2197,
                'price_per_hour' => 25.00,
                'is_active'      => true,
            ],
            [
                'admin_id'       => 1,
                'name'           => 'New Cairo City Stars Lot',
                'address'        => 'City Stars Mall, New Cairo',
                'area'           => 'New Cairo',
                'latitude'       => 30.0731,
                'longitude'      => 31.4052,
                'price_per_hour' => 15.00,
                'is_active'      => true,
            ],
            [
                'admin_id'       => 1,
                'name'           => 'Heliopolis Parking Hub',
                'address'        => '5 Merghany St, Heliopolis',
                'area'           => 'Heliopolis',
                'latitude'       => 30.0876,
                'longitude'      => 31.3219,
                'price_per_hour' => 18.00,
                'is_active'      => true,
            ],
            [
                'admin_id'       => 1,
                'name'           => 'Downtown Cairo Lot',
                'address'        => 'Tahrir Square Area, Downtown',
                'area'           => 'Downtown',
                'latitude'       => 30.0444,
                'longitude'      => 31.2357,
                'price_per_hour' => 30.00,
                'is_active'      => true,
            ],
            [
                'admin_id'       => 1,
                'name'           => 'Dokki Smart Park',
                'address'        => '10 Tahrir St, Dokki',
                'area'           => 'Dokki',
                'latitude'       => 30.0380,
                'longitude'      => 31.2106,
                'price_per_hour' => 22.00,
                'is_active'      => true,
            ],
        ];

        foreach ($locations as $location) {
            ParkingLocation::create($location);
        }
    }
}