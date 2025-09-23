<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Traits\FunctionsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use FunctionsTrait;
    protected $service;
    protected $is_ris_admin = false;

    public function __construct(Request $request)
    {
        $this->service = new UserService($request);
    }

    public function index(Request $request)
    {
        $this->service->setActivity('listRecords')->checkIfPermissionGranted();
        return response()->json(['users' => $this->service->getAll()]);
    }

    public function store(Request $request)
    {
        // if($request->hasFile('profile_pic'))
        // {
        //     return ['yes'];
        // }
        // else
        // {
        //     return ['no'];
        // }
        // return $request;
        // Log::info('--- ROLE STORE METHOD WAS REACHED ---',$request->all());
        //    dd("sss");
        $this->service->setActivity('createRecord')->checkIfPermissionGranted();
        $this->service->validateRequest();
        $user = DB::transaction(function () use ($request) {

            $user =  $this->service->create($request, $this->is_ris_admin);

            $this->service->SyncRolesToUser($user, $request->role_ids);
            $this->service->SyncPermissionsToUser($user, $request->permission_ids);
            return $user;
        });
        return response()->json(['user' => $user->load(['roles'])], 201);
    }

    public function show(User $user)
    {

        $this->service->setActivity('viewRecord')->checkIfPermissionGranted();
        $loadedUser = $this->service->getById($user->id, null);
        $loadedUser->append(['role_ids'])->makeHidden('roles');
        if (!$loadedUser) return response()->json(['message' => 'User not found.'], 404);
        return response()->json(['user' => $loadedUser]);
    }

    public function update(Request $request, User $user)
    {
        $this->service->setActivity('updateRecord')->checkIfPermissionGranted();
        $this->service->validateRequest($user);
        $updatedUser = DB::transaction(function () use ($request, $user) {
            $this->service->update($request, $user);

            $this->service->SyncRolesToUser($user, $request->role_ids);


            $this->service->SyncPermissionsToUser($user, $request->permission_ids);
            return $user;
        });
        return response()->json(['user' => $updatedUser->load(['roles', 'permissions'])]);
    }

    public function destroy(User $user)
    {
        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->delete($user));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting user: " . $e->getMessage());
            return response()->json(['message' => "Could not delete user."], 500);
        }
    }

    public function deleteMultiple(Request $request)
    {

        $this->service->setActivity('deleteRecord')->checkIfPermissionGranted();
        try {
            DB::transaction(fn() => $this->service->deleteMultiple($request->ids));
            return response()->json(null, 204);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error("Error deleting district: " . $e->getMessage());
            return response()->json(['message' => "Could not delete address."], 500);
        }
    }

    // ADD THIS NEW METHOD
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $this->service->validateProfileUpdateRequest($request, $user);

        $updatedUser = DB::transaction(function () use ($request, $user) {
            return $this->service->updateProfile($request, $user);
        });

        return response()->json(['user' => $updatedUser, 'message' => 'Profile updated successfully.']);
    }
}
