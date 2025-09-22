<?php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Organization;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Get the specific organization
        $organization = Organization::where('name', 'Milma')->first();

        // Exit if the organization doesn't exist
        if (!$organization) {
            $this->command->info('Organization "Milma" not found. Skipping PermissionSeeder.');
            return;
        }

        // Define permissions
        $permissions = [
            'list-documents',
            'view-document',
            'create-document',
            'edit-document',
            'delete-document',

            'view-tag',
            'list-tags',
            'create-tag',
            'edit-tag',
            'delete-tag',

            'list-users',
            'view-user',
            'create-user',
            'edit-user',
            'delete-user',

             'list-roles',
            'view-role',
            'create-role',
            'edit-role',
            'delete-role',

              'list-permissions',
            'view-permission',
            'create-permission',
            'edit-permission',
            'delete-permission',
        ];

        // Create permissions and scope them to the organization
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'organization_id' => $organization->id,
                'guard_name' => 'web' // or 'api' depending on your setup
            ]);
        }

        $this->command->info('Permissions created for Milma organization.');

        // Create the OrganizationAdmin role if it doesn't exist for this organization
        $role = Role::firstOrCreate([
            'name' => 'OrganizationAdmin',
            'organization_id' => $organization->id,
            'guard_name' => 'web'
        ]);

        // Assign all permissions to the role
        $role->givePermissionTo(Permission::where('organization_id', $organization->id)->get());
        
        $this->command->info('OrganizationAdmin role created and permissions assigned for Milma.');
    }
}