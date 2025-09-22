<?php


use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignSajarasAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the user by their phone number
        $user = User::where('phone_number', '8848757578')->first();

        if (!$user) {
            $this->command->error('User with phone number 8848757578 not found.');
            return;
        }

        // Ensure the user belongs to an organization
        if (!$user->organization_id) {
            $this->command->error('User sajaras is not assigned to an organization.');
            return;
        }
        
        // Find the OrganizationAdmin role within that user's organization
        $role = Role::where('name', 'OrganizationAdmin')
                    ->where('organization_id', $user->organization_id)
                    ->first();

        if (!$role) {
            $this->command->error('Role "OrganizationAdmin" not found for the user\'s organization.');
            return;
        }

        // Assign the role to the user
        $user->assignRole($role);

        $this->command->info('Assigned OrganizationAdmin role to the user sajaras.');
    }
}