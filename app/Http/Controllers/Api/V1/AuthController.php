<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Jobs\RegisterUserEmailJob;
use App\Models\User;
use App\Notifications\RegisterNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->only(['name', 'email']);
            $data['password'] = bcrypt($request->password);
            $user = User::create($data);
            dispatch(new RegisterUserEmailJob($user));
            $data = [
                'userData' => new UserResource($user)
            ];
            return response()->successResponse($data, 'Registration successful', 201);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }

    }

//    public function register(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|string|max:255',
//            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|confirmed|min:8',
//        ]);
//        if ($validator->fails()) {
//            return send_error('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
//        }
//        $request['password'] = Hash::make($request['password']);
//        $user = User::create([
//            'name' => $request['name'],
//            'email' => $request['email'],
//            'password' => $request['password'],
//        ]);
//
//        $token = $user->createToken('auth_token')->plainTextToken;
//        $data = [
//            'access_token' => $token,
//            'token_type' => 'Bearer',
//            'userData' => $user,
//        ];
//        return send_response('Registration Successful.', $data, Response::HTTP_CREATED);
//    }

//    public function login(Request $request)
//    {
//        $credentials = $request->only('email', 'password');
//
//        if ($token = $this->guard()->attempt($credentials)) {
//            return $this->respondWithToken($token);
//        }
//
//        return response()->json(['error' => 'Unauthorized'], 401);
//    }
    public function login(LoginRequest $request)
    {
        try {
            if ($token = JWTAuth::attempt($request->validated())) {
                return $this->respondWithToken($token);
            }
            return response()->errorResponse('Invalid email or password', 401);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }

    }

    public function profile()
    {
        $data = [
            'userData' => auth()->user()
        ];
        return send_response('User Retrieved SuccessFul.', $data, Response::HTTP_FOUND);
    }

    public function logout(Request $request)
    {
        try {
            auth()->logout();
            return response()->successResponse([], 'Logout successful', 200);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }
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
//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => $this->guard()->factory()->getTTL() * 60
//        ]);
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'userData' => JWTAuth::user()
        ];
        return response()->successResponse($data, 'Login successful');
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
