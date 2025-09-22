<?php

namespace App\Services;


use App\Models\State;
use App\Scopes\OrganizationScope;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

use App\Models\PermissionGroup;
use App\Traits\HelperTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PermissionService
{
    use FunctionsTrait,HelperTrait;

    protected $request;
    protected $activity;
    protected $permissionCheckArray;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->activity = null;
        // Permissions for managing this global master data
        $this->permissionCheckArray = [
            'viewRecord'   => ['view-permission'],
            'listRecords'  => ['list-permissions'],
            'createRecord' => ['create-permission'],
            'updateRecord' => ['edit-permission'],
            'deleteRecord' => ['delete-permission'],
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
            throw new \Exception("Activity or permission not set for PermissionService.");
        }
    }

    public function validateRequest(Permission $permission = null): void
    {
        if (!$this->request) {
            throw new \Exception("Request object not set.");
        }

        $stateId = $this->request->input('state_id');

        $rules = [

            'name' => 'required',

        ];

        $this->request->validate($rules);
    }

    public function getAll(array $with = [], $sortColumns = [])
    {



        $defaultWith = ['roles', 'users'];
        $relationsToLoad = array_unique(array_merge($defaultWith, $with));
        $query = Permission::with($relationsToLoad);

        $authUser = Auth::user();

        if ($this->request && $this->request->filled('search_term')) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->request->search_term . '%');
            });
        }
        // Check for pagination request
        if ($this->request && $this->request->has('per_page')) {
            $perPage = (int) $this->request->input('per_page', 15);
            return $query->paginate($perPage);
        }

        if (count($sortColumns)) {
            foreach ($sortColumns as $sort) {
                $query->orderBy($sort['column'], $sort['direction']);
            }
        }

        return $query->get();
    }

    public function getById(int $id, array $with = [])
    {
        $defaultWith = ['roles'];
        $relationsToLoad = array_unique(array_merge($defaultWith, $with));
        return Permission::with($relationsToLoad)->find($id);
    }

    public function create($requestData)
    {

        // Get only the data that is safe for a user to provide
        $data = $requestData->all();
        $authUser = Auth::user();
        return Permission::create($data);
    }

    public function update(Request $requestData, Permission $permission)
    {
        $data = $requestData->all();
        $permission->update($data);
        return $permission;
    }

    public function delete(Permission $permission): bool
    {

        return $permission->delete();
    }

    public function deleteMultiple($ids)
    {

        foreach ($ids as $eachid) {
            $this->delete(Permission::find($eachid));
        }
    }

   
}
