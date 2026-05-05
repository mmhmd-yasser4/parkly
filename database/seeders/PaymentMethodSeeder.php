<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'mohamed1@gmail.com')->first();

        PaymentMethod::insert([
            [
                'user_id'    => $user->id,
                'token'      => 'fake_token_visa_' . uniqid(),
                'card_brand' => 'Visa',
                'last_four'  => '1234',
                'expiry'     => '12/26',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => $user->id,
                'token'      => 'fake_token_mc_' . uniqid(),
                'card_brand' => 'Mastercard',
                'last_four'  => '5678',
                'expiry'     => '08/27',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}