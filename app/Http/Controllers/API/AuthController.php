<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'daily_posts_limit' => 10,
        ]);

        $token = $user->createToken('token')->plainTextToken;

        return $this->respondWithToken($token, $user);
    }


    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            throw new AuthenticationException('Invalid login credentials');
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log($user->id, 'user_login', 'User logged in successfully');

        return $this->respondWithToken($token, $user);
    }


    public function logout(Request $request)
    {
        ActivityLog::log($request->user()->id, 'user_logout', 'User logged out successfully');

        $request->user()->tokens()->delete();

        return process(true);
    }

    public function refresh()
    {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->respondWithToken($token, $user);
    }

    protected function respondWithToken(string $token, $user = null, $status = 200)
    {
        $response = [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

        if ($user) {
            $response['user'] = new UserResource($user);
        }

        return response()->json($response, $status);
    }
}