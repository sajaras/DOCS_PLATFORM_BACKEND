<?php

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class AccessControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $organizations = Organization::orderBy('id')->get();
        $guardName = 'sanctum';
         Auth::shouldUse('sanctum');

        // Define entities for which standard CRUD permissions will be created
        $entities = [
            'master' =>
            [
                'organization',
                'unit',
                'store',
                'party',
                'party-type',
                'party-type-category',
                'address',
                'item-main-group',
                'item-sub-group',
                'item',
                'measurement-unit',
                'service-main-group',
                'service-sub-group',
                'service',
                'vehicle-type',
                'vehicle',
                'section',
                'sub-section',
                'make',
                'mode-of-transportation',
                'charge-deduction-head',
                'invoice-type',
                'user',
                'role',
                'permission',
                'finance-account-maingroup',
                'finance-account-subgroup',
                'finance-account-account-code',
                'entity-finance-account-code-setting',
                'default-finance-account-code-setting',
                'organization-timebased-setting',
                'party-timebased-setting',
                'unit-timebased-setting',
                'party-party-type-category-timebased-setting',
                'country',
                'state',
                'district'
            ],
            'data' => [

                'tender',
                'purchase-order',
                'invoice',
                'buyer-indent',
                'finance-journal',
            ]

        ];


        $actions = ['view', 'list', 'create', 'edit', 'delete'];
        foreach ($organizations  as $eachOrganization) {

            $organizationUsers = $eachOrganization->users()->wherePivot('is_super_user', true)->get();
          
            
            foreach ($entities as $entityType => $entityTypeData) {

                foreach ($entityTypeData as $eachEntity) {
                    $permissionGroup =  PermissionGroup::firstorCreate(['name' => $eachEntity, 'group_type' => $entityType, 'organization_id' => $eachOrganization->id]);
                    foreach ($actions as $action) {
                        // CORRECTED LOGIC for permission name generation
                        if ($action === 'list') {
                            // Use Laravel's Str::plural to handle pluralization correctly (e.g., 'party' -> 'parties', 'party-type' -> 'party-types')
                            // And keep the hyphens.
                            $permissionName = 'list-' . \Illuminate\Support\Str::plural($eachEntity);
                        } else {
                            $permissionName = $action . '-' . $eachEntity;
                        }
                        Permission::firstOrCreate(['name' =>  $permissionName, 'guard_name' => $guardName, 'permission_group_id' => $permissionGroup->id,'organization_id'=>$eachOrganization->id]);
                    }
                }
            }
            // Create Roles
            // $risAdminRole = Role::firstOrCreate(['name' => 'ris-admin', 'guard_name' => $guardName]);
            // $storeManagerRole = Role::firstOrCreate(['name' => 'store-manager', 'guard_name' => $guardName]);
            // $salesUserRole = Role::firstOrCreate(['name' => 'sales-user', 'guard_name' => $guardName]);
            $orgSuperUserRole = Role::firstOrCreate(['name' => 'organization-super-user', 'guard_name' => $guardName,'organization_id'=>$eachOrganization->id]);
            
            $orgSuperUserPermissionNames = Permission::where('guard_name', $guardName)
            // ->whereNotIn('name', ['view-permission', 'list-permissions']) // Exclude system-level permission management
            ->where('organization_id',$eachOrganization->id)
            ->pluck('name')
            ->toArray();
           
            $orgSuperUserRole->syncPermissions($orgSuperUserPermissionNames);
            foreach($organizationUsers as $eachUser)
            {
                $eachUser->assignRole($orgSuperUserRole);

            }
       

        }
    }
}
