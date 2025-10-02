<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Category;
use App\Models\Address;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a client category
        $clientCategory = Category::firstOrCreate([
            'name' => 'Individual',
            'type' => 'client'
        ]);

        // Get some countries, states and cities
        $egypt = Country::firstOrCreate(['name' => 'Egypt']);
        $cairo = State::firstOrCreate(['name' => 'Cairo', 'country_id' => $egypt->id]);
        $giza = City::firstOrCreate(['name' => 'Giza', 'state_id' => $cairo->id]);

        $clients = [
            [
                'name' => 'Mohamed Ali',
                'email' => 'mohamed.ali@example.com',
                'mobile' => '01001234567',
                'gender' => 'male',
                'company' => 'Ali Enterprises',
                'category_id' => $clientCategory->id,
                'notes' => 'Regular client with multiple cases',
                'address' => [
                    'country_id' => $egypt->id,
                    'state_id' => $cairo->id,
                    'city_id' => $giza->id,
                    'address' => '123 Nile Street, Giza'
                ]
            ],
            [
                'name' => 'Fatma Hassan',
                'email' => 'fatma.hassan@example.com',
                'mobile' => '01007654321',
                'gender' => 'female',
                'company' => 'Hassan & Co.',
                'category_id' => $clientCategory->id,
                'notes' => 'Corporate client with ongoing contracts',
                'address' => [
                    'country_id' => $egypt->id,
                    'state_id' => $cairo->id,
                    'city_id' => $giza->id,
                    'address' => '456 Pyramid Road, Giza'
                ]
            ],
            [
                'name' => 'Ahmed Mahmoud',
                'email' => 'ahmed.mahmoud@example.com',
                'mobile' => '01009876543',
                'gender' => 'male',
                'company' => null,
                'category_id' => $clientCategory->id,
                'notes' => 'Individual client with personal cases',
                'address' => [
                    'country_id' => $egypt->id,
                    'state_id' => $cairo->id,
                    'city_id' => $giza->id,
                    'address' => '789 Sphinx Avenue, Giza'
                ]
            ]
        ];

        foreach ($clients as $clientData) {
            // Create address first
            $address = Address::create($clientData['address']);
            
            // Create client with address_id
            $client = Client::create(array_merge(
                array_diff_key($clientData, ['address' => '']),
                ['address_id' => $address->id]
            ));
        }
    }
}
