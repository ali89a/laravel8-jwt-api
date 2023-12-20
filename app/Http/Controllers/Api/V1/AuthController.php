<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return send_error('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request['password'] = Hash::make($request['password']);
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'userData' => $user,
        ];
        return send_response('Registration Successful.', $data, Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function profile(Request $request)
    {
        $data = [
            'userData' => $request->user()
        ];
        return send_response('User Retrieved SuccessFul.', $data, Response::HTTP_FOUND);
    }

    public function logout(Request $request)
    {
        $user = request()->user(); //or Auth::user()

        // Revoke current user token
        $data = [
            'userData' => $request->user()
        ];
        $request->user()->tokens()->delete();

        return send_response('Logged Out Successful.', $data, Response::HTTP_FOUND);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
