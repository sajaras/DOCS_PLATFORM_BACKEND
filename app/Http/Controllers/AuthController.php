<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\ClubService;
use App\Traits\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\User;
use App\Services\UserService;


class AuthController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;
    protected $clubService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $token = $this->authService->login($credentials);



        if ($token == null) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_name' => 'required|string|max:255',
            'manager_contact' => 'required|string|max:20',
            'manager_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $club = $this->clubService->createClub(
            $request->only(['name', 'location', 'description']),
            $request->only(['manager_name', 'manager_contact', 'manager_password']) + ['membership_type' => 'player']
        );

        return response()->json($club, 201);
    }

    public function getAuthUser()
    {
        $authuser = User::where('id', Auth::id())->first();
        $authuser->load(['organizations', 'personalInformation','unreadNotifications']);
        $authuser->append('current_organization');
        return response()->json($authuser, 200);
    }

    public function getAuthUserPersonalInformations(Request $request)
    {
        $personalInformation = Auth::user()->personalInformation;
        return response()->json($personalInformation, 200);
    }
    public function getAuthUserNotificationSettings(Request $request)
    {
        $notificationSettings = Auth::user()->notificationSettings;
        return response()->json($notificationSettings, 200);
    }

    public function updateProfileSettings(Request $request)
    {
        
        $user =  DB::transaction(function () use ($request) {
            $user = Auth::user();
            $userService = new UserService;
            $userService->updateProfileSettings($user, $request);
        });

        return response()->json($user, 200);
    }

    public function updateNotificationSettings(Request $request)
    {
    
        $user = DB::transaction(function () use ($request) {
            $user = Auth::user();
           $userService = new UserService;
             $userService->updateNotificationSettings($user, $request);
          
            return $user->notificationSettings;
        });

        return response()->json($user, 200);
    }

    public function updateProfilePicture(Request $request)
    {
        
        // if($request->hasFile('profile_pic'))
        // {
        //     return ['yes'];
        // }
        $request->validate([
            'profile_pic' => 'required|file|mimes:jpeg,jpg,png|max:2048',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = Auth::user();
          
            $userService = new UserService;
            $userService->update($request, $user);
            return $user;
        });
        

        return response()->json(['message' => 'Profile picture updated successfully', 'profile_pic_path' => $user->profile_pic_path], 200);
    }   

    public function deleteProfilePicture(Request $request)
    {
        $user = Auth::user();

        if (!$user->profile_pic_path) {
            return response()->json(['message' => 'No profile picture to delete.'], 404);
        }

        DB::transaction(function () use ($user) {
            $userService = new UserService;
            $userService->deleteProfilePicture($user);
        });

        return response()->json(['message' => 'Profile picture deleted successfully.'], 200);
    }
        
}
