<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    public function register(Request $request)
    {
        if (!env('REGISTER_ENABLED')) {
            return response()->json( [
                'result' => 'failed'
            ], 403);
        }
        //validate incoming request
        $this->validate($request, [
            'username' => 'required|string|unique:users',
            'password' => 'required|confirmed',
        ]);
        try {
            $user = new User;
            $user->username= $request->input('username');
            $user->password = app('hash')->make($request->input('password'));
            $user->save();

            return response()->json( [
                'entity' => 'users',
                'action' => 'create',
                'result' => 'success'
            ], 201);
        } catch (\Exception $cc) {
            return response()->json( [
                'entity' => 'users',
                'action' => 'create',
                'result' => 'failed'
            ], 409);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['username', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }
}
