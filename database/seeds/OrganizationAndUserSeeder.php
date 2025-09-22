<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organization;

class OrganizationAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create the 'Milma' organization
        $milmaOrg = Organization::firstOrCreate([
            'name' => 'Milma'
        ]);

        // Create the 'sajaras' user and link to the Milma organization
        User::firstOrCreate([
            'organization_id' => $milmaOrg->id,
            'name' => 'sajaras',
            'phone_number' => '8848757578',
            'password' => Hash::make('password'), // Default password is 'password'
            'is_platform_admin' => false, // Optional: make this first user an admin
        ]);
    }
}