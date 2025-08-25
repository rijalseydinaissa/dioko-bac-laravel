<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTypes = [
            [
                'name' => 'Électricité',
                'slug' => 'electricity',
                'description' => 'Factures d\'électricité',
                'icon' => 'electricity',
            ],
            [
                'name' => 'Internet',
                'slug' => 'internet',
                'description' => 'Factures internet et télécommunications',
                'icon' => 'wifi',
            ],
            [
                'name' => 'Eau',
                'slug' => 'water',
                'description' => 'Factures d\'eau',
                'icon' => 'water',
            ],
            [
                'name' => 'Loyer',
                'slug' => 'rent',
                'description' => 'Paiements de loyer',
                'icon' => 'home',
            ],
            [
                'name' => 'Assurance',
                'slug' => 'insurance',
                'description' => 'Primes d\'assurance',
                'icon' => 'shield',
            ],
            [
                'name' => 'Téléphone',
                'slug' => 'phone',
                'description' => 'Factures de téléphone mobile',
                'icon' => 'phone',
            ],
            [
                'name' => 'Gaz',
                'slug' => 'gas',
                'description' => 'Factures de gaz',
                'icon' => 'gas',
            ],
            [
                'name' => 'Services divers',
                'slug' => 'other',
                'description' => 'Autres services et factures',
                'icon' => 'other',
            ],
        ];

        foreach ($paymentTypes as $type) {
            PaymentType::create($type);
        }
    }
}