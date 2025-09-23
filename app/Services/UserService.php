<?php

namespace App\Services;

use App\Models\User;
use App\Models\State;
use App\Rules\ValidPhoneNumber;
use App\Scopes\OrganizationScope;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Rules\ValidTextString;
use Illuminate\Support\Facades\Storage;

class UserService
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
            'viewRecord'   => ['view-user'],
            'listRecords'  => ['list-users'],
            'createRecord' => ['create-user'],
            'updateRecord' => ['edit-user'],
            'deleteRecord' => ['delete-user'],
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
            throw new \Exception("Activity or permission not set for UserService.");
        }
    }

    public function validateRequest(User $user = null): void
    {
        if (!$this->request) {
            throw new \Exception("Request object not set.");
        }

        $stateId = $this->request->input('state_id');

        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => ['required', new ValidPhoneNumber],
            'profile_pic' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'role_ids' => 'present|array', // Ensures 'permissions' key exists, even if empty
            'role_ids.*' => [ // Validate each item in the permissions array
                'required',
                'integer',
                Rule::exists('roles', 'id'), // Ensure each permission ID is valid and exists
            ],
            'permission_ids' => 'nullable|array', // Ensures 'permissions' key exists, even if empty
            'permission_ids.*' => [ // Validate each item in the permissions array
                'required',
                'integer',
                Rule::exists('permissions', 'id'), // Ensure each permission ID is valid and exists
            ],
        ];
        if ($this->activity == 'createRecord') {
            $rules['password'] = 'required|string|confirmed|min:6';
        }

        $this->request->validate($rules);
    }

    public function getAll(array $with = [], $sortColumns = [])
    {

        $defaultWith = ['permissions'];
        $relationsToLoad = array_unique(array_merge($defaultWith, $with));
        $query = User::with($relationsToLoad);

        $authUser = Auth::user();

        // Logic to determine the organization_id
        if ($authUser && $authUser->is_ris_admin) {

            $query->withoutGlobalScope(OrganizationScope::class);
            if ($this->request->filled('organization_id')) {
                $query->where('organization_id', $this->request->organization_id);
            }
        }

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

    public function getById(int $id, ?array $with = null)
    {
        $query = User::query();

        // 1. Only load relations if the $with parameter is not null.
        // This means getById(1) or getById(1, null) will not load any relations.
        if ($with !== null) {
            // Define the relations you want to load by default when an array is passed.
            $defaultWith = ['roles', 'permissions'];
            
            // 2. Merge the provided relations with the default ones.
            // An empty array getById(1, []) will load the default relations.
            $relationsToLoad = array_unique(array_merge($defaultWith, $with));

            if (!empty($relationsToLoad)) {
                $query->with($relationsToLoad);
            }
        }

        // If $with was null, the query proceeds without any eager loading.
        return $query->find($id);
    }

    public function create($requestData, $is_ris_admin)
    {

        // Get only the data that is safe for a user to provide
        $data = $requestData->only(['name', 'phone_number', 'password']);
        if ($requestData->hasFile('profile_pic')) {
            $fileName = time() . '_' . $requestData->file('profile_pic')->getClientOriginalName();
            $data['profile_pic_path'] =  '/storage/'.($requestData->file('profile_pic')->storeAs('profile_pictures', $fileName, 'public'));
        }
        $data['password'] = Hash::make($requestData->password);
        $data['is_ris_admin'] = $is_ris_admin;

        $authUser = Auth::user();

        // Logic to determine the organization_id
        if ($authUser && $authUser->is_platform_admin && $requestData->has('organization_id')) {
            // If the user is an admin, they MUST provide the organization_id in the request
            $data['organization_id'] = $requestData->input('organization_id');
        } elseif ($authUser && $authUser->organization) {
            // For a regular user, we get the organization_id from their authenticated context
            $data['organization_id'] = $authUser->organization->id;
        } else {
            // If we can't determine an organization, we can't proceed.
            throw new HttpException(403, 'Cannot determine the organization for this action.');
        }
        //  dd("i reacheed here");
        Log::info($data);
        // dd("stop");
        // Now, create the entity with the correctly assigned organization_id
        return User::create($data);
    }

    public function update(Request $requestData, User $user,$is_ris_admin=null)
    {
        $data = $requestData->only(['name', 'phone_number']);
        if ($requestData->hasFile('profile_pic')) {
            $fileName = time() . '_' . $requestData->file('profile_pic')->getClientOriginalName();
            $data['profile_pic_path'] =  '/storage/'.($requestData->file('profile_pic')->storeAs('profile_pictures', $fileName, 'public'));
        }
        if($requestData->filled('password'))
        {
             $data['password'] = Hash::make($requestData->password);
        }

        if($is_ris_admin != null)
        {
             $data['is_ris_admin'] = $is_ris_admin;
        }

        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {

        return $user->delete();
    }

    public function SyncRolesToUser($user, $roleIds)
    {
        $user->syncRoles($roleIds);
    }
    public function SyncPermissionsToUser($user, $permissionIds)
    {
        $user->syncPermissions($permissionIds);
    }

      public function deleteMultiple($ids)
    {

        foreach ($ids as $eachid) {
            $this->delete(User::find($eachid));
        }
    }

    public function updateProfileSettings($user, $requestData)
    {
         
            $requestData->validate([
             'date_of_birth' => [
                'nullable',
                'date_format:Y-m-d',
                // The user must be at least 10 years old.
                // We calculate the date 10 years ago and ensure the DOB is on or before that date.
                'before_or_equal:' . now()->subYears(10)->format('Y-m-d')
            ],
            'about' => ['nullable', 'string', new ValidTextString],
            'permanent_address' => ['nullable', 'string', new ValidTextString],
            'current_address' => ['nullable', 'string', new ValidTextString],
            'email' => 'nullable|email|max:255',
            'phone_number' => ['nullable', new ValidPhoneNumber],
            'whatsapp_number' => ['nullable', new ValidPhoneNumber],
          
        ]);
        

        $data = $requestData->only([
            'date_of_birth',
            'about',
            'permanent_address',
            'current_address',
            'email',
            'phone_number',
            'whatsapp_number',
            'github_account_link',
            'height_in_cm',
            'weight_in_kg'
        ]);

        // Update or create the personal information record
        $user->personalInformation()->updateOrCreate(
            ['user_id' => $user->id], // Condition to find the record
            $data // Data to update or create
        );

        return $user->personalInformation;  // Return the updated personal information record       

    }

    public function updateNotificationSettings($user, $requestData)
    {
        $data = $requestData->only([
            'is_sms_notification_enabled',
            'is_mail_notification_enabled',
        ]);
        // return $data;
        if(!isset($data['is_sms_notification_enabled']))
        {
            $data['is_sms_notification_enabled'] = false;
        }
        if(!isset($data['is_mail_notification_enabled']))
        {
            $data['is_mail_notification_enabled'] = false;
            
        }

        // return $data;

        $user->notificationSettings()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $user->notificationSettings;
    }

    public function deleteProfilePicture(User $user)
    {
        if ($user->profile_pic_path) {
            // Delete the file from storage
            $path = str_replace('/storage/', 'public/', $user->profile_pic_path);
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
            // Clear the profile_pic_path in the database
            $user->profile_pic_path = null;
            $user->save();
        }
    }

      // ADD THESE NEW METHODS
    public function validateProfileUpdateRequest(Request $request, User $user): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'profile_pic' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|confirmed|min:6';
        }

        $request->validate($rules);
    }

    public function updateProfile(Request $requestData, User $user)
    {
        $data = $requestData->only(['name']);

        if ($requestData->hasFile('profile_pic')) {
            // Delete old picture if it exists
            $this->deleteProfilePicture($user);
            $fileName = time() . '_' . $requestData->file('profile_pic')->getClientOriginalName();
            $data['profile_pic_path'] = '/storage/' . ($requestData->file('profile_pic')->storeAs('profile_pictures', $fileName, 'public'));
        }

        if ($requestData->filled('password')) {
            $data['password'] = Hash::make($requestData->password);
        }

        $user->update($data);
        return $user;
    }
        
}
