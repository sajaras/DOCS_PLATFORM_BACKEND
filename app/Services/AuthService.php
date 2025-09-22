<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials)
    {
     
        if (!Auth::attempt($credentials)) {
            return null;
        }

        $user = Auth::user();
       
        $token = $user->createToken('auth_token')->plainTextToken; // Access the token string
        return $token;
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }

    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'password' => bcrypt($data['password']),
        ]);

        return $user->createToken('auth_token')->plainTextToken;
    }
}