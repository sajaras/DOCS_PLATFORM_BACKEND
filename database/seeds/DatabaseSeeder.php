<?php

use App\Models\User;
// use Database\seeds\OrganizationSetupSeeder;
// use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        
        // $this->call(OrganizationAndUserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(AssignSajarasAdminRoleSeeder::class);
        
    }
}
