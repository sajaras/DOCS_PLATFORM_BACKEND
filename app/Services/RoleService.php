<?php

namespace App\Services;

use App\Models\Role;
use App\Models\State;
use App\Scopes\OrganizationScope;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleService
{
    use FunctionsTrait;

    protected $request;
    protected $activity;
    protected $permissionCheckArray;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->activity = null;
        // Permissions for managing this global master data
        $this->permissionCheckArray = [
            'viewRecord'   => ['view-role'],
            'listRecords'  => ['list-roles'],
            'createRecord' => ['create-role'],
            'updateRecord' => ['edit-role'],
            'deleteRecord' => ['delete-role'],
        ];
    }

    public function setActivity(string $activity): self
    {
        $this->activity = $activity;
        return $this;
    }

    public function checkIfPermissionGranted(): void
    {
        if ($this->activity && isset($this->permissionCheckArray[$this->activity])) {
            $this->checkForPermission($this->permissionCheckArray[$this->activity]);
        } else {
            throw new \Exception("Activity or permission not set for RoleService.");
        }
    }

    public function validateRequest(Role $role = null): void
    {
        if (!$this->request) {
            throw new \Exception("Request object not set.");
        }

        $stateId = $this->request->input('state_id');

        $rules = [
            'name' => 'required|string|max:255',
            'permission_ids' => 'present|array', // Ensures 'permissions' key exists, even if empty
            'permission_ids.*' => [ // Validate each item in the permissions array
                'required',
                'integer',
                Rule::exists('permissions', 'id'), // Ensure each permission ID is valid and exists
            ],
        ];
        if($this->activity == 'createRecord')
        {
            $rules['name'] = 'required|string|max:255|unique:roles,name';
        }

        $this->request->validate($rules);
    }

    public function getAll(array $with = [],$sortColumns= [])
    {
       
        $defaultWith = ['permissions'];
        $relationsToLoad = array_unique(array_merge($defaultWith, $with));
        $query = Role::with($relationsToLoad);

        $authUser = Auth::user();

    // Logic to determine the organization_id
    if ($authUser && $authUser->is_ris_admin) {
        
         $query->withoutGlobalScope(OrganizationScope::class);
         if($this->request->filled('organization_id'))
         {
             $query->where('organization_id',$this->request->organization_id);

         }
    }

        if ($this->request && $this->request->filled('search_term')) {
            $query->where(function($q){
                $q->where('name','ilike','%'.$this->request->search_term.'%');
               
            });
        }

     

        // Check for pagination request
        if ($this->request && $this->request->has('per_page')) {
            $perPage = (int) $this->request->input('per_page', 15);
            return $query->paginate($perPage);
        }

        if(count($sortColumns))
        {
            foreach ($sortColumns as $sort) {
                $query->orderBy($sort['column'], $sort['direction']);
            }
        }
        
        return $query->get();

    }

    public function getById(int $id,?array $with = null)
    {
        $query = Role::query();
         if ($with !== null) {
             $defaultWith = ['permissions'];
             $relationsToLoad = array_unique(array_merge($defaultWith, $with));
             if (!empty($relationsToLoad)) {
                $query->with($relationsToLoad);
            }

         }

        return $query->find($id);
    }

    public function create($requestData)
    {
      
         // Get only the data that is safe for a user to provide
    $data = $requestData->only(['name','organization_id']);
    $authUser = Auth::user();

    // Logic to determine the organization_id
    if ($authUser && $authUser->is_ris_admin && $requestData->has('organization_id')) {
        // If the user is an admin, they MUST provide the organization_id in the request
        $data['organization_id'] = $requestData->input('organization_id');
    } elseif ($authUser && $authUser->currentOrganization) {
        // For a regular user, we get the organization_id from their authenticated context
        $data['organization_id'] = $authUser->currentOrganization->id;
    } else {
        // If we can't determine an organization, we can't proceed.
        throw new HttpException(403, 'Cannot determine the organization for this action.');
    }
    //  dd("i reacheed here");
    Log::info($data);
    // dd("stop");
    // Now, create the entity with the correctly assigned organization_id
    return Role::create($data);
    }

    public function update(Request $requestData, Role $role)
    {
        $data = $requestData->only(['name']);
        $role->update($data);
        return $role;
    }

    public function delete(Role $role): bool
    {
        $role->SyncPermissions([]);
        return $role->delete();
    }

    public function SyncPermissionToRole($role,$permissionIds)
    {
        $role->syncPermissions($permissionIds);

    }

     public function deleteMultiple($ids)
    {

        foreach ($ids as $eachid) {
            $this->delete(Role::find($eachid));
        }
    }
}
