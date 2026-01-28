<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        Client::create(
            [
                "companyID" => 16,
                "clientCode" => 466822,
                "sessionKey" => "duslkdjflsakdjf",
                "username" => "support@retailcare.com.au",
                "password" => "RCare123@#$",
            ]
        );

        Client::create(
            [
                "companyID" => 16,
                "clientCode" => 603303,
                "sessionKey" => "duslkdjflsakdjf",
                "username" => "support@retailcare.com.au",
                "password" => "RCare123@#$",
            ]
        );

    }
}
