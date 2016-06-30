<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class AuthenticateController extends Controller
{
    public function login(Request $request)
    {
		$credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    public function signup(Request $request)
    {
    	$credentials = $request->only('name', 'email', 'password');
    	$credentials['password'] = bcrypt($credentials['password']);

		try {
		   $user = User::create($credentials);
		} catch (Exception $e) {
		   return response()->json(['error' => 'User already exists.']);
		}

		$token = JWTAuth::fromUser($user);

		return response()->json(compact('token'));
    }
}
