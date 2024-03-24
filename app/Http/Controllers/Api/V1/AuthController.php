<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Jobs\RegisterUserEmailJob;
use App\Models\User;
use App\Notifications\RegisterNotification;
use Exception;
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
        try{
            $data = $request->only(['name', 'email']);
            $data['password'] = bcrypt($request->password);
            $user = User::create($data);
            return response()->successResponse('Registration successful',new UserResource($user),  201);
        }catch(Exception $exception){
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if ($token = $this->guard()->attempt($request->validated())) {
                return $this->respondWithToken($token);
            }
            return response()->errorResponse('Invalid email or password', 401);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }

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
        try{
            auth()->logout();
            return response()->successResponse( 'Logout successful', [],200);
        }catch(Exception $exception){
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
        try{
            $token=auth()->refresh();
            $data = [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->guard()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ];
            return response()->successResponse( 'New Access Token generated',$data, 200);
        }catch(Exception $exception){
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }
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
            'user' => $this->guard()->user()
        ];
        return response()->successResponse('Login successful', $data);
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
